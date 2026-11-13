<?php
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM ressources WHERE id = :id AND statut = 'publie'");
$stmt->execute([':id' => $id]);
$res = $stmt->fetch();

if (!$res) {
    header('Location: ressources.php');
    exit;
}

$labels_validation = [
    'valide_scientifiquement' => 'Outil validé scientifiquement',
    'adapte' => 'Outil adapté',
    'pratique_terrain' => 'Exemple de pratique professionnelle'
];

include 'includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="ressources.php?cat=<?= htmlspecialchars($res['categorie_principale']) ?>" class="btn-login" style="background: #fff; border: 1px solid var(--border); color: var(--text);">&larr; Retour au catalogue</a>
</div>

<article class="form-box" style="border-top: 5px solid var(--primary);">
    <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 0.8rem;">
        <span class="badge badge-blue">
            <?= $res['categorie_principale'] === 'scientifique' ? 'Ressource scientifique' : 'Outil pratique' ?>
        </span>
        <span class="badge badge-purple"><?= htmlspecialchars($res['type_document']) ?></span>
        <?php if (!empty($res['thematique'])): ?>
            <span class="badge badge-green"><?= htmlspecialchars($res['thematique']) ?></span>
        <?php endif; ?>
        <?php if ($res['categorie_principale'] === 'pratique' && !empty($res['niveau_validation'])): ?>
            <span class="badge badge-orange"><?= htmlspecialchars($labels_validation[$res['niveau_validation']] ?? $res['niveau_validation']) ?></span>
        <?php endif; ?>
    </div>

    <h1 style="color: var(--primary); margin-bottom: 0.6rem; font-size: 1.7rem;"><?= htmlspecialchars($res['titre']) ?></h1>
    
    <p style="color: var(--muted); margin-bottom: 1.5rem; font-size: 0.95rem;">
        <strong>Auteur(s) :</strong> <?= htmlspecialchars($res['auteurs']) ?>
        <?php if (!empty($res['annee_publication'])): ?> | <strong>Année :</strong> <?= (int)$res['annee_publication'] ?><?php endif; ?>
        <?php if (!empty($res['organisme'])): ?> | <strong>Organisme :</strong> <?= htmlspecialchars($res['organisme']) ?><?php endif; ?>
    </p>

    <!-- Métadonnées ciblées -->
    <div style="background: #f8f9fa; padding: 1rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <?php if (!empty($res['population'])): ?>
            <div><strong>Public / Population :</strong><br><?= htmlspecialchars($res['population']) ?></div>
        <?php endif; ?>
        <?php if (!empty($res['pathologie'])): ?>
            <div><strong>Pathologie / Problématique :</strong><br><?= htmlspecialchars($res['pathologie']) ?></div>
        <?php endif; ?>
        <?php if (!empty($res['mots_cles'])): ?>
            <div><strong>Mots-clés :</strong><br><?= htmlspecialchars($res['mots_cles']) ?></div>
        <?php endif; ?>
    </div>

    <!-- Résumé ou description générale -->
    <?php if (!empty($res['resume'])): ?>
        <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.2rem; color: var(--primary); margin-bottom: 0.5rem;">Résumé / Présentation</h2>
            <p style="line-height: 1.7;"><?= nl2br(htmlspecialchars($res['resume'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Champs spécifiques aux outils pratiques -->
    <?php if ($res['categorie_principale'] === 'pratique'): ?>
        <?php if (!empty($res['objectif'])): ?>
            <div style="margin-bottom: 1.2rem;">
                <h3 style="font-size: 1.1rem; color: var(--secondary); margin-bottom: 0.3rem;">Objectif et indications</h3>
                <p><?= nl2br(htmlspecialchars($res['objectif'])) ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($res['materiel'])): ?>
            <div style="margin-bottom: 1.2rem;">
                <h3 style="font-size: 1.1rem; color: var(--secondary); margin-bottom: 0.3rem;">Matériel nécessaire</h3>
                <p><?= nl2br(htmlspecialchars($res['materiel'])) ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($res['protocole'])): ?>
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; color: var(--secondary); margin-bottom: 0.3rem;">Protocole / Déroulement et interprétation</h3>
                <p><?= nl2br(htmlspecialchars($res['protocole'])) ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Source bibliographique -->
    <?php if (!empty($res['source_biblio'])): ?>
        <div style="margin-bottom: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
            <strong>Référence bibliographique / Source :</strong>
            <p style="color: var(--muted); font-style: italic; margin-top: 0.3rem;"><?= nl2br(htmlspecialchars($res['source_biblio'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Les 3 modalités de diffusion du cahier des charges -->
    <div style="background: #e3f2fd; padding: 1.2rem; border-radius: 6px; margin-top: 1.5rem;">
        <h3 style="font-size: 1.05rem; color: var(--primary); margin-bottom: 0.5rem;">Accès au document</h3>
        
        <?php if ($res['mode_diffusion'] === 'pdf_heberge' && !empty($res['fichier_path'])): ?>
            <p style="margin-bottom: 0.8rem;">Ce document est librement accessible et hébergé sur la plateforme CRAPAA.</p>
            <a href="<?= htmlspecialchars($res['fichier_path']) ?>" download class="btn-login" style="background: var(--primary); color: #fff;">Télécharger le document (PDF)</a>
        
        <?php elseif ($res['mode_diffusion'] === 'lien_externe' && !empty($res['lien_externe'])): ?>
            <p style="margin-bottom: 0.8rem;">Ce document est consultable directement sur le site de l'éditeur ou de l'organisme d'origine.</p>
            <a href="<?= htmlspecialchars($res['lien_externe']) ?>" target="_blank" rel="noopener noreferrer" class="btn-login" style="background: var(--secondary); color: #fff;">Consulter la publication originale</a>
        
        <?php else: ?>
            <p style="color: var(--text);"><strong>Notice bibliographique uniquement :</strong> Les droits de diffusion ne permettent pas l'hébergement direct du fichier sur le CRAPAA. Référez-vous à la source bibliographique ci-dessus pour le consulter en bibliothèque universitaire ou sur portail scientifique.</p>
        <?php endif; ?>
    </div>
</article>

<?php include 'includes/footer.php'; ?>