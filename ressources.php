<?php
require_once 'config/db.php';

// Récupération des paramètres de filtres cumulables
$cat = $_GET['cat'] ?? ''; // 'scientifique', 'pratique', ou '' (tout)
$q = trim($_GET['q'] ?? '');
$type_doc = $_GET['type_doc'] ?? '';
$thematique = $_GET['thematique'] ?? '';
$annee = $_GET['annee'] ?? '';
$tri = $_GET['tri'] ?? 'recent';
$page = max(1, (int)($_GET['page'] ?? 1));
$par_page = 6;
$offset = ($page - 1) * $par_page;

// Construction dynamique de la requête SQL
$where = ["statut = 'publie'"];
$params = [];

if (in_array($cat, ['scientifique', 'pratique'])) {
    $where[] = "categorie_principale = :cat";
    $params[':cat'] = $cat;
}
if (!empty($q)) {
    $where[] = "(titre LIKE :q OR auteurs LIKE :q OR mots_cles LIKE :q OR pathologie LIKE :q OR population LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
if (!empty($type_doc)) {
    $where[] = "type_document = :type_doc";
    $params[':type_doc'] = $type_doc;
}
if (!empty($thematique)) {
    $where[] = "thematique = :thematique";
    $params[':thematique'] = $thematique;
}
if (!empty($annee)) {
    $where[] = "annee_publication = :annee";
    $params[':annee'] = (int)$annee;
}

$where_sql = implode(' AND ', $where);
$order_sql = ($tri === 'ancien') ? "annee_publication ASC, date_creation ASC" : "date_creation DESC, annee_publication DESC";

// Comptage total pour la pagination
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM ressources WHERE $where_sql");
$stmt_count->execute($params);
$total_res = (int)$stmt_count->fetchColumn();
$total_pages = max(1, (int)ceil($total_res / $par_page));

// Récupération des résultats de la page courante
$sql = "SELECT * FROM ressources WHERE $where_sql ORDER BY $order_sql LIMIT $par_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ressources = $stmt->fetchAll();

// Listes des thématiques du cahier des charges
$thematiques_liste = [
    'Physiologie et activité physique',
    'Biomécanique',
    'Psychologie',
    'Sociologie',
    'Pédagogie',
    'Santé publique',
    'Handicap et inclusion',
    'Pathologies chroniques',
    'Vieillissement'
];

include 'includes/header.php';
?>

<section class="hero">
    <h1>Bibliothèque Documentaire & Outils Pratiques</h1>
    <p>Explorez les travaux scientifiques, mémoires universitaires, fiches d'exercices et outils d'évaluation en APA.</p>
</section>

<!-- Onglets de navigation rapide -->
<div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
    <a href="ressources.php" class="btn-login" style="<?= $cat === '' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Toutes les ressources</a>
    <a href="ressources.php?cat=scientifique" class="btn-login" style="<?= $cat === 'scientifique' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Ressources scientifiques & Mémoires</a>
    <a href="ressources.php?cat=pratique" class="btn-login" style="<?= $cat === 'pratique' ? 'background: var(--secondary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Outils pratiques & Évaluation</a>
</div>

<!-- Formulaire de filtres cumulables -->
<form action="ressources.php" method="GET" class="form-box" style="margin-bottom: 2rem; padding: 1.5rem;">
    <?php if (!empty($cat)): ?>
        <input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>">
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Recherche (titre, auteur, mot-clé)</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ex : Parkinson, équilibre..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Type de document</label>
            <select name="type_doc" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="">Tous les types</option>
                <optgroup label="Scientifique & Universitaire">
                    <option value="Article scientifique" <?= $type_doc === 'Article scientifique' ? 'selected' : '' ?>>Article scientifique</option>
                    <option value="Mémoire" <?= $type_doc === 'Mémoire' ? 'selected' : '' ?>>Mémoire (Licence / Master)</option>
                    <option value="Thèse" <?= $type_doc === 'Thèse' ? 'selected' : '' ?>>Thèse</option>
                    <option value="Rapport de stage" <?= $type_doc === 'Rapport de stage' ? 'selected' : '' ?>>Rapport de stage</option>
                    <option value="Recommandations" <?= $type_doc === 'Recommandations' ? 'selected' : '' ?>>Recommandations institutionnelles</option>
                </optgroup>
                <optgroup label="Pratique & Terrain">
                    <option value="Fiche exercice" <?= $type_doc === 'Fiche exercice' ? 'selected' : '' ?>>Fiche d'exercices / Séance</option>
                    <option value="Outil d'évaluation" <?= $type_doc === "Outil d'évaluation" ? 'selected' : '' ?>>Test / Outil d'évaluation</option>
                    <option value="Programme APA" <?= $type_doc === 'Programme APA' ? 'selected' : '' ?>>Programme d'activité physique</option>
                </optgroup>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Thématique</label>
            <select name="thematique" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="">Toutes les thématiques</option>
                <?php foreach ($thematiques_liste as $th): ?>
                    <option value="<?= htmlspecialchars($th) ?>" <?= $thematique === $th ? 'selected' : '' ?>><?= htmlspecialchars($th) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Année</label>
            <input type="number" name="annee" min="1990" max="<?= date('Y') ?>" value="<?= htmlspecialchars($annee) ?>" placeholder="Ex : 2025" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Trier par</label>
            <select name="tri" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                <option value="ancien" <?= $tri === 'ancien' ? 'selected' : '' ?>>Plus anciens</option>
            </select>
        </div>
    </div>

    <div style="display: flex; gap: 0.8rem; align-items: center;">
        <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Appliquer les filtres</button>
        <a href="ressources.php<?= !empty($cat) ? '?cat=' . urlencode($cat) : '' ?>" style="color: var(--muted); font-size: 0.9rem;">Réinitialiser</a>
        <span style="margin-left: auto; color: var(--muted); font-size: 0.9rem;"><strong><?= $total_res ?></strong> ressource(s) trouvée(s)</span>
    </div>
</form>

<!-- Grille de résultats -->
<?php if (count($ressources) > 0): ?>
    <div class="modules-grid">
        <?php foreach ($ressources as $res): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top-color: <?= $res['categorie_principale'] === 'scientifique' ? 'var(--primary)' : 'var(--secondary)' ?>;">
                <div>
                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                        <span class="badge <?= $res['categorie_principale'] === 'scientifique' ? 'badge-blue' : 'badge-green' ?>">
                            <?= htmlspecialchars($res['type_document']) ?>
                        </span>
                        <?php if (!empty($res['annee_publication'])): ?>
                            <span class="badge badge-purple"><?= (int)$res['annee_publication'] ?></span>
                        <?php endif; ?>
                    </div>

                    <h2 style="font-size: 1.15rem; margin-bottom: 0.4rem;">
                        <a href="ressource_detail.php?id=<?= $res['id'] ?>" style="color: var(--primary); text-decoration: none;">
                            <?= htmlspecialchars($res['titre']) ?>
                        </a>
                    </h2>

                    <p style="font-size: 0.88rem; color: var(--muted); margin-bottom: 0.6rem;">
                        Par <strong><?= htmlspecialchars($res['auteurs']) ?></strong>
                    </p>

                    <?php if (!empty($res['resume'])): ?>
                        <p style="font-size: 0.92rem; color: var(--text); margin-bottom: 1rem;">
                            <?= htmlspecialchars(mb_strimwidth($res['resume'], 0, 140, '...')) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div style="padding-top: 0.8rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <a href="ressource_detail.php?id=<?= $res['id'] ?>" class="btn-login" style="background: var(--primary); color: #fff; font-size: 0.85rem;">Voir la fiche</a>
                    <?php if ($res['mode_diffusion'] === 'pdf_heberge' && !empty($res['fichier_path'])): ?>
                        <a href="<?= htmlspecialchars($res['fichier_path']) ?>" download style="color: var(--secondary); font-weight: 600; font-size: 0.85rem; text-decoration: none;">Télécharger PDF</a>
                    <?php elseif ($res['mode_diffusion'] === 'lien_externe'): ?>
                        <span style="color: var(--muted); font-size: 0.82rem;">Lien externe</span>
                    <?php else: ?>
                        <span style="color: var(--muted); font-size: 0.82rem;">Notice biblio</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <?php
                $query_params = $_GET;
                $query_params['page'] = $p;
                $url_page = 'ressources.php?' . http_build_query($query_params);
                ?>
                <a href="<?= htmlspecialchars($url_page) ?>" class="btn-login" style="<?= $p === $page ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="form-box" style="text-align: center; padding: 3rem;">
        <p style="color: var(--muted); margin-bottom: 1rem;">Aucune ressource publiée ne correspond à vos critères.</p>
        <a href="contribuer.php" class="btn-login" style="background: var(--secondary); color: #fff;">Proposer une ressource</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>