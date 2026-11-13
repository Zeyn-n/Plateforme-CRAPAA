<?php
require_once 'config/db.php';

$type_partage = trim($_GET['type'] ?? '');

$where = ["statut = 'publie'"];
$params = [];

if (!empty($type_partage)) {
    $where[] = "type_partage = :tp";
    $params[':tp'] = $type_partage;
}

$sql = "SELECT * FROM temoignages WHERE " . implode(' AND ', $where) . " ORDER BY date_creation DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$temoignages = $stmt->fetchAll();

$types_liste = [
    'Témoignage professionnel',
    'Retour d\'expérience',
    'Projet de terrain',
    'Interview',
    'Initiative étudiante'
];

include 'includes/header.php';
?>

<section class="hero">
    <h1>Communauté & Partage d'Expériences</h1>
    <p>Valorisation des parcours professionnels, projets de terrain, interviews et initiatives étudiantes en APA en Alsace.</p>
</section>

<!-- Barre d'actions et filtres -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem; margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="communaute.php" class="btn-login" style="<?= $type_partage === '' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Tous les partages</a>
        <?php foreach ($types_liste as $tl): ?>
            <a href="communaute.php?type=<?= urlencode($tl) ?>" class="btn-login" style="<?= $type_partage === $tl ? 'background: var(--secondary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">
                <?= htmlspecialchars($tl) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <a href="contribuer.php?type=temoignage" class="btn-login" style="background: var(--secondary); color: #fff;">+ Partager mon expérience</a>
</div>

<!-- Liste des témoignages et projets -->
<?php if (count($temoignages) > 0): ?>
    <div class="modules-grid">
        <?php foreach ($temoignages as $tem): ?>
            <article class="card" style="border-top-color: var(--secondary); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <?php if (!empty($tem['type_partage'])): ?>
                        <span class="badge badge-green"><?= htmlspecialchars($tem['type_partage']) ?></span>
                    <?php endif; ?>
                    <h2 style="font-size: 1.25rem; margin: 0.4rem 0; color: var(--primary);"><?= htmlspecialchars($tem['titre']) ?></h2>
                    <p style="font-size: 0.88rem; color: var(--muted); margin-bottom: 1rem;">
                        Par <strong><?= htmlspecialchars($tem['auteur_nom']) ?></strong>
                        <?php if (!empty($tem['auteur_profil'])): ?>
                            (<?= htmlspecialchars($tem['auteur_profil']) ?>)
                        <?php endif; ?>
                    </p>
                    <div style="color: var(--text); line-height: 1.65; font-size: 0.96rem;">
                        <?= nl2br(htmlspecialchars($tem['contenu'])) ?>
                    </div>
                </div>
                <div style="margin-top: 1.2rem; padding-top: 0.7rem; border-top: 1px solid var(--border); font-size: 0.82rem; color: var(--muted);">
                    Publié le <?= date('d/m/Y', strtotime($tem['date_creation'])) ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="form-box" style="text-align: center; padding: 3rem;">
        <p style="color: var(--muted); margin-bottom: 1rem;">Aucun témoignage ou retour d'expérience publié pour le moment.</p>
        <a href="contribuer.php?type=temoignage" class="btn-login" style="background: var(--primary); color: #fff;">Proposer un témoignage ou un projet</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>