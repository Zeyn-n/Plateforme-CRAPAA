<?php
session_start();
require_once '../config/db.php';

// Seuls les administrateurs et super-admins peuvent gérer les utilisateurs
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$message = '';
$erreur = '';

// Traitement des actions (Changement de rôle ou suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_cible = (int)($_POST['user_id'] ?? 0);

    if ($id_cible > 0) {
        // Empêcher de modifier son propre compte admin par inadvertance
        if ($id_cible === (int)$_SESSION['user_id']) {
            $erreur = "Vous ne pouvez pas modifier ou supprimer votre propre compte administrateur depuis cette interface.";
        } else {
            if ($action === 'modifier_role') {
                $nouveau_role = $_POST['role'] ?? 'contributeur';
                $roles_autorises = ['visiteur', 'contributeur', 'professionnel', 'moderateur', 'admin', 'super_admin'];
                
                if (in_array($nouveau_role, $roles_autorises)) {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET role = :role WHERE id = :id");
                    $stmt->execute([':role' => $nouveau_role, ':id' => $id_cible]);
                    $message = "Le rôle de l'utilisateur a été mis à jour avec succès.";
                }
            } elseif ($action === 'supprimer') {
                $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
                $stmt->execute([':id' => $id_cible]);
                $message = "Le compte utilisateur a été supprimé définitivement.";
            }
        }
    }
}

// Récupération de tous les utilisateurs
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll();

include '../includes/header.php';
?>

<section class="hero">
    <h1>Gestion des Utilisateurs & Rôles</h1>
    <p>Administration des comptes enregistrés sur la plateforme CRAPAA.</p>
</section>

<div style="margin-bottom: 1.5rem;">
    <a href="index.php" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">&larr; Retour au tableau de bord</a>
</div>

<?php if ($message): ?>
    <div class="alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="form-box">
    <h2 style="color: var(--primary); margin-bottom: 1rem;">Liste des utilisateurs (<?= count($utilisateurs) ?>)</h2>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 700px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); background: #f8f9fa;">
                    <th style="padding: 0.75rem;">Nom & Prénom</th>
                    <th style="padding: 0.75rem;">Email</th>
                    <th style="padding: 0.75rem;">Rôle actuel</th>
                    <th style="padding: 0.75rem;">Date d'inscription</th>
                    <th style="padding: 0.75rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $u): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 0.75rem; font-weight: 600;"><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></td>
                        <td style="padding: 0.75rem; color: var(--muted);"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="padding: 0.75rem;">
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="action" value="modifier_role">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role" style="padding: 0.35rem; border: 1px solid var(--border); border-radius: 4px; font-size: 0.88rem;">
                                    <option value="visiteur" <?= $u['role'] === 'visiteur' ? 'selected' : '' ?>>Visiteur</option>
                                    <option value="contributeur" <?= $u['role'] === 'contributeur' ? 'selected' : '' ?>>Contributeur</option>
                                    <option value="professionnel" <?= $u['role'] === 'professionnel' ? 'selected' : '' ?>>Professionnel</option>
                                    <option value="moderateur" <?= $u['role'] === 'moderateur' ? 'selected' : '' ?>>Modérateur</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="super_admin" <?= $u['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                </select>
                                <button type="submit" class="btn-login" style="padding: 0.35rem 0.7rem; font-size: 0.85rem;">Modifier</button>
                            </form>
                        </td>
                        <td style="padding: 0.75rem; color: var(--muted);"><?= date('d/m/Y H:i', strtotime($u['date_inscription'])) ?></td>
                        <td style="padding: 0.75rem;">
                            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #c62828; text-decoration: underline; cursor: pointer; font-size: 0.85rem;">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size: 0.82rem;">Compte courant</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>