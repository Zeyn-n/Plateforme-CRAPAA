<?php
require_once 'config/db.php';

// Récupération des filtres
$entite = $_GET['entite'] ?? 'tous'; // 'tous', 'structures', 'professionnels'
$ville_cp = trim($_GET['ville_cp'] ?? '');
$type_struct = trim($_GET['type_struct'] ?? '');
$mot_cle = trim($_GET['mot_cle'] ?? ''); // spécialité, public, pathologie, activité

$resultats = [];

// 1. Requête Structures
if ($entite === 'tous' || $entite === 'structures') {
    $where_s = ["statut = 'publie'"];
    $params_s = [];

    if (!empty($ville_cp)) {
        $where_s[] = "(ville LIKE :v OR code_postal LIKE :v)";
        $params_s[':v'] = '%' . $ville_cp . '%';
    }
    if (!empty($type_struct)) {
        $where_s[] = "type_etablissement = :ts";
        $params_s[':ts'] = $type_struct;
    }
    if (!empty($mot_cle)) {
        $where_s[] = "(nom LIKE :mc OR activites LIKE :mc OR publics LIKE :mc OR pathologies LIKE :mc)";
        $params_s[':mc'] = '%' . $mot_cle . '%';
    }

    $stmt_s = $pdo->prepare("SELECT * FROM structures WHERE " . implode(' AND ', $where_s) . " ORDER BY nom ASC");
    $stmt_s->execute($params_s);
    foreach ($stmt_s->fetchAll() as $s) {
        $resultats[] = [
            'entite' => 'structure',
            'id' => $s['id'],
            'titre' => $s['nom'],
            'sous_titre' => $s['type_etablissement'],
            'adresse' => $s['adresse'],
            'cp' => $s['code_postal'],
            'ville' => $s['ville'],
            'lat' => $s['latitude'],
            'lng' => $s['longitude'],
            'activites' => $s['activites'],
            'publics' => $s['publics'],
            'pathologies' => $s['pathologies']
        ];
    }
}

// 2. Requête Professionnels (si aucun filtre spécifique "type de structure" n'est imposé)
if (($entite === 'tous' || $entite === 'professionnels') && empty($type_struct)) {
    $where_p = ["statut = 'publie'"];
    $params_p = [];

    if (!empty($ville_cp)) {
        $where_p[] = "(ville LIKE :v OR code_postal LIKE :v)";
        $params_p[':v'] = '%' . $ville_cp . '%';
    }
    if (!empty($mot_cle)) {
        $where_p[] = "(nom LIKE :mc OR prenom LIKE :mc OR specialites LIKE :mc OR activites LIKE :mc OR publics LIKE :mc OR pathologies LIKE :mc)";
        $params_p[':mc'] = '%' . $mot_cle . '%';
    }

    $stmt_p = $pdo->prepare("SELECT * FROM professionnels WHERE " . implode(' AND ', $where_p) . " ORDER BY nom ASC");
    $stmt_p->execute($params_p);
    foreach ($stmt_p->fetchAll() as $p) {
        $resultats[] = [
            'entite' => 'professionnel',
            'id' => $p['id'],
            'titre' => $p['prenom'] . ' ' . $p['nom'],
            'sous_titre' => $p['diplome'],
            'adresse' => $p['adresse'],
            'cp' => $p['code_postal'],
            'ville' => $p['ville'],
            'lat' => $p['latitude'],
            'lng' => $p['longitude'],
            'activites' => $p['activites'],
            'publics' => $p['publics'],
            'pathologies' => $p['pathologies']
        ];
    }
}

include 'includes/header.php';
?>

<!-- CSS Leaflet & MarkerCluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<section class="hero">
    <h1>Annuaire Régional & Carte Interactive APA</h1>
    <p>Localisez les Enseignants en Activité Physique Adaptée (EAPA) et les structures d'accueil en Alsace.</p>
</section>

