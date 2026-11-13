<?php
session_start();
require_once 'config/db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (!empty($email) && !empty($mot_de_passe)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirection selon le rôle
            if (in_array($user['role'], ['moderateur', 'admin', 'super_admin'])) {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}

include 'includes/header.php';
?>

<section class="hero">
    <h1>Connexion à votre compte CRAPAA</h1>
    <p>Accédez à votre espace contributeur, suivez vos publications ou gérez la plateforme.</p>
</section>

<div style="max-width: 500px; margin: 0 auto;">
    <div class="form-box">
        <?php if ($erreur): ?>
            <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Adresse email *</label>
                <input type="email" name="email" required placeholder="votre.email@exemple.fr">
            </div>
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" name="mot_de_passe" required placeholder="Votre mot de passe">
            </div>
            <button type="submit" class="btn-login" style="width: 100%; background: var(--primary); color: #fff; padding: 0.7rem; font-size: 1rem; margin-top: 0.5rem;">
                Se connecter
            </button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; border-top: 1px solid var(--border); padding-top: 1rem;">
            Pas encore de compte ? <a href="inscription.php" style="color: var(--secondary); font-weight: 600;">Créer un compte contributeur</a>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="margin-top: 1rem; text-align: center; border-top: 1px solid var(--border); padding-top: 1rem;">
                <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">Vous êtes déjà connecté en tant que <strong><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></strong> (<?= htmlspecialchars($_SESSION['user_role']) ?>).</p>
                <a href="deconnexion.php" class="btn-login" style="background: #c62828; color: #fff; font-size: 0.85rem;">Se déconnecter</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>