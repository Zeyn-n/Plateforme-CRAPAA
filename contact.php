<?php
require_once 'config/db.php';

$message_envoye = false;
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    if (!empty($nom) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($sujet) && !empty($contenu)) {
        $message_envoye = true;
    } else {
        $erreur = "Veuillez renseigner tous les champs obligatoires avec une adresse email valide.";
    }
}

include 'includes/header.php';
?>

<section class="hero">
    <h1>Contact & Informations</h1>
    <p>Une question sur le CRAPAA, un partenariat universitaire ou une demande d'information ? Écrivez-nous.</p>
</section>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
    <div class="form-box">
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Envoyer un message</h2>

        <?php if ($message_envoye): ?>
            <div class="alert-success">
                Votre message a bien été transmis à l'équipe du CRAPAA. Nous vous répondrons dans les meilleurs délais.
            </div>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nom et prénom *</label>
                <input type="text" name="nom" required placeholder="Votre nom complet">
            </div>
            <div class="form-group">
                <label>Adresse email *</label>
                <input type="email" name="email" required placeholder="votre.email@exemple.fr">
            </div>
            <div class="form-group">
                <label>Objet de la demande *</label>
                <select name="sujet" required>
                    <option value="Information générale">Demande d'information générale</option>
                    <option value="Partenariat / Institution">Partenariat institutionnel ou universitaire</option>
                    <option value="Annuaire / Référencement">Question sur l'annuaire APA</option>
                    <option value="Technique / RGPD">Demande RGPD ou signalement technique</option>
                </select>
            </div>
            <div class="form-group">
                <label>Votre message *</label>
                <textarea name="contenu" rows="5" required placeholder="Précisez votre demande (merci de ne pas transmettre de données médicales personnelles)..."></textarea>
            </div>
            <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Envoyer le message</button>
        </form>
    </div>

    <div class="form-box">
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Coordonnées & Ancrage régional</h2>
        <p style="margin-bottom: 1rem;">
            <strong>CRAPAA</strong><br>
            Centre de Ressources des Activités Physiques Adaptées d’Alsace
        </p>
        <p style="margin-bottom: 1rem;">
            <strong>Cadre universitaire :</strong><br>
            Faculté des Sciences du Sport (STAPS) — Master 2 APAS<br>
            Université de Strasbourg, Alsace
        </p>
        <p style="margin-bottom: 1.5rem;">
            <strong>Vous souhaitez ajouter un contenu ?</strong><br>
            Utilisez directement nos formulaires dédiés pour soumettre une ressource scientifique, une offre d'emploi/stage, un événement ou une fiche annuaire.
        </p>
        <a href="contribuer.php" class="btn-login" style="background: var(--secondary); color: #fff;">Accéder à l'espace Contribuer</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>