<!-- Barre de filtres -->
<form action="annuaire.php" method="GET" class="form-box" style="margin-bottom: 1.5rem; padding: 1.5rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Rechercher</label>
            <select name="entite" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="tous" <?= $entite === 'tous' ? 'selected' : '' ?>>Structures et Professionnels</option>
                <option value="structures" <?= $entite === 'structures' ? 'selected' : '' ?>>Structures uniquement</option>
                <option value="professionnels" <?= $entite === 'professionnels' ? 'selected' : '' ?>>Professionnels (EAPA) uniquement</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Ville ou Code postal</label>
            <input type="text" name="ville_cp" value="<?= htmlspecialchars($ville_cp) ?>" placeholder="Ex : Strasbourg, 68100..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Type de structure</label>
            <select name="type_struct" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="">Tous types</option>
                <option value="Centre Hospitalier" <?= $type_struct === 'Centre Hospitalier' ? 'selected' : '' ?>>Centre Hospitalier</option>
                <option value="Maison Sport-Santé" <?= $type_struct === 'Maison Sport-Santé' ? 'selected' : '' ?>>Maison Sport-Santé</option>
                <option value="Association" <?= $type_struct === 'Association' ? 'selected' : '' ?>>Association</option>
                <option value="EHPAD" <?= $type_struct === 'EHPAD' ? 'selected' : '' ?>>EHPAD / Médico-social</option>
                <option value="Cabinet libéral" <?= $type_struct === 'Cabinet libéral' ? 'selected' : '' ?>>Cabinet libéral</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Pathologie, public ou activité</label>
            <input type="text" name="mot_cle" value="<?= htmlspecialchars($mot_cle) ?>" placeholder="Ex : Parkinson, Seniors, Marche..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>
    </div>

    <div style="display: flex; gap: 0.8rem; align-items: center; flex-wrap: wrap;">
        <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Filtrer l'annuaire</button>
        <a href="annuaire.php" style="color: var(--muted); font-size: 0.9rem;">Réinitialiser</a>
        <span style="margin-left: auto; font-weight: 600; color: var(--primary);"><?= count($resultats) ?> résultat(s) trouvé(s)</span>
    </div>
</form>

<!-- Bascule Vue Carte / Vue Liste -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" id="btn-vue-carte" onclick="basculerVue('carte')" class="btn-login" style="background: var(--primary); color: #fff;">Vue Carte interactive</button>
        <button type="button" id="btn-vue-liste" onclick="basculerVue('liste')" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">Vue Liste détaillée</button>
    </div>
    <div style="font-size: 0.88rem; color: var(--muted);">
        <span style="display:inline-block; width:12px; height:12px; background:#0d47a1; border-radius:50%; margin-right:4px;"></span> Structure
        <span style="display:inline-block; width:12px; height:12px; background:#00897b; border-radius:50%; margin-left:12px; margin-right:4px;"></span> Professionnel EAPA
    </div>
</div>

<!-- Conteneur Carte -->
<div id="bloc-carte" style="background: #fff; padding: 0.6rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); margin-bottom: 2rem;">
    <div id="map-alsace" style="height: 520px; width: 100%; border-radius: 6px; z-index: 1;"></div>
</div>

