<?php
session_start();
require_once '../config/db.php';

// Vérification des droits d'accès au back-office
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['moderateur', 'admin', 'super_admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$message = '';
$erreur = '';

// Traitement de la modération (Validation ou Suppression d'une contribution externe)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type_contrib = $_POST['type_contrib'] ?? '';
    $id_contrib = (int)($_POST['id_contrib'] ?? 0);

    if ($id_contrib > 0) {
        $table = '';
        if ($type_contrib === 'ressource') $table = 'ressources';
        elseif ($type_contrib === 'professionnel') $table = 'professionnels';
        elseif ($type_contrib === 'structure') $table = 'structures';
        elseif ($type_contrib === 'opportunite') $table = 'opportunites';
        elseif ($type_contrib === 'evenement') $table = 'actualites_evenements';
        elseif ($type_contrib === 'temoignage') $table = 'temoignages';

        if (!empty($table)) {
            if ($action === 'valider') {
                $stmt = $pdo->prepare("UPDATE $table SET statut = 'publie' WHERE id = :id");
                $stmt->execute([':id' => $id_contrib]);
                $message = "La contribution a été validée et publiée avec succès.";
            } elseif ($action === 'supprimer') {
                $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
                $stmt->execute([':id' => $id_contrib]);
                $message = "La contribution a été supprimée de la file d'attente.";
            }
        }
    }
}

// Récupération des indicateurs clés (Statistiques globales)
$nb_ressources = $pdo->query("SELECT COUNT(*) FROM ressources WHERE statut = 'publie'")->fetchColumn();
$nb_structures = $pdo->query("SELECT COUNT(*) FROM structures WHERE statut = 'publie'")->fetchColumn();
$nb_pros = $pdo->query("SELECT COUNT(*) FROM professionnels WHERE statut = 'publie'")->fetchColumn();
$nb_offres = $pdo->query("SELECT COUNT(*) FROM opportunites WHERE statut = 'publie' AND date_expiration >= CURDATE()")->fetchColumn();
$nb_events = $pdo->query("SELECT COUNT(*) FROM actualites_evenements WHERE statut = 'publie' AND (type_contenu = 'actualite' OR date_debut >= CURDATE())")->fetchColumn();

// Centralisation des contributions en attente de modération
$contributions_attente = [];

foreach ($pdo->query("SELECT id, titre, 'ressource' AS type_contrib, date_creation FROM ressources WHERE statut = 'en_attente'")->fetchAll() as $row) {
    $contributions_attente[] = $row;
}
foreach ($pdo->query("SELECT id, CONCAT(prenom, ' ' , nom) AS titre, 'professionnel' AS type_contrib, date_creation FROM professionnels WHERE statut = 'en_attente'")->fetchAll() as $row) {
    $contributions_attente[] = $row;
}
foreach ($pdo->query("SELECT id, nom AS titre, 'structure' AS type_contrib, date_creation FROM structures WHERE statut = 'en_attente'")->fetchAll() as $row) {
    $contributions_attente[] = $row;
}
foreach ($pdo->query("SELECT id, titre, 'opportunite' AS type_contrib, date_creation FROM opportunites WHERE statut = 'en_attente'")->fetchAll() as $row) {
    $contributions_attente[] = $row;
}
foreach ($pdo->query("SELECT id, titre, 'evenement' AS type_contrib, date_creation FROM actualites_evenements WHERE statut = 'en_attente'")->fetchAll() as $row) {
    $contributions_attente[] = $row;
}
foreach ($pdo->query("SELECT id, titre, 'temoignage' AS type_contrib, date_creation FROM temoignages WHERE statut = 'en_attente'")->fetchAll() as $row) {
    $contributions_attente[] = $row;
}

$nb_moderation = count($contributions_attente);

include '../includes/header.php';
?>

<section class="hero">
    <h1>Back-Office d'Administration CRAPAA</h1>
    <p>Tableau de bord de gestion, modération des contributions et suivi de la plateforme[cite: 1].</p>
</section>

<!-- Menu d'administration interne -->
<div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 2rem;">
    <a href="index.php" class="btn-login" style="background: var(--primary); color: #fff;">Tableau de bord</a>
    <a href="gerer_pages.php" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">Éditer "Découvrir l'APA"</a>
    <a href="gerer_utilisateurs.php" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">Utilisateurs & Rôles</a>
    <a href="../index.php" target="_blank" class="btn-login" style="background: var(--secondary); color: #fff; margin-left: auto;">Voir le site public</a>
