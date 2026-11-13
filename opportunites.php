<?php
require_once 'config/db.php';

// 1. Archivage automatique des offres arrivées à échéance
$pdo->exec("UPDATE opportunites SET statut = 'archive' WHERE statut = 'publie' AND date_expiration < CURDATE()");

// 2. Récupération des filtres
$categorie = $_GET['categorie'] ?? ''; // 'emploi', 'stage', 'formation' ou ''
$lieu = trim($_GET['lieu'] ?? '');
$type_contrat = trim($_GET['type_contrat'] ?? '');
$niveau_etude = trim($_GET['niveau_etude'] ?? '');
$q = trim($_GET['q'] ?? '');

$where = ["statut = 'publie'", "date_expiration >= CURDATE()"];
$params = [];

if (in_array($categorie, ['emploi', 'stage', 'formation'])) {
    $where[] = "categorie = :categorie";
    $params[':categorie'] = $categorie;
}
if (!empty($lieu)) {
    $where[] = "lieu LIKE :lieu";
    $params[':lieu'] = '%' . $lieu . '%';
}
if (!empty($type_contrat)) {
    $where[] = "type_contrat LIKE :tc";
    $params[':tc'] = '%' . $type_contrat . '%';
}
if (!empty($niveau_etude)) {
    $where[] = "niveau_etude LIKE :ne";
    $params[':ne'] = '%' . $niveau_etude . '%';
}
if (!empty($q)) {
    $where[] = "(titre LIKE :q OR organisme LIKE :q OR description LIKE :q OR public_concerne LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$sql = "SELECT * FROM opportunites WHERE " . implode(' AND ', $where) . " ORDER BY date_creation DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll();

$labels_cat = [
    'emploi' => ['label' => "Offre d'emploi", 'badge' => 'badge-blue'],
    'stage' => ['label' => 'Offre de stage', 'badge' => 'badge-green'],
    'formation' => ['label' => 'Formation', 'badge' => 'badge-purple']
];

include 'includes/header.php';
?>

<section class="hero">
    <h1>Emploi, Stages & Formations APA</h1>
    <p>Consultez les opportunités professionnelles, les offres de stages universitaires et les formations continues en Alsace.</p>
</section>

<!-- Onglets de catégories + Lien vers contribution -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem; margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="opportunites.php" class="btn-login" style="<?= $categorie === '' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Toutes les opportunités</a>
        <a href="opportunites.php?categorie=emploi" class="btn-login" style="<?= $categorie === 'emploi' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Offres d'emploi</a>
        <a href="opportunites.php?categorie=stage" class="btn-login" style="<?= $categorie === 'stage' ? 'background: var(--secondary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Offres de stage</a>
        <a href="opportunites.php?categorie=formation" class="btn-login" style="<?= $categorie === 'formation' ? 'background: #6a1b9a; color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Formations</a>
    </div>
    <a href="contribuer.php?type=opportunite" class="btn-login" style="background: var(--secondary); color: #fff;">+ Publier une offre ou formation</a>
</div>

<!-- Barre de filtres cumulables -->
<form action="opportunites.php" method="GET" class="form-box" style="margin-bottom: 2rem; padding: 1.5rem;">
    <?php if (!empty($categorie)): ?>
        <input type="hidden" name="categorie" value="<?= htmlspecialchars($categorie) ?>">
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Mots-clés / Thématique</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ex : Oncologie, EAPA, EHPAD..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Localisation</label>
            <input type="text" name="lieu" value="<?= htmlspecialchars($lieu) ?>" placeholder="Ex : Strasbourg, Colmar, Distanciel..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Contrat / Modalité</label>
            <input type="text" name="type_contrat" value="<?= htmlspecialchars($type_contrat) ?>" placeholder="Ex : CDI, CDD, Temps partiel..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Niveau d'études / Diplôme</label>
            <input type="text" name="niveau_etude" value="<?= htmlspecialchars($niveau_etude) ?>" placeholder="Ex : Licence APAS, Master 2..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>
    </div>

    <div style="display: flex; gap: 0.8rem; align-items: center;">
        <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Filtrer les annonces</button>
        <a href="opportunites.php<?= !empty($categorie) ? '?categorie=' . urlencode($categorie) : '' ?>" style="color: var(--muted); font-size: 0.9rem;">Réinitialiser</a>
        <span style="margin-left: auto; color: var(--muted); font-size: 0.9rem;"><strong><?= count($offres) ?></strong> annonce(s) active(s)</span>
    </div>
</form>

<!-- Affichage des offres -->
<?php if (count($offres) > 0): ?>
    <div style="display: grid; gap: 1.5rem;">
        <?php foreach ($offres as $offre): ?>
            <?php $meta = $labels_cat[$offre['categorie']] ?? ['label' => $offre['categorie'], 'badge' => 'badge-blue']; ?>
            <article class="form-box" style="border-left: 5px solid var(--primary);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.6rem;">
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                        <span class="badge <?= $meta['badge'] ?>"><?= htmlspecialchars($meta['label']) ?></span>
                        <?php if (!empty($offre['type_contrat'])): ?>
                            <span class="badge badge-orange"><?= htmlspecialchars($offre['type_contrat']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($offre['duree'])): ?>
                            <span style="font-size: 0.85rem; color: var(--muted);">Durée : <?= htmlspecialchars($offre['duree']) ?></span>
                        <?php endif; ?>
                    </div>
                    <small style="color: var(--muted);">
                        Publié le <?= date('d/m/Y', strtotime($offre['date_creation'])) ?> — Échéance : <strong><?= date('d/m/Y', strtotime($offre['date_expiration'])) ?></strong>
                    </small>
                </div>

                <h2 style="color: var(--primary); font-size: 1.35rem; margin-bottom: 0.3rem;"><?= htmlspecialchars($offre['titre']) ?></h2>
                <p style="font-weight: 600; color: var(--text); margin-bottom: 0.8rem;">
                    Organisme : <?= htmlspecialchars($offre['organisme']) ?> | Lieu : <?= htmlspecialchars($offre['lieu']) ?>
                </p>

                <!-- Détails complémentaires -->
                <div style="background: #f8f9fa; padding: 0.8rem 1rem; border-radius: 6px; margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 1.5rem; font-size: 0.9rem;">
                    <?php if (!empty($offre['niveau_etude'])): ?>
                        <div><strong>Niveau / Diplôme :</strong> <?= htmlspecialchars($offre['niveau_etude']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($offre['public_concerne'])): ?>
                        <div><strong>Public concerné :</strong> <?= htmlspecialchars($offre['public_concerne']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($offre['gratification_cout'])): ?>
                        <div><strong><?= $offre['categorie'] === 'formation' ? 'Coût :' : 'Gratification / Rémunération :' ?></strong> <?= htmlspecialchars($offre['gratification_cout']) ?></div>
                    <?php endif; ?>
                </div>

                <p style="line-height: 1.65; margin-bottom: 1.2rem;"><?= nl2br(htmlspecialchars($offre['description'])) ?></p>

                <div style="padding-top: 0.8rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <span style="font-size: 0.92rem;"><strong>Contact / Candidature :</strong> <?= htmlspecialchars($offre['contact_lien']) ?></span>
                    <?php if (filter_var($offre['contact_lien'], FILTER_VALIDATE_URL)): ?>
                        <a href="<?= htmlspecialchars($offre['contact_lien']) ?>" target="_blank" rel="noopener noreferrer" class="btn-login" style="background: var(--primary); color: #fff;">Accéder au lien</a>
                    <?php elseif (filter_var($offre['contact_lien'], FILTER_VALIDATE_EMAIL)): ?>
                        <a href="mailto:<?= htmlspecialchars($offre['contact_lien']) ?>" class="btn-login" style="background: var(--secondary); color: #fff;">Postuler par email</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="form-box" style="text-align: center; padding: 3rem;">
        <p style="color: var(--muted); margin-bottom: 1rem;">Aucune offre d'emploi, de stage ou de formation active ne correspond à votre recherche.</p>
        <a href="contribuer.php?type=opportunite" class="btn-login" style="background: var(--primary); color: #fff;">Déposer une annonce</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>