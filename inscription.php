<?php
session_start();
require_once 'config/db.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation_mdp'] ?? '';

    if (!empty($nom) && !empty($prenom) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($mot_de_passe)) {
        if ($mot_de_passe === $confirmation) {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $erreur = "Cette adresse email est déjà associée à un compte.";
            } else {
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (:nom, :prenom, :email, :mdp, 'contributeur')");
                $stmt_insert->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':mdp' => $hash
                ]);
                $succes = "Votre compte a bien été créé. Vous pouvez dès à présent vous connecter.";
            }
        } else {
            $erreur = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs obligatoires avec une adresse email valide.";
    }
}

include 'includes/header.php';
?>

<section class="hero">
    <h1>Créer un compte contributeur</h1>
    <p>Rejoignez la communauté CRAPAA pour proposer des ressources, des offres ou des actualités.</p>
</section>

<div style="max-width: 500px; margin: 0 auto;">
    <div class="form-box">
        <?php if ($erreur): ?>
            <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="alert-success">
                <?= htmlspecialchars($succes) ?><br><br>
                <a href="connexion.php" class="btn-login" style="background: var(--primary); color: #fff;">Aller à la connexion</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" required placeholder="Votre nom">
                </div>
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" required placeholder="Votre prénom">
                </div>
                <div class="form-group">
                    <label>Adresse email *</label>
                    <input type="email" name="email" required placeholder="votre.email@exemple.fr">
                </div>
                <div class="form-group">
                    <label>Mot de passe *</label>
                    <input type="password" name="mot_de_passe" required placeholder="Minimum 6 caractères">
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe *</label>
                    <input type="password" name="confirmation_mdp" required placeholder="Répétez le mot de passe">
                </div>
                <button type="submit" class="btn-login" style="width: 100%; background: var(--secondary); color: #fff; padding: 0.7rem; font-size: 1rem; margin-top: 0.5rem;">
                    S'inscrire
                </button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; border-top: 1px solid var(--border); padding-top: 1rem;">
            Déjà un compte ? <a href="connexion.php" style="color: var(--primary); font-weight: 600;">Se connecter</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>