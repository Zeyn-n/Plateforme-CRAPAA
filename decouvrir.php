<?php
require_once 'config/db.php';

// Récupération de toutes les sections éditoriales par ordre d'affichage
$stmt = $pdo->query("SELECT * FROM pages_contenu ORDER BY ordre ASC, id ASC");
$sections = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="hero">
    <h1>Découvrir l'Activité Physique Adaptée (APA)</h1>
    <p>Comprendre les enjeux, le rôle des professionnels et les dispositifs d'accès en Alsace.</p>
</section>

<!-- Sommaire de navigation rapide -->
<?php if (count($sections) > 0): ?>
    <div style="background: #fff; padding: 1.2rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <strong style="display: block; margin-bottom: 0.6rem; color: var(--primary);">Sommaire de la rubrique :</strong>
        <ul style="list-style: none; display: flex; flex-wrap: wrap; gap: 1rem;">
            <?php foreach ($sections as $sec): ?>
                <li>
                    <a href="#section-<?= htmlspecialchars($sec['slug']) ?>" style="color: var(--secondary); text-decoration: none; font-weight: 600;">
                        &rarr; <?= htmlspecialchars($sec['titre']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Affichage dynamique des blocs de contenu -->
<div style="display: grid; gap: 1.8rem;">
    <?php foreach ($sections as $sec): ?>
        <article id="section-<?= htmlspecialchars($sec['slug']) ?>" class="form-box" style="border-left: 5px solid var(--secondary);">
            <h2 style="color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($sec['titre']) ?></h2>
            <div style="color: var(--text); font-size: 1.02rem; line-height: 1.7;">
                <?= nl2br(htmlspecialchars($sec['contenu'])) ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<!-- Liens institutionnels et orientation -->
<div class="modules-grid" style="margin-top: 2.5rem;">
    <div class="card">
        <span class="badge badge-blue">Dispositif Régional</span>
        <h3>Prescri'Mouv (Grand Est)</h3>
        <p style="margin-bottom: 1rem;">Dispositif régional d'accompagnement vers la pratique d'activité physique sur prescription médicale.</p>
        <a href="orienter.php" class="btn-login" style="background: var(--primary); color: #fff;">M'orienter en Alsace</a>
    </div>
    <div class="card">
        <span class="badge badge-green">Annuaire</span>
        <h3>Trouver un professionnel ou une structure</h3>
        <p style="margin-bottom: 1rem;">Identifiez un Enseignant en APA (EAPA) ou une Maison Sport-Santé près de chez vous.</p>
        <a href="annuaire.php" class="btn-login" style="background: var(--secondary); color: #fff;">Consulter l'annuaire</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>