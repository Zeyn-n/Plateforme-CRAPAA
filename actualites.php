<?php
require_once 'config/db.php';

// Récupération des filtres
$type = $_GET['type'] ?? ''; // 'actualite', 'evenement' ou ''
$categorie = trim($_GET['categorie'] ?? '');
$lieu = trim($_GET['lieu'] ?? '');
$periode = $_GET['periode'] ?? 'avenir'; // 'avenir', 'tout', 'passes'
$q = trim($_GET['q'] ?? '');

$where = ["statut = 'publie'"];
$params = [];

if (in_array($type, ['actualite', 'evenement'])) {
    $where[] = "type_contenu = :type";
    $params[':type'] = $type;
}
if (!empty($categorie)) {
    $where[] = "categorie = :cat";
    $params[':cat'] = $categorie;
}
if (!empty($lieu)) {
    $where[] = "lieu LIKE :lieu";
    $params[':lieu'] = '%' . $lieu . '%';
}
if (!empty($q)) {
    $where[] = "(titre LIKE :q OR organisateur LIKE :q OR description LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

if ($periode === 'avenir') {
    $where[] = "(type_contenu = 'actualite' OR date_debut >= CURDATE())";
} elseif ($periode === 'passes') {
    $where[] = "type_contenu = 'evenement' AND date_debut < CURDATE()";
}

$sql = "SELECT * FROM actualites_evenements WHERE " . implode(' AND ', $where) . " ORDER BY COALESCE(date_debut, date_creation) ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Récupération de tous les événements publiés pour le calendrier mensuel
$mois_cal = isset($_GET['mois']) ? max(1, min(12, (int)$_GET['mois'])) : (int)date('m');
$annee_cal = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');

$debut_mois = sprintf('%04d-%02d-01 00:00:00', $annee_cal, $mois_cal);
$fin_mois = date('Y-m-t 23:59:59', strtotime($debut_mois));

$stmt_cal = $pdo->prepare("SELECT id, titre, date_debut, lieu, categorie FROM actualites_evenements WHERE statut = 'publie' AND type_contenu = 'evenement' AND date_debut BETWEEN :d1 AND :d2 ORDER BY date_debut ASC");
$stmt_cal->execute([':d1' => $debut_mois, ':d2' => $fin_mois]);
$events_mois = [];
foreach ($stmt_cal->fetchAll() as $ev) {
    $jour = (int)date('j', strtotime($ev['date_debut']));
    $events_mois[$jour][] = $ev;
}

$noms_mois = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

$categories_liste = [
    'Conférence',
    'Colloque',
    'Journée professionnelle',
    'Formation',
    'Événement universitaire',
    'Action de prévention',
    'Manifestation sportive adaptée',
    'Actualité institutionnelle'
];

include 'includes/header.php';
?>

<section class="hero">
    <h1>Actualités & Événements APA</h1>
    <p>Suivez l'actualité du secteur, les colloques scientifiques, les journées professionnelles et les manifestations en Alsace.</p>
</section>

<!-- Onglets rapides + Contribution -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem; margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="actualites.php" class="btn-login" style="<?= $type === '' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Tout afficher</a>
        <a href="actualites.php?type=evenement" class="btn-login" style="<?= $type === 'evenement' ? 'background: var(--secondary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Événements & Agenda</a>
        <a href="actualites.php?type=actualite" class="btn-login" style="<?= $type === 'actualite' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Actualités</a>
    </div>
    <a href="contribuer.php?type=evenement" class="btn-login" style="background: var(--secondary); color: #fff;">+ Proposer un événement ou une actualité</a>
</div>

<!-- Barre de filtres -->
<form action="actualites.php" method="GET" class="form-box" style="margin-bottom: 1.5rem; padding: 1.5rem;">
    <?php if (!empty($type)): ?>
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Recherche</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Titre, organisateur..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Catégorie</label>
            <select name="categorie" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories_liste as $cat_item): ?>
                    <option value="<?= htmlspecialchars($cat_item) ?>" <?= $categorie === $cat_item ? 'selected' : '' ?>><?= htmlspecialchars($cat_item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Localisation</label>
            <input type="text" name="lieu" value="<?= htmlspecialchars($lieu) ?>" placeholder="Ex : Strasbourg, Mulhouse..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Période</label>
            <select name="periode" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="avenir" <?= $periode === 'avenir' ? 'selected' : '' ?>>À venir & Récentes</option>
                <option value="tout" <?= $periode === 'tout' ? 'selected' : '' ?>>Toutes les dates</option>
                <option value="passes" <?= $periode === 'passes' ? 'selected' : '' ?>>Événements passés</option>
            </select>
        </div>
    </div>

    <div style="display: flex; gap: 0.8rem; align-items: center;">
        <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Filtrer</button>
        <a href="actualites.php" style="color: var(--muted); font-size: 0.9rem;">Réinitialiser</a>
        <span style="margin-left: auto; color: var(--muted); font-size: 0.9rem;"><strong><?= count($items) ?></strong> résultat(s)</span>
    </div>
</form>

<!-- Bascule Vue Liste / Vue Calendrier -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
    <button type="button" id="btn-actus-liste" onclick="basculerActus('liste')" class="btn-login" style="background: var(--primary); color: #fff;">Vue Liste</button>
    <button type="button" id="btn-actus-cal" onclick="basculerActus('calendrier')" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">Vue Calendrier mensuel</button>
</div>

<!-- VUE LISTE -->
<div id="vue-actus-liste">
    <?php if (count($items) > 0): ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach ($items as $actu): ?>
                <article class="form-box" style="border-left: 5px solid <?= $actu['type_contenu'] === 'evenement' ? 'var(--secondary)' : 'var(--primary)' ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.6rem;">
                        <div>
                            <span class="badge <?= $actu['type_contenu'] === 'evenement' ? 'badge-green' : 'badge-blue' ?>">
                                <?= $actu['type_contenu'] === 'evenement' ? 'Événement' : 'Actualité' ?>
                            </span>
                            <?php if (!empty($actu['categorie'])): ?>
                                <span class="badge badge-purple"><?= htmlspecialchars($actu['categorie']) ?></span>
                            <?php endif; ?>
                        </div>
                        <small style="color: var(--muted);">
                            <?php if ($actu['type_contenu'] === 'evenement' && !empty($actu['date_debut'])): ?>
                                Date : <strong><?= date('d/m/Y à H:i', strtotime($actu['date_debut'])) ?></strong>
                                <?php if (!empty($actu['date_fin'])): ?>
                                    (fin : <?= date('d/m/Y H:i', strtotime($actu['date_fin'])) ?>)
                                <?php endif; ?>
                            <?php else: ?>
                                Publié le <?= date('d/m/Y', strtotime($actu['date_creation'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>

                    <h2 style="color: var(--primary); font-size: 1.35rem; margin-bottom: 0.4rem;"><?= htmlspecialchars($actu['titre']) ?></h2>

                    <?php if (!empty($actu['organisateur']) || !empty($actu['lieu'])): ?>
                        <p style="font-size: 0.92rem; color: var(--muted); margin-bottom: 0.8rem;">
                            <?php if (!empty($actu['organisateur'])): ?>
                                <strong>Organisateur :</strong> <?= htmlspecialchars($actu['organisateur']) ?>
                            <?php endif; ?>
                            <?php if (!empty($actu['organisateur']) && !empty($actu['lieu'])): ?> | <?php endif; ?>
                            <?php if (!empty($actu['lieu'])): ?>
                                <strong>Lieu :</strong> <?= htmlspecialchars($actu['lieu']) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <p style="line-height: 1.65; margin-bottom: 1rem;"><?= nl2br(htmlspecialchars($actu['description'])) ?></p>

                    <?php if (!empty($actu['public_tarif']) || !empty($actu['lien_inscription'])): ?>
                        <div style="background: #f8f9fa; padding: 0.8rem 1rem; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem;">
                            <?php if (!empty($actu['public_tarif'])): ?>
                                <span style="font-size: 0.9rem;"><strong>Public & Tarif :</strong> <?= htmlspecialchars($actu['public_tarif']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($actu['lien_inscription'])): ?>
                                <a href="<?= htmlspecialchars($actu['lien_inscription']) ?>" target="_blank" rel="noopener noreferrer" class="btn-login" style="background: var(--secondary); color: #fff; font-size: 0.85rem;">Informations / Inscription</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="form-box" style="text-align: center; padding: 3rem;">
            <p style="color: var(--muted); margin-bottom: 1rem;">Aucune actualité ou événement publié ne correspond à vos critères.</p>
            <a href="contribuer.php?type=evenement" class="btn-login" style="background: var(--secondary); color: #fff;">Proposer un événement</a>
        </div>
    <?php endif; ?>
</div>

<!-- VUE CALENDRIER MENSUEL -->
<?php
$mois_prec = $mois_cal - 1;
$annee_prec = $annee_cal;
if ($mois_prec < 1) { $mois_prec = 12; $annee_prec--; }

$mois_suiv = $mois_cal + 1;
$annee_suiv = $annee_cal;
if ($mois_suiv > 12) { $mois_suiv = 1; $annee_suiv++; }

$premier_jour_semaine = (int)date('N', strtotime($debut_mois)); // 1 (Lundi) à 7 (Dimanche)
$nb_jours_mois = (int)date('t', strtotime($debut_mois));
?>
<div id="vue-actus-cal" class="form-box" style="display: <?= isset($_GET['mois']) ? 'block' : 'none' ?>;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
        <a href="actualites.php?mois=<?= $mois_prec ?>&annee=<?= $annee_prec ?>" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">&larr; Mois précédent</a>
        <h2 style="color: var(--primary);"><?= $noms_mois[$mois_cal] . ' ' . $annee_cal ?></h2>
        <a href="actualites.php?mois=<?= $mois_suiv ?>&annee=<?= $annee_suiv ?>" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">Mois suivant &rarr;</a>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 700px;">
            <thead>
                <tr style="background: var(--primary); color: #fff;">
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Lundi</th>
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Mardi</th>
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Mercredi</th>
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Jeudi</th>
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Vendredi</th>
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Samedi</th>
                    <th style="padding: 0.6rem; border: 1px solid var(--border);">Dimanche</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <?php
                $case = 1;
                for ($vide = 1; $vide < $premier_jour_semaine; $vide++, $case++) {
                    echo '<td style="height: 100px; background: #f8f9fa; border: 1px solid var(--border);"></td>';
                }
                for ($jour = 1; $jour <= $nb_jours_mois; $jour++, $case++) {
                    $est_aujourd_hui = ($jour == date('j') && $mois_cal == date('m') && $annee_cal == date('Y'));
                    $bg_cell = $est_aujourd_hui ? '#e3f2fd' : '#ffffff';
                    echo '<td style="height: 100px; vertical-align: top; padding: 0.4rem; border: 1px solid var(--border); background: ' . $bg_cell . ';">';
                    echo '<strong style="font-size: 0.85rem; color: var(--muted);">' . $jour . '</strong>';
                    if (isset($events_mois[$jour])) {
                        foreach ($events_mois[$jour] as $ev) {
                            echo '<div style="margin-top: 4px; padding: 3px 5px; background: var(--secondary); color: #fff; border-radius: 3px; font-size: 0.78rem; line-height: 1.25;">';
                            echo htmlspecialchars($ev['titre']);
                            echo '</div>';
                        }
                    }
                    echo '</td>';
                    if ($case % 7 === 0 && $jour < $nb_jours_mois) {
                        echo '</tr><tr>';
                    }
                }
                while (($case - 1) % 7 !== 0) {
                    echo '<td style="height: 100px; background: #f8f9fa; border: 1px solid var(--border);"></td>';
                    $case++;
                }
                ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function basculerActus(mode) {
    var liste = document.getElementById('vue-actus-liste');
    var cal = document.getElementById('vue-actus-cal');
    var btnListe = document.getElementById('btn-actus-liste');
    var btnCal = document.getElementById('btn-actus-cal');

    if (mode === 'calendrier') {
        liste.style.display = 'none';
        cal.style.display = 'block';
        btnCal.style.background = 'var(--primary)';
        btnCal.style.color = '#fff';
        btnListe.style.background = '#fff';
        btnListe.style.color = 'var(--text)';
    } else {
        liste.style.display = 'block';
        cal.style.display = 'none';
        btnListe.style.background = 'var(--primary)';
        btnListe.style.color = '#fff';
        btnCal.style.background = '#fff';
        btnCal.style.color = 'var(--text)';
    }
}

<?php if (isset($_GET['mois'])): ?>
    basculerActus('calendrier');
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>