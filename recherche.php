<?php
require_once 'config/db.php';

$q = trim($_GET['q'] ?? '');
$resultats_globaux = [];

if (!empty($q)) {
    $terme = '%' . $q . '%';

    // 1. Ressources scientifiques et outils
    $stmt = $pdo->prepare("SELECT id, titre, resume AS description, 'ressource' AS type_item, categorie_principale AS sous_type FROM ressources WHERE statut = 'publie' AND (titre LIKE :q OR auteurs LIKE :q OR mots_cles LIKE :q OR pathologie LIKE :q OR population LIKE :q)");
    $stmt->execute([':q' => $terme]);
    foreach ($stmt->fetchAll() as $row) {
        $row['url'] = 'ressource_detail.php?id=' . $row['id'];
        $row['badge'] = ($row['sous_type'] === 'scientifique') ? 'Ressource scientifique' : 'Outil pratique';
        $resultats_globaux[] = $row;
    }

    // 2. Structures APA
    $stmt = $pdo->prepare("SELECT id, nom AS titre, CONCAT(type_etablissement, ' - ', ville) AS description, 'structure' AS type_item FROM structures WHERE statut = 'publie' AND (nom LIKE :q OR ville LIKE :q OR activites LIKE :q OR pathologies LIKE :q)");
    $stmt->execute([':q' => $terme]);
    foreach ($stmt->fetchAll() as $row) {
        $row['url'] = 'fiche_annuaire.php?type=structure&id=' . $row['id'];
        $row['badge'] = 'Structure APA';
        $resultats_globaux[] = $row;
    }

    // 3. Professionnels EAPA
    $stmt = $pdo->prepare("SELECT id, CONCAT(prenom, ' ', nom) AS titre, CONCAT(diplome, ' - ', ville) AS description, 'professionnel' AS type_item FROM professionnels WHERE statut = 'publie' AND (nom LIKE :q OR prenom LIKE :q OR specialites LIKE :q OR ville LIKE :q OR pathologies LIKE :q)");
    $stmt->execute([':q' => $terme]);
    foreach ($stmt->fetchAll() as $row) {
        $row['url'] = 'fiche_annuaire.php?type=professionnel&id=' . $row['id'];
        $row['badge'] = 'Professionnel EAPA';
        $resultats_globaux[] = $row;
    }

    // 4. Offres d'emploi, stage, formation
    $stmt = $pdo->prepare("SELECT id, titre, CONCAT(organisme, ' - ', lieu) AS description, 'opportunite' AS type_item, categorie AS sous_type FROM opportunites WHERE statut = 'publie' AND date_expiration >= CURDATE() AND (titre LIKE :q OR organisme LIKE :q OR description LIKE :q)");
    $stmt->execute([':q' => $terme]);
    foreach ($stmt->fetchAll() as $row) {
        $row['url'] = 'opportunites.php';
        $row['badge'] = 'Offre ' . $row['sous_type'];
        $resultats_globaux[] = $row;
    }

    // 5. Actualités et événements
    $stmt = $pdo->prepare("SELECT id, titre, description, 'actualite' AS type_item, type_contenu AS sous_type FROM actualites_evenements WHERE statut = 'publie' AND (titre LIKE :q OR description LIKE :q)");
    $stmt->execute([':q' => $terme]);
    foreach ($stmt->fetchAll() as $row) {
        $row['url'] = 'actualites.php';
        $row['badge'] = ($row['sous_type'] === 'evenement') ? 'Événement' : 'Actualité';
        $resultats_globaux[] = $row;
    }
}

include 'includes/header.php';
?>

<section class="hero">
    <h1>Résultats de la recherche globale</h1>
    <p>Recherche transversale pour le terme : <strong><?= htmlspecialchars($q) ?></strong></p>
</section>

<!-- Formulaire de recherche rapide -->
<form action="recherche.php" method="GET" class="form-box" style="margin-bottom: 2rem; padding: 1.2rem;">
    <div style="display: flex; gap: 0.8rem;">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Affiner votre recherche..." required style="flex: 1; padding: 0.6rem; border: 1px solid var(--border); border-radius: 4px;">
        <button type="submit" class="btn-login" style="background: var(--primary); color: #fff;">Rechercher</button>
    </div>
</form>

<?php if (!empty($q)): ?>
    <h2 style="margin-bottom: 1rem; color: var(--primary);"><?= count($resultats_globaux) ?> résultat(s) trouvé(s)</h2>

    <?php if (count($resultats_globaux) > 0): ?>
        <div style="display: grid; gap: 1.2rem;">
            <?php foreach ($resultats_globaux as $res): ?>
                <article class="form-box" style="padding: 1.2rem; border-left: 4px solid var(--primary);">
                    <span class="badge badge-blue"><?= htmlspecialchars($res['badge']) ?></span>
                    <h3 style="margin: 0.3rem 0;">
                        <a href="<?= htmlspecialchars($res['url']) ?>" style="color: var(--primary); text-decoration: none;">
                            <?= htmlspecialchars($res['titre']) ?>
                        </a>
                    </h3>
                    <p style="color: var(--muted); font-size: 0.92rem;"><?= htmlspecialchars(mb_strimwidth($res['description'], 0, 180, '...')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="form-box" style="text-align: center; padding: 3rem;">
            <p style="color: var(--muted); margin-bottom: 1rem;">Aucun résultat ne correspond à votre recherche sur l'ensemble de la plateforme.</p>
            <a href="annuaire.php" class="btn-login" style="background: var(--secondary); color: #fff;">Consulter l'annuaire régional</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>