</div>

<?php if ($message): ?>
    <div class="alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<!-- Indicateurs clés -->
<h2 style="color: var(--primary); margin-bottom: 1rem;">Indicateurs clés</h2>
<div class="modules-grid" style="margin-bottom: 2.5rem;">
    <div class="card" style="border-top: 4px solid var(--primary);">
        <span class="badge badge-blue">Bibliothèque</span>
        <h3 style="font-size: 2rem; margin: 0.4rem 0; color: var(--primary);"><?= $nb_ressources ?></h3>
        <p style="color: var(--muted); font-size: 0.9rem;">Ressources publiées</p>
    </div>
    <div class="card" style="border-top: 4px solid var(--secondary);">
        <span class="badge badge-green">Annuaire</span>
        <h3 style="font-size: 2rem; margin: 0.4rem 0; color: var(--secondary);"><?= $nb_pros . ' / ' . $nb_structures ?></h3>
        <p style="color: var(--muted); font-size: 0.9rem;">Professionnels / Structures</p>
    </div>
    <div class="card" style="border-top: 4px solid #e65100;">
        <span class="badge badge-orange">Opportunités</span>
        <h3 style="font-size: 2rem; margin: 0.4rem 0; color: #e65100;"><?= $nb_offres ?></h3>
        <p style="color: var(--muted); font-size: 0.9rem;">Offres actives</p>
    </div>
    <div class="card" style="border-top: 4px solid #6a1b9a;">
        <span class="badge badge-purple">Agenda</span>
        <h3 style="font-size: 2rem; margin: 0.4rem 0; color: #6a1b9a;"><?= $nb_events ?></h3>
        <p style="color: var(--muted); font-size: 0.9rem;">Événements à venir</p>
    </div>
</div>

<!-- Bloc d'export des données administratives (CSV) -->
<div class="form-box" style="margin-bottom: 2rem; border-top: 4px solid var(--secondary); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h3 style="color: var(--primary); margin-bottom: 0.2rem;">Exports administratifs (CSV)</h3>
        <p style="color: var(--muted); font-size: 0.9rem; margin: 0;">Téléchargez les données de la plateforme pour vos rapports ou analyses.</p>
    </div>
    <div style="display: flex; gap: 0.8rem; flex-wrap: wrap;">
        <a href="export_csv.php?type=structures" class="btn-login" style="background: var(--primary); color: #fff; font-size: 0.9rem;">Exporter les structures (CSV)</a>
        <a href="export_csv.php?type=professionnels" class="btn-login" style="background: var(--secondary); color: #fff; font-size: 0.9rem;">Exporter les professionnels (CSV)</a>
    </div>
</div>

<!-- File d'attente de modération -->
<div class="form-box" style="border-top: 4px solid #c62828;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="color: var(--primary); margin: 0;">Contributions en attente de validation (<?= $nb_moderation ?>)</h2>
    </div>

    <?php if ($nb_moderation > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 650px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); background: #f8f9fa;">
                        <th style="padding: 0.75rem;">Type</th>
                        <th style="padding: 0.75rem;">Intitulé / Nom</th>
                        <th style="padding: 0.75rem;">Date de soumission</th>
                        <th style="padding: 0.75rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contributions_attente as $item): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 0.75rem;">
                                <span class="badge badge-blue"><?= htmlspecialchars(ucfirst($item['type_contrib'])) ?></span>
                            </td>
                            <td style="padding: 0.75rem; font-weight: 600;"><?= htmlspecialchars($item['titre']) ?></td>
                            <td style="padding: 0.75rem; color: var(--muted);"><?= date('d/m/Y H:i', strtotime($item['date_creation'])) ?></td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="id_contrib" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="type_contrib" value="<?= $item['type_contrib'] ?>">
                                    <button type="submit" name="action" value="valider" class="btn-login" style="background: var(--secondary); color: #fff; padding: 0.35rem 0.7rem; font-size: 0.85rem; margin-right: 0.4rem;">Valider</button>
                                    <button type="submit" name="action" value="supprimer" onclick="return confirm('Refuser et supprimer cette contribution ?');" style="background: #c62828; color: #fff; border: none; padding: 0.4rem 0.7rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Rejeter</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color: var(--muted); margin: 0; padding: 1rem 0; text-align: center;">Aucune contribution externe en attente de validation pour le moment[cite: 1].</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>