<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['moderateur', 'admin', 'super_admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$message = '';
$erreur = '';

// Traitement de la modification ou de l'ajout d'une section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'modifier') {
        $id = (int)$_POST['id'];
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        $ordre = (int)($_POST['ordre'] ?? 0);

        if ($id > 0 && !empty($titre) && !empty($contenu)) {
            $stmt = $pdo->prepare("UPDATE pages_contenu SET titre = :titre, contenu = :contenu, ordre = :ordre WHERE id = :id");
            $stmt->execute([
                ':titre' => $titre,
                ':contenu' => $contenu,
                ':ordre' => $ordre,
                ':id' => $id
            ]);
            $message = "Section mise à jour avec succès.";
        } else {
            $erreur = "Veuillez remplir tous les champs obligatoires.";
        }
    } elseif ($action === 'ajouter') {
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        $ordre = (int)($_POST['ordre'] ?? 10);
        // Génération automatique d'un slug propre
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $titre)), '-'));

        if (!empty($titre) && !empty($contenu)) {
            $stmt = $pdo->prepare("INSERT INTO pages_contenu (slug, titre, contenu, ordre) VALUES (:slug, :titre, :contenu, :ordre)");
            $stmt->execute([
                ':slug' => $slug . '-' . time(),
                ':titre' => $titre,
                ':contenu' => $contenu,
                ':ordre' => $ordre
            ]);
            $message = "Nouvelle section ajoutée à la page Découvrir l'APA.";
        } else {
            $erreur = "Le titre et le contenu sont obligatoires.";
        }
    } elseif ($action === 'supprimer') {
        $id = (int)$_POST['id'];
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM pages_contenu WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $message = "Section supprimée.";
        }
    }
}

$sections = $pdo->query("SELECT * FROM pages_contenu ORDER BY ordre ASC, id ASC")->fetchAll();

include '../includes/header.php';
?>

<section class="hero">
    <h1>Éditeur : Rubrique « Découvrir l'APA »</h1>
    <p>Modifiez les textes de présentation publics sans toucher au code source.</p>
</section>

<div style="margin-bottom: 1.5rem;">
    <a href="index.php" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">&larr; Retour au tableau de bord</a>
    <a href="../decouvrir.php" target="_blank" class="btn-login" style="background: var(--primary); color: #fff; margin-left: 0.5rem;">Voir la page publique</a>
</div>

<?php if ($message): ?>
    <div class="alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<!-- Liste des sections modifiables -->
<div style="display: grid; gap: 1.5rem; margin-bottom: 3rem;">
    <?php foreach ($sections as $sec): ?>
        <div class="form-box">
            <form method="POST">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id" value="<?= $sec['id'] ?>">

                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
                    <div style="flex: 4; min-width: 250px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Titre de la section</label>
                        <input type="text" name="titre" value="<?= htmlspecialchars($sec['titre']) ?>" required style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
                    </div>
                    <div style="flex: 1; min-width: 100px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Ordre d'affichage</label>
                        <input type="number" name="ordre" value="<?= (int)$sec['ordre'] ?>" style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Contenu éditorial</label>
                    <textarea name="contenu" rows="5" required><?= htmlspecialchars($sec['contenu']) ?></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="submit" class="btn-login" style="background: var(--secondary); color: #fff;">Enregistrer les modifications</button>
                    <small style="color: var(--muted);">Dernière modification : <?= date('d/m/Y H:i', strtotime($sec['date_modification'])) ?></small>
                </div>
            </form>

            <form method="POST" onsubmit="return confirm('Supprimer définitivement cette section ?');" style="margin-top: 0.8rem; text-align: right;">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $sec['id'] ?>">
                <button type="submit" style="background: none; border: none; color: #c62828; text-decoration: underline; cursor: pointer; font-size: 0.85rem;">Supprimer cette section</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<!-- Formulaire d'ajout d'une nouvelle section -->
<div class="form-box" style="border-top: 4px solid var(--primary);">
    <h2 style="color: var(--primary); margin-bottom: 1rem;">Ajouter une nouvelle section</h2>
    <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
            <div style="flex: 4; min-width: 250px;">
                <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Titre de la nouvelle section</label>
                <input type="text" name="titre" placeholder="Ex : Prise en charge par les mutuelles" required style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Ordre</label>
                <input type="number" name="ordre" value="4" style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
            </div>
        </div>
        <div class="form-group">
            <label>Texte de présentation</label>
            <textarea name="contenu" rows="5" required placeholder="Rédigez le contenu pédagogique ici..."></textarea>
        </div>
        <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Ajouter la section</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>