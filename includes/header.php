<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déterminer si on est dans le dossier /admin/ pour ajuster les chemins relatifs
$is_admin_dir = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base_path = $is_admin_dir ? '../' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRAPAA - Centre de Ressources des Activités Physiques Adaptées d’Alsace</title>
    <link rel="stylesheet" href="http://localhost/CRAPAA/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <!-- Barre supérieure : Logo, Recherche globale, Compte -->
        <div class="top-bar">
            <div class="logo">
                <a href="<?= $base_path ?>index.php">
                    <strong>CRAPAA</strong>
                    <span>Ressources APA Alsace</span>
                </a>
            </div>

            <form action="<?= $base_path ?>recherche.php" method="GET" class="global-search">
                <input type="text" name="q" placeholder="Rechercher une ressource, un EAPA, un stage, une pathologie..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                <button type="submit" aria-label="Rechercher">🔍</button>
            </form>

            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (in_array($_SESSION['user_role'], ['moderateur', 'admin', 'super_admin'])): ?>
                        <a href="<?= $base_path ?>admin/index.php" class="btn-admin"> Back-office</a>
                    <?php endif; ?>
                    <a href="<?= $base_path ?>connexion.php" class="btn-account"> <?= htmlspecialchars($_SESSION['user_prenom']) ?></a>
                <?php else: ?>
                    <a href="<?= $base_path ?>connexion.php" class="btn-login">Connexion / Inscription</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Barre de navigation principale (Les 10 rubriques) -->
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="<?= $base_path ?>index.php">Accueil</a></li>
                <li><a href="<?= $base_path ?>decouvrir.php">Découvrir l'APA</a></li>
                <li><a href="<?= $base_path ?>ressources.php">Ressources & Outils</a></li>
                <li><a href="<?= $base_path ?>annuaire.php">Annuaire & Carte</a></li>
                <li><a href="<?= $base_path ?>opportunites.php">Emploi & Formation</a></li>
                <li><a href="<?= $base_path ?>actualites.php">Actus & Événements</a></li>
                <li><a href="<?= $base_path ?>communaute.php">Communauté</a></li>
                <li><a href="<?= $base_path ?>orienter.php">M'orienter en Alsace</a></li>
                <li><a href="<?= $base_path ?>contribuer.php" class="nav-highlight">+ Contribuer</a></li>
            </ul>
        </nav>
    </header>
    <main class="container">