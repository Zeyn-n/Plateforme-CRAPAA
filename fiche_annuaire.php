<?php
require_once 'config/db.php';

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!in_array($type, ['structure', 'professionnel']) || $id <= 0) {
    header('Location: annuaire.php');
    exit;
}

if ($type === 'structure') {
    $stmt = $pdo->prepare("SELECT * FROM structures WHERE id = :id AND statut = 'publie'");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();

    // Récupérer les professionnels rattachés à cette structure
    $pros_rattaches = [];
    if ($item) {
        $stmt_p = $pdo->prepare("SELECT id, nom, prenom, diplome, specialites FROM professionnels WHERE structure_id = :sid AND statut = 'publie'");
        $stmt_p->execute([':sid' => $id]);
        $pros_rattaches = $stmt_p->fetchAll();
    }
} else {
    $stmt = $pdo->prepare("SELECT p.*, s.nom AS structure_nom, s.id AS structure_id_ref 
                           FROM professionnels p 
                           LEFT JOIN structures s ON p.structure_id = s.id 
                           WHERE p.id = :id AND p.statut = 'publie'");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();
}

if (!$item) {
    header('Location: annuaire.php');
    exit;
}

include 'includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="annuaire.php" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">&larr; Retour à l'annuaire</a>
</div>

<article class="form-box" style="border-top: 5px solid <?= $type === 'structure' ? 'var(--primary)' : 'var(--secondary)' ?>;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
        <div>
            <?php if ($type === 'structure'): ?>
                <span class="badge badge-blue">Structure APA</span>
                <span class="badge badge-purple"><?= htmlspecialchars($item['type_etablissement']) ?></span>
            <?php else: ?>
                <span class="badge badge-green">Professionnel EAPA</span>
            <?php endif; ?>
        </div>
        <?php if (!empty($item['date_verification'])): ?>
            <small style="color: var(--muted);">Coordonnées vérifiées le : <?= date('d/m/Y', strtotime($item['date_verification'])) ?></small>
        <?php endif; ?>
    </div>

    <h1 style="color: var(--primary); margin-bottom: 0.4rem; font-size: 1.8rem;">
        <?= $type === 'structure' ? htmlspecialchars($item['nom']) : htmlspecialchars($item['prenom'] . ' ' . $item['nom']) ?>
    </h1>

    <?php if ($type === 'professionnel'): ?>
        <p style="font-size: 1.05rem; color: var(--secondary); font-weight: 600; margin-bottom: 1.2rem;">
            <?= htmlspecialchars($item['diplome']) ?>
        </p>
        <?php if (!empty($item['structure_nom'])): ?>
            <p style="margin-bottom: 1rem;">
                <strong>Structure de rattachement :</strong> 
                <a href="fiche_annuaire.php?type=structure&id=<?= (int)$item['structure_id_ref'] ?>" style="color: var(--primary);">
                    <?= htmlspecialchars($item['structure_nom']) ?>
                </a>
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Bloc Coordonnées & Localisation -->
    <div style="background: #f8f9fa; padding: 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        <div>
            <strong>Localisation :</strong><br>
            <?= !empty($item['adresse']) ? htmlspecialchars($item['adresse']) . '<br>' : '' ?>
            <?= htmlspecialchars($item['code_postal'] . ' ' . $item['ville']) ?>
        </div>
        <div>
            <strong>Contact professionnel :</strong><br>
            <?php if (!empty($item['telephone'])): ?>
                Tél : <?= htmlspecialchars($item['telephone']) ?><br>
            <?php endif; ?>
            <?php if (!empty($item['email'])): ?>
                Email : <a href="mailto:<?= htmlspecialchars($item['email']) ?>" style="color: var(--primary);"><?= htmlspecialchars($item['email']) ?></a><br>
            <?php endif; ?>
            <?php if (!empty($item['site_web'])): ?>
                Site : <a href="<?= htmlspecialchars($item['site_web']) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--secondary);">Consulter le site web</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Détails de l'accompagnement -->
    <div style="display: grid; gap: 1.2rem;">
        <?php if ($type === 'professionnel' && !empty($item['specialites'])): ?>
            <div>
                <h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.3rem;">Spécialités et domaines d'intervention</h2>
                <p><?= nl2br(htmlspecialchars($item['specialites'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($item['activites'])): ?>
            <div>
                <h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.3rem;">Activités proposées</h2>
                <p><?= nl2br(htmlspecialchars($item['activites'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($item['publics'])): ?>
            <div>
                <h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.3rem;">Publics accompagnés</h2>
                <p><?= nl2br(htmlspecialchars($item['publics'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($item['pathologies'])): ?>
            <div>
                <h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.3rem;">Pathologies ou situations particulières prises en charge</h2>
                <p><?= nl2br(htmlspecialchars($item['pathologies'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($item['modalites_tarifs'])): ?>
            <div>
                <h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.3rem;">Modalités d'accompagnement, tarifs et financements</h2>
                <p><?= nl2br(htmlspecialchars($item['modalites_tarifs'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($type === 'structure' && count($pros_rattaches) > 0): ?>
            <div style="padding-top: 1rem; border-top: 1px solid var(--border);">
                <h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.6rem;">Professionnels exerçant dans cette structure</h2>
                <ul style="list-style: none; display: grid; gap: 0.5rem;">
                    <?php foreach ($pros_rattaches as $p): ?>
                        <li>
                            <a href="fiche_annuaire.php?type=professionnel&id=<?= $p['id'] ?>" style="font-weight: 600; color: var(--secondary); text-decoration: none;">
                                &rarr; <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                            </a>
                            <span style="color: var(--muted); font-size: 0.9rem;">(<?= htmlspecialchars($p['diplome']) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border); text-align: right;">
        <a href="contribuer.php?type=modification" style="color: var(--muted); font-size: 0.88rem;">Signaler une mise à jour sur cette fiche</a>
    </div>
</article>

<?php include 'includes/footer.php'; ?>