<!-- Conteneur Liste -->
<div id="bloc-liste" style="display: none;">
    <?php if (count($resultats) > 0): ?>
        <div class="modules-grid">
            <?php foreach ($resultats as $item): ?>
                <div class="card" style="border-top-color: <?= $item['entite'] === 'structure' ? 'var(--primary)' : 'var(--secondary)' ?>; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <span class="badge <?= $item['entite'] === 'structure' ? 'badge-blue' : 'badge-green' ?>">
                            <?= $item['entite'] === 'structure' ? 'Structure' : 'Professionnel EAPA' ?>
                        </span>
                        <h3 style="margin-bottom: 0.2rem;"><?= htmlspecialchars($item['titre']) ?></h3>
                        <p style="font-size: 0.88rem; color: var(--muted); margin-bottom: 0.6rem;"><?= htmlspecialchars($item['sous_titre']) ?></p>
                        <p style="font-size: 0.9rem; margin-bottom: 0.6rem;">
                            <strong>Lieu :</strong> <?= htmlspecialchars($item['cp'] . ' ' . $item['ville']) ?>
                        </p>
                        <?php if (!empty($item['pathologies'])): ?>
                            <p style="font-size: 0.88rem; color: var(--text);"><strong>Pathologies / Publics :</strong> <?= htmlspecialchars($item['pathologies']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 0.8rem; border-top: 1px solid var(--border);">
                        <a href="fiche_annuaire.php?type=<?= $item['entite'] ?>&id=<?= $item['id'] ?>" class="btn-login" style="background: <?= $item['entite'] === 'structure' ? 'var(--primary)' : 'var(--secondary)' ?>; color: #fff; font-size: 0.85rem;">
                            Consulter la fiche complète
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="form-box" style="text-align: center; padding: 2.5rem;">
            <p style="color: var(--muted);">Aucun professionnel ou structure ne correspond à votre recherche.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Scripts Leaflet & MarkerCluster -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
// Initialisation de la carte centrée sur l'Alsace
var map = L.map('map-alsace').setView([48.18, 7.55], 8);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Groupe de clusters pour regrouper les marqueurs proches
var markersCluster = L.markerClusterGroup();

var donnees = <?= json_encode($resultats, JSON_UNESCAPED_UNICODE) ?>;

function creerIcone(couleur) {
    return L.divIcon({
        className: 'custom-pin',
        html: '<div style="background-color:' + couleur + '; width:18px; height:18px; border-radius:50%; border:3px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>',
        iconSize: [18, 18],
        iconAnchor: [9, 9]
    });
}

donnees.forEach(function(item) {
    if (item.lat && item.lng) {
        var couleur = (item.entite === 'structure') ? '#0d47a1' : '#00897b';
        var labelEntite = (item.entite === 'structure') ? 'Structure' : 'Professionnel EAPA';
        
        var marker = L.marker([parseFloat(item.lat), parseFloat(item.lng)], {
            icon: creerIcone(couleur)
        });

        var popupHtml = '<div style="min-width: 200px;">' +
            '<span style="font-size: 0.75rem; font-weight: bold; text-transform: uppercase; color:' + couleur + ';">' + labelEntite + '</span><br>' +
            '<strong style="font-size: 1rem;">' + item.titre + '</strong><br>' +
            '<small style="color: #555;">' + item.sous_titre + '</small><br>' +
            '<p style="margin: 6px 0; font-size: 0.85rem;">' + (item.adresse ? item.adresse + ', ' : '') + item.cp + ' ' + item.ville + '</p>' +
            '<a href="fiche_annuaire.php?type=' + item.entite + '&id=' + item.id + '" style="display:inline-block; margin-top:4px; padding:4px 8px; background:' + couleur + '; color:#fff; text-decoration:none; border-radius:4px; font-size:0.82rem;">Voir la fiche complète</a>' +
            '</div>';

        marker.bindPopup(popupHtml);
        markersCluster.addLayer(marker);
    }
});

map.addLayer(markersCluster);

// Gestion de la bascule Carte / Liste
function basculerVue(mode) {
    var blocCarte = document.getElementById('bloc-carte');
    var blocListe = document.getElementById('bloc-liste');
    var btnCarte = document.getElementById('btn-vue-carte');
    var btnListe = document.getElementById('btn-vue-liste');

    if (mode === 'carte') {
        blocCarte.style.display = 'block';
        blocListe.style.display = 'none';
        btnCarte.style.background = 'var(--primary)';
        btnCarte.style.color = '#fff';
        btnListe.style.background = '#fff';
        btnListe.style.color = 'var(--text)';
        map.invalidateSize();
    } else {
        blocCarte.style.display = 'none';
        blocListe.style.display = 'block';
        btnListe.style.background = 'var(--primary)';
        btnListe.style.color = '#fff';
        btnCarte.style.background = '#fff';
        btnCarte.style.color = 'var(--text)';
    }
}
</script>

<?php include 'includes/footer.php'; ?>