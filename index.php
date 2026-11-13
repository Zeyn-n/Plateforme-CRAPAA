<?php
require_once 'config/db.php';

// Récupération dynamique des derniers contenus publiés
$dernieres_ressources = $pdo->query("SELECT id, titre, type_document, annee_publication FROM ressources WHERE statut = 'publie' ORDER BY date_creation DESC LIMIT 3")->fetchAll();
$dernieres_offres = $pdo->query("SELECT id, titre, organisme, lieu, categorie FROM opportunites WHERE statut = 'publie' AND date_expiration >= CURDATE() ORDER BY date_creation DESC LIMIT 3")->fetchAll();
$prochains_evenements = $pdo->query("SELECT id, titre, date_debut, lieu FROM actualites_evenements WHERE statut = 'publie' AND type_contenu = 'evenement' AND date_debut >= NOW() ORDER BY date_debut ASC LIMIT 3")->fetchAll();

include 'includes/header.php';
?>

<!-- CSS Leaflet pour la mini-carte d'accueil -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Bannière principale -->
<section class="hero" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
    <div style="flex: 1; min-width: 280px;">
        <span class="badge badge-blue">Portail Régional Alsace</span>
        <h1 style="font-size: 2.1rem; margin: 0.4rem 0 0.8rem 0;">Centre de Ressources des Activités Physiques Adaptées d’Alsace</h1>
        <p style="margin-bottom: 1.2rem;">Plateforme numérique de référence pour la centralisation des connaissances scientifiques, des outils professionnels, des structures et des offres en APA.</p>
        <div style="display: flex; gap: 0.8rem; flex-wrap: wrap;">
            <a href="decouvrir.php" class="btn-login" style="background: var(--primary); color: #fff; padding: 0.65rem 1.2rem;">Découvrir l'APA</a>
            <a href="annuaire.php" class="btn-login" style="background: var(--secondary); color: #fff; padding: 0.65rem 1.2rem;">Trouver un professionnel</a>
        </div>
    </div>
    <div style="flex: 1; min-width: 280px; background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
        <h3 style="color: var(--primary); font-size: 1rem; margin-bottom: 0.6rem;">Ancrage territorial en Alsace</h3>
        <div id="map-accueil" style="height: 220px; width: 100%; border-radius: 6px;"></div>
    </div>
</section>

<!-- Blocs d'accès rapides (Cahier des charges Section 3.C) -->
<h2 style="margin-bottom: 1rem; color: var(--primary);">Accès rapides</h2>
<div class="modules-grid" style="margin-bottom: 2.5rem;">
    <div class="card">
        <span class="badge badge-blue">Bibliothèque</span>
        <h3>Ressources scientifiques</h3>
        <p style="margin-bottom: 1rem; font-size: 0.92rem;">Consultez les articles, mémoires universitaires et thèses en APA.</p>
        <a href="ressources.php?cat=scientifique" style="color: var(--primary); font-weight: 600; text-decoration: none;">Explorer &rarr;</a>
    </div>
    <div class="card">
        <span class="badge badge-green">Annuaire</span>
        <h3>Professionnels & Structures</h3>
        <p style="margin-bottom: 1rem; font-size: 0.92rem;">Localisez les EAPA et les structures sport-santé en Alsace.</p>
        <a href="annuaire.php" style="color: var(--secondary); font-weight: 600; text-decoration: none;">Consulter la carte &rarr;</a>
    </div>
    <div class="card">
        <span class="badge badge-orange">Emploi & Stage</span>
        <h3>Offres & Formations</h3>
        <p style="margin-bottom: 1rem; font-size: 0.92rem;">Trouvez un contrat, un stage de Master 2 ou une formation continue.</p>
        <a href="opportunites.php" style="color: #e65100; font-weight: 600; text-decoration: none;">Voir les offres &rarr;</a>
    </div>
    <div class="card">
        <span class="badge badge-purple">Agenda</span>
        <h3>Actus & Événements</h3>
        <p style="margin-bottom: 1rem; font-size: 0.92rem;">Ne manquez aucun colloque ou journée professionnelle.</p>
        <a href="actualites.php" style="color: #6a1b9a; font-weight: 600; text-decoration: none;">Voir l'agenda &rarr;</a>
    </div>
</div>

<!-- Contenus dynamiques récents -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
    
    <!-- Dernières ressources -->
    <div class="form-box">
        <h3 style="color: var(--primary); margin-bottom: 1rem; border-bottom: 2px solid var(--border); padding-bottom: 0.4rem;">Dernières ressources publiées</h3>
        <?php if (count($dernieres_ressources) > 0): ?>
            <ul style="list-style: none; display: grid; gap: 0.8rem;">
                <?php foreach ($dernieres_ressources as $res): ?>
                    <li>
                        <span class="badge badge-blue"><?= htmlspecialchars($res['type_document']) ?></span>
                        <a href="ressource_detail.php?id=<?= $res['id'] ?>" style="display: block; font-weight: 600; color: var(--primary); text-decoration: none; margin-top: 0.2rem;">
                            <?= htmlspecialchars($res['titre']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--muted); font-size: 0.92rem;">Aucune ressource pour le moment.</p>
        <?php endif; ?>
    </div>

    <!-- Dernières offres -->
    <div class="form-box">
        <h3 style="color: var(--primary); margin-bottom: 1rem; border-bottom: 2px solid var(--border); padding-bottom: 0.4rem;">Dernières opportunités</h3>
        <?php if (count($dernieres_offres) > 0): ?>
            <ul style="list-style: none; display: grid; gap: 0.8rem;">
                <?php foreach ($dernieres_offres as $off): ?>
                    <li>
                        <span class="badge badge-orange"><?= ucfirst($off['categorie']) ?></span>
                        <a href="opportunites.php" style="display: block; font-weight: 600; color: var(--primary); text-decoration: none; margin-top: 0.2rem;">
                            <?= htmlspecialchars($off['titre']) ?> (<?= htmlspecialchars($off['organisme']) ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--muted); font-size: 0.92rem;">Aucune offre active pour le moment.</p>
        <?php endif; ?>
    </div>

</div>

<!-- Script mini-carte Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var mapAccueil = L.map('map-accueil', { zoomControl: false, dragging: false, scrollWheelZoom: false }).setView([48.35, 7.50], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(mapAccueil);

// Marqueurs rapides Strasbourg / Mulhouse
L.circleMarker([48.5734, 7.7521], { radius: 6, color: '#0d47a1', fillOpacity: 0.8 }).addTo(mapAccueil).bindPopup('Strasbourg');
L.circleMarker([47.7508, 7.3359], { radius: 6, color: '#00897b', fillOpacity: 0.8 }).addTo(mapAccueil).bindPopup('Mulhouse');
</script>

<?php include 'includes/footer.php'; ?>