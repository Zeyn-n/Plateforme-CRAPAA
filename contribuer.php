<?php
require_once 'config/db.php';

$type_form = $_GET['type'] ?? 'ressource';
$message = '';
$erreur = '';

// Fonction utilitaire sécurisée pour l'upload de PDF
function gererUploadPdf($file_input) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors du transfert du fichier.");
    }
    $ext = strtolower(pathinfo($_FILES[$file_input]['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        throw new Exception("Seuls les fichiers au format PDF sont autorisés.");
    }
    if ($_FILES[$file_input]['size'] > 10 * 1024 * 1024) {
        throw new Exception("Le fichier PDF ne doit pas dépasser 10 Mo.");
    }
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }
    $nom_fichier = uniqid('doc_', true) . '.pdf';
    $destination = 'uploads/' . $nom_fichier;
    if (!move_uploaded_file($_FILES[$file_input]['tmp_name'], $destination)) {
        throw new Exception("Impossible d'enregistrer le fichier sur le serveur.");
    }
    return $destination;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_soumission = $_POST['type_soumission'] ?? '';
    $type_form = $type_soumission;

    if (!isset($_POST['consentement_droits'])) {
        $erreur = "Vous devez cocher la case confirmant les droits de diffusion et l'exactitude des informations.";
    } else {
        try {
            $auteur_id = $_SESSION['user_id'] ?? null;

            // 1. RESSOURCE SCIENTIFIQUE OU OUTIL PRATIQUE
            if ($type_soumission === 'ressource' || $type_soumission === 'outil') {
                $cat_principale = ($type_soumission === 'outil') ? 'pratique' : 'scientifique';
                $mode_diffusion = $_POST['mode_diffusion'] ?? 'reference_seule';
                $fichier_path = null;

                if ($mode_diffusion === 'pdf_heberge') {
                    $fichier_path = gererUploadPdf('fichier_pdf');
                    if (!$fichier_path) {
                        throw new Exception("Veuillez joindre un fichier PDF ou choisir un autre mode de diffusion.");
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO ressources 
                    (categorie_principale, type_document, titre, auteurs, annee_publication, organisme, thematique, population, pathologie, mots_cles, resume, objectif, materiel, protocole, niveau_validation, mode_diffusion, fichier_path, lien_externe, source_biblio, statut, auteur_id)
                    VALUES 
                    (:cat, :type_doc, :titre, :auteurs, :annee, :organisme, :thematique, :population, :pathologie, :mots_cles, :resume, :objectif, :materiel, :protocole, :niveau_val, :mode_diff, :fichier, :lien, :source, 'en_attente', :auteur_id)");

                $stmt->execute([
                    ':cat' => $cat_principale,
                    ':type_doc' => trim($_POST['type_document']),
                    ':titre' => trim($_POST['titre']),
                    ':auteurs' => trim($_POST['auteurs']),
                    ':annee' => !empty($_POST['annee_publication']) ? (int)$_POST['annee_publication'] : null,
                    ':organisme' => trim($_POST['organisme'] ?? ''),
                    ':thematique' => trim($_POST['thematique'] ?? ''),
                    ':population' => trim($_POST['population'] ?? ''),
                    ':pathologie' => trim($_POST['pathologie'] ?? ''),
                    ':mots_cles' => trim($_POST['mots_cles'] ?? ''),
                    ':resume' => trim($_POST['resume'] ?? ''),
                    ':objectif' => trim($_POST['objectif'] ?? ''),
                    ':materiel' => trim($_POST['materiel'] ?? ''),
                    ':protocole' => trim($_POST['protocole'] ?? ''),
                    ':niveau_val' => $_POST['niveau_validation'] ?? 'pratique_terrain',
                    ':mode_diff' => $mode_diffusion,
                    ':fichier' => $fichier_path,
                    ':lien' => trim($_POST['lien_externe'] ?? ''),
                    ':source' => trim($_POST['source_biblio'] ?? ''),
                    ':auteur_id' => $auteur_id
                ]);
                $message = "Votre ressource a bien été enregistrée. Elle est en attente de validation par un modérateur avant publication.";

            // 2. PROFESSIONNEL EAPA
            } elseif ($type_soumission === 'professionnel') {
                $stmt = $pdo->prepare("INSERT INTO professionnels 
                    (nom, prenom, diplome, specialites, publics, pathologies, activites, adresse, code_postal, ville, latitude, longitude, telephone, email, site_web, modalites_tarifs, statut, date_verification)
                    VALUES 
                    (:nom, :prenom, :diplome, :specialites, :publics, :pathologies, :activites, :adresse, :cp, :ville, :lat, :lng, :tel, :email, :site, :tarifs, 'en_attente', CURDATE())");

                $stmt->execute([
                    ':nom' => trim($_POST['nom']),
                    ':prenom' => trim($_POST['prenom']),
                    ':diplome' => trim($_POST['diplome']),
                    ':specialites' => trim($_POST['specialites'] ?? ''),
                    ':publics' => trim($_POST['publics'] ?? ''),
                    ':pathologies' => trim($_POST['pathologies'] ?? ''),
                    ':activites' => trim($_POST['activites'] ?? ''),
                    ':adresse' => trim($_POST['adresse'] ?? ''),
                    ':cp' => trim($_POST['code_postal']),
                    ':ville' => trim($_POST['ville']),
                    ':lat' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : 48.5839,
                    ':lng' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : 7.7455,
                    ':tel' => trim($_POST['telephone'] ?? ''),
                    ':email' => trim($_POST['email'] ?? ''),
                    ':site' => trim($_POST['site_web'] ?? ''),
                    ':tarifs' => trim($_POST['modalites_tarifs'] ?? '')
                ]);
                $message = "Votre fiche professionnelle a été soumise. Elle sera publiée dans l'annuaire après vérification par l'équipe CRAPAA.";

            // 3. STRUCTURE APA
            } elseif ($type_soumission === 'structure') {
                $stmt = $pdo->prepare("INSERT INTO structures 
                    (nom, type_etablissement, adresse, code_postal, ville, latitude, longitude, telephone, email, site_web, activites, publics, pathologies, modalites_tarifs, statut, date_verification)
                    VALUES 
                    (:nom, :type_etab, :adresse, :cp, :ville, :lat, :lng, :tel, :email, :site, :activites, :publics, :pathologies, :tarifs, 'en_attente', CURDATE())");

                $stmt->execute([
                    ':nom' => trim($_POST['nom']),
                    ':type_etab' => trim($_POST['type_etablissement']),
                    ':adresse' => trim($_POST['adresse']),
                    ':cp' => trim($_POST['code_postal']),
                    ':ville' => trim($_POST['ville']),
                    ':lat' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : 48.5839,
                    ':lng' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : 7.7455,
                    ':tel' => trim($_POST['telephone'] ?? ''),
                    ':email' => trim($_POST['email'] ?? ''),
                    ':site' => trim($_POST['site_web'] ?? ''),
                    ':activites' => trim($_POST['activites'] ?? ''),
                    ':publics' => trim($_POST['publics'] ?? ''),
                    ':pathologies' => trim($_POST['pathologies'] ?? ''),
                    ':tarifs' => trim($_POST['modalites_tarifs'] ?? '')
                ]);
                $message = "La structure a été enregistrée et transmise à la modération avant affichage sur la carte.";

            // 4. OFFRE D'EMPLOI, STAGE OU FORMATION
            } elseif ($type_soumission === 'opportunite') {
                $stmt = $pdo->prepare("INSERT INTO opportunites 
                    (categorie, titre, organisme, lieu, type_contrat, duree, niveau_etude, public_concerne, gratification_cout, description, contact_lien, date_expiration, statut)
                    VALUES 
                    (:cat, :titre, :organisme, :lieu, :contrat, :duree, :niveau, :public, :grat, :desc, :contact, :exp, 'en_attente')");

                $stmt->execute([
                    ':cat' => $_POST['categorie'],
                    ':titre' => trim($_POST['titre']),
                    ':organisme' => trim($_POST['organisme']),
                    ':lieu' => trim($_POST['lieu']),
                    ':contrat' => trim($_POST['type_contrat'] ?? ''),
                    ':duree' => trim($_POST['duree'] ?? ''),
                    ':niveau' => trim($_POST['niveau_etude'] ?? ''),
                    ':public' => trim($_POST['public_concerne'] ?? ''),
                    ':grat' => trim($_POST['gratification_cout'] ?? ''),
                    ':desc' => trim($_POST['description']),
                    ':contact' => trim($_POST['contact_lien']),
                    ':exp' => $_POST['date_expiration']
                ]);
                $message = "Votre offre / formation a bien été transmise. Elle sera mise en ligne après validation.";

            // 5. ÉVÉNEMENT OU ACTUALITÉ
            } elseif ($type_soumission === 'evenement') {
                $stmt = $pdo->prepare("INSERT INTO actualites_evenements 
                    (type_contenu, categorie, titre, organisateur, description, date_debut, date_fin, lieu, public_tarif, lien_inscription, statut)
                    VALUES 
                    (:type_c, :cat, :titre, :org, :desc, :d1, :d2, :lieu, :pt, :lien, 'en_attente')");

                $stmt->execute([
                    ':type_c' => $_POST['type_contenu'],
                    ':cat' => trim($_POST['categorie'] ?? ''),
                    ':titre' => trim($_POST['titre']),
                    ':org' => trim($_POST['organisateur'] ?? ''),
                    ':desc' => trim($_POST['description']),
                    ':d1' => !empty($_POST['date_debut']) ? $_POST['date_debut'] : null,
                    ':d2' => !empty($_POST['date_fin']) ? $_POST['date_fin'] : null,
                    ':lieu' => trim($_POST['lieu'] ?? ''),
                    ':pt' => trim($_POST['public_tarif'] ?? ''),
                    ':lien' => trim($_POST['lien_inscription'] ?? '')
                ]);
                $message = "Votre événement / actualité a bien été enregistré et sera publié après validation.";

            // 6. TÉMOIGNAGE / PARTAGE D'EXPÉRIENCE
            } elseif ($type_soumission === 'temoignage') {
                $stmt = $pdo->prepare("INSERT INTO temoignages 
                    (titre, auteur_nom, auteur_profil, type_partage, contenu, statut)
                    VALUES 
                    (:titre, :auteur, :profil, :type_p, :contenu, 'en_attente')");

                $stmt->execute([
                    ':titre' => trim($_POST['titre']),
                    ':auteur' => trim($_POST['auteur_nom']),
                    ':profil' => trim($_POST['auteur_profil'] ?? ''),
                    ':type_p' => trim($_POST['type_partage'] ?? 'Témoignage professionnel'),
                    ':contenu' => trim($_POST['contenu'])
                ]);
                $message = "Merci pour votre partage d'expérience. Il sera visible dans la rubrique Communauté après modération.";
            }
        } catch (Exception $e) {
            $erreur = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<section class="hero">
    <h1>Contribuer à la plateforme CRAPAA</h1>
    <p>Enrichissez la base de connaissances régionale : déposez un document, référencez un acteur APA, publiez une offre ou partagez une expérience.</p>
</section>

<!-- Sélecteur de type de contribution -->
<div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 2rem;">
    <a href="contribuer.php?type=ressource" class="btn-login" style="<?= $type_form === 'ressource' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Ressource scientifique / Mémoire</a>
    <a href="contribuer.php?type=outil" class="btn-login" style="<?= $type_form === 'outil' ? 'background: var(--secondary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Outil pratique / Fiche séance</a>
    <a href="contribuer.php?type=professionnel" class="btn-login" style="<?= $type_form === 'professionnel' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Référencer un EAPA</a>
    <a href="contribuer.php?type=structure" class="btn-login" style="<?= $type_form === 'structure' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Référencer une structure</a>
    <a href="contribuer.php?type=opportunite" class="btn-login" style="<?= $type_form === 'opportunite' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Offre Emploi / Stage / Formation</a>
    <a href="contribuer.php?type=evenement" class="btn-login" style="<?= $type_form === 'evenement' ? 'background: var(--primary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Événement / Actualité</a>
    <a href="contribuer.php?type=temoignage" class="btn-login" style="<?= $type_form === 'temoignage' ? 'background: var(--secondary); color: #fff;' : 'background: #fff; border: 1px solid var(--border); color: var(--text);' ?>">Témoignage / Projet</a>
</div>

<?php if ($message): ?>
    <div class="alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="form-box" style="border-top: 5px solid var(--primary);">

    <?php if ($type_form === 'ressource' || $type_form === 'outil'): ?>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">
            <?= $type_form === 'outil' ? 'Proposer un outil pratique ou pédagogique' : 'Proposer une ressource scientifique ou universitaire' ?>
        </h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="type_soumission" value="<?= htmlspecialchars($type_form) ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Type de document *</label>
                    <select name="type_document" required>
                        <?php if ($type_form === 'ressource'): ?>
                            <option value="Article scientifique">Article scientifique</option>
                            <option value="Mémoire">Mémoire (Licence / Master)</option>
                            <option value="Thèse">Thèse</option>
                            <option value="Rapport de stage">Rapport de stage</option>
                            <option value="Recommandations">Recommandations institutionnelles</option>
                        <?php else: ?>
                            <option value="Fiche exercice">Fiche d'exercices / Séance APA</option>
                            <option value="Outil d'évaluation">Test / Outil d'évaluation</option>
                            <option value="Programme APA">Programme d'activité physique</option>
                            <option value="Support de sensibilisation">Support de sensibilisation</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Thématique *</label>
                    <select name="thematique" required>
                        <option value="Physiologie et activité physique">Physiologie et activité physique</option>
                        <option value="Biomécanique">Biomécanique</option>
                        <option value="Psychologie">Psychologie</option>
                        <option value="Sociologie">Sociologie</option>
                        <option value="Pédagogie">Pédagogie</option>
                        <option value="Santé publique">Santé publique</option>
                        <option value="Handicap et inclusion">Handicap et inclusion</option>
                        <option value="Pathologies chroniques">Pathologies chroniques</option>
                        <option value="Vieillissement">Vieillissement</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Titre complet *</label>
                <input type="text" name="titre" required placeholder="Titre de l'article, du mémoire ou de l'outil">
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Auteur(s) *</label>
                    <input type="text" name="auteurs" required placeholder="Nom Prénom">
                </div>
                <div class="form-group">
                    <label>Année de publication</label>
                    <input type="number" name="annee_publication" min="1990" max="<?= date('Y') ?>" value="<?= date('Y') ?>">
                </div>
                <div class="form-group">
                    <label>Établissement / Organisme</label>
                    <input type="text" name="organisme" placeholder="Ex : Université de Strasbourg">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Population concernée</label>
                    <input type="text" name="population" placeholder="Ex : Seniors, Enfants, Adultes...">
                </div>
                <div class="form-group">
                    <label>Pathologie ou problématique</label>
                    <input type="text" name="pathologie" placeholder="Ex : Maladie de Parkinson, Diabète...">
                </div>
                <div class="form-group">
                    <label>Mots-clés (séparés par des virgules)</label>
                    <input type="text" name="mots_cles" placeholder="équilibre, chute, renforcement">
                </div>
            </div>

            <div class="form-group">
                <label>Résumé / Description générale *</label>
                <textarea name="resume" rows="4" required></textarea>
            </div>

            <?php if ($type_form === 'outil'): ?>
                <div class="form-group">
                    <label>Niveau de validation de l'outil *</label>
                    <select name="niveau_validation">
                        <option value="valide_scientifiquement">Outil validé scientifiquement</option>
                        <option value="adapte">Outil adapté</option>
                        <option value="pratique_terrain" selected>Exemple de pratique professionnelle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Objectif et conditions d'utilisation</label>
                    <textarea name="objectif" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Matériel nécessaire</label>
                    <input type="text" name="materiel" placeholder="Ex : Chronomètre, chaise, plots...">
                </div>
                <div class="form-group">
                    <label>Protocole / Déroulement et modalités d'interprétation</label>
                    <textarea name="protocole" rows="4"></textarea>
                </div>
            <?php endif; ?>

            <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <h3 style="font-size: 1.05rem; margin-bottom: 0.6rem; color: var(--primary);">Modalités de diffusion & Droits d'auteur</h3>
                <div class="form-group">
                    <label>Mode de mise à disposition *</label>
                    <select name="mode_diffusion">
                        <option value="pdf_heberge">Héberger le fichier PDF sur le CRAPAA (droits libres ou autorisés)</option>
                        <option value="lien_externe">Lien vers la publication originale</option>
                        <option value="reference_seule">Notice bibliographique uniquement (sans fichier)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fichier PDF (si hébergement direct, max 10 Mo)</label>
                    <input type="file" name="fichier_pdf" accept=".pdf">
                </div>
                <div class="form-group">
                    <label>Lien URL vers la publication originale</label>
                    <input type="url" name="lien_externe" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>Source bibliographique complète</label>
                    <input type="text" name="source_biblio" placeholder="Revue, volume, pages, DOI...">
                </div>
            </div>

    <?php elseif ($type_form === 'professionnel'): ?>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Référencer un Professionnel (EAPA)</h2>
        <form method="POST">
            <input type="hidden" name="type_soumission" value="professionnel">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" required>
                </div>
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" required>
                </div>
                <div class="form-group">
                    <label>Diplôme et qualification *</label>
                    <input type="text" name="diplome" required placeholder="Ex : Master 2 STAPS APAS">
                </div>
            </div>

            <div class="form-group">
                <label>Spécialités et domaines d'intervention</label>
                <input type="text" name="specialites" placeholder="Ex : Neurologie, Oncologie, Réadaptation cardiaque...">
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Publics accompagnés</label>
                    <input type="text" name="publics" placeholder="Ex : Enfants, Adultes, Seniors">
                </div>
                <div class="form-group">
                    <label>Pathologies prises en charge</label>
                    <input type="text" name="pathologies" placeholder="Ex : Diabète, Parkinson, Lombalgies">
                </div>
                <div class="form-group">
                    <label>Activités proposées</label>
                    <input type="text" name="activites" placeholder="Ex : Marche nordique, Gym douce, Équilibre">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Adresse professionnelle</label>
                    <input type="text" name="adresse">
                </div>
                <div class="form-group">
                    <label>Code postal *</label>
                    <input type="text" name="code_postal" required placeholder="67000">
                </div>
                <div class="form-group">
                    <label>Ville *</label>
                    <input type="text" name="ville" required placeholder="Strasbourg">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Latitude (optionnel, ex: 48.5839)</label>
                    <input type="text" name="latitude" placeholder="48.5839">
                </div>
                <div class="form-group">
                    <label>Longitude (optionnel, ex: 7.7455)</label>
                    <input type="text" name="longitude" placeholder="7.7455">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Téléphone professionnel</label>
                    <input type="text" name="telephone">
                </div>
                <div class="form-group">
                    <label>Email professionnel *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Site web ou réseau professionnel</label>
                    <input type="url" name="site_web" placeholder="https://...">
                </div>
            </div>

            <div class="form-group">
                <label>Modalités d'accompagnement, tarifs et disponibilités</label>
                <textarea name="modalites_tarifs" rows="3" placeholder="Séances individuelles ou collectives, à domicile, tarifs indicatifs..."></textarea>
            </div>

    <?php elseif ($type_form === 'structure'): ?>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Référencer une Structure APA</h2>
        <form method="POST">
            <input type="hidden" name="type_soumission" value="structure">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Nom de la structure *</label>
                    <input type="text" name="nom" required>
                </div>
                <div class="form-group">
                    <label>Type d'établissement *</label>
                    <select name="type_etablissement" required>
                        <option value="Maison Sport-Santé">Maison Sport-Santé</option>
                        <option value="Centre Hospitalier">Centre Hospitalier / Clinique</option>
                        <option value="Association">Association Sport-Santé</option>
                        <option value="EHPAD">EHPAD / Établissement médico-social</option>
                        <option value="Cabinet libéral">Cabinet libéral</option>
                        <option value="Autre">Autre structure</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Adresse complète *</label>
                    <input type="text" name="adresse" required>
                </div>
                <div class="form-group">
                    <label>Code postal *</label>
                    <input type="text" name="code_postal" required placeholder="68000">
                </div>
                <div class="form-group">
                    <label>Ville *</label>
                    <input type="text" name="ville" required placeholder="Colmar">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Latitude (optionnel, ex: 48.0793)</label>
                    <input type="text" name="latitude" placeholder="48.0793">
                </div>
                <div class="form-group">
                    <label>Longitude (optionnel, ex: 7.3585)</label>
                    <input type="text" name="longitude" placeholder="7.3585">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone">
                </div>
                <div class="form-group">
                    <label>Email de contact</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Site internet</label>
                    <input type="url" name="site_web" placeholder="https://...">
                </div>
            </div>

            <div class="form-group">
                <label>Activités proposées</label>
                <input type="text" name="activites" placeholder="Ex : Bilans physiques, gym adaptée, activités aquatiques...">
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Publics accueillis</label>
                    <input type="text" name="publics">
                </div>
                <div class="form-group">
                    <label>Pathologies ou problématiques concernées</label>
                    <input type="text" name="pathologies">
                </div>
            </div>

            <div class="form-group">
                <label>Modalités d'accompagnement, tarifs et dispositifs de financement</label>
                <textarea name="modalites_tarifs" rows="3"></textarea>
            </div>

    <?php elseif ($type_form === 'opportunite'): ?>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Publier une Offre d'emploi, de stage ou une formation</h2>
        <form method="POST">
            <input type="hidden" name="type_soumission" value="opportunite">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie" required>
                        <option value="emploi">Offre d'emploi</option>
                        <option value="stage">Offre de stage</option>
                        <option value="formation">Formation continue / universitaire</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Intitulé du poste / stage / formation *</label>
                    <input type="text" name="titre" required placeholder="Ex : Enseignant en APA (H/F)">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Structure / Organisme *</label>
                    <input type="text" name="organisme" required>
                </div>
                <div class="form-group">
                    <label>Localisation (ou Distanciel) *</label>
                    <input type="text" name="lieu" required placeholder="Ex : Strasbourg (67)">
                </div>
                <div class="form-group">
                    <label>Date limite / expiration *</label>
                    <input type="date" name="date_expiration" required min="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Type de contrat / Modalité</label>
                    <input type="text" name="type_contrat" placeholder="Ex : CDI, CDD 6 mois, Stage M2, Présentiel">
                </div>
                <div class="form-group">
                    <label>Durée et temps de travail</label>
                    <input type="text" name="duree" placeholder="Ex : 35h/semaine, 6 mois">
                </div>
                <div class="form-group">
                    <label>Niveau d'études / Diplôme requis</label>
                    <input type="text" name="niveau_etude" placeholder="Ex : Licence ou Master APAS">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Public concerné</label>
                    <input type="text" name="public_concerne" placeholder="Ex : Patients en oncologie, Seniors">
                </div>
                <div class="form-group">
                    <label>Gratification / Rémunération / Coût formation</label>
                    <input type="text" name="gratification_cout" placeholder="Ex : Gratification légale, Convention collective...">
                </div>
            </div>

            <div class="form-group">
                <label>Description détaillée des missions ou du programme *</label>
                <textarea name="description" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label>Email de contact ou lien de candidature *</label>
                <input type="text" name="contact_lien" required placeholder="recrutement@structure.fr ou https://...">
            </div>

    <?php elseif ($type_form === 'evenement'): ?>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Proposer un Événement ou une Actualité</h2>
        <form method="POST">
            <input type="hidden" name="type_soumission" value="evenement">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Type de publication *</label>
                    <select name="type_contenu" required>
                        <option value="evenement">Événement (avec date dans l'agenda)</option>
                        <option value="actualite">Actualité institutionnelle / associative</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie" required>
                        <option value="Conférence">Conférence</option>
                        <option value="Colloque">Colloque</option>
                        <option value="Journée professionnelle">Journée professionnelle</option>
                        <option value="Formation">Formation</option>
                        <option value="Événement universitaire">Événement universitaire</option>
                        <option value="Action de prévention">Action de prévention</option>
                        <option value="Manifestation sportive adaptée">Manifestation sportive adaptée</option>
                        <option value="Actualité institutionnelle">Actualité institutionnelle</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="titre" required>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Organisateur</label>
                    <input type="text" name="organisateur">
                </div>
                <div class="form-group">
                    <label>Date et heure de début (pour événement)</label>
                    <input type="datetime-local" name="date_debut">
                </div>
                <div class="form-group">
                    <label>Date et heure de fin (optionnel)</label>
                    <input type="datetime-local" name="date_fin">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Lieu / Adresse</label>
                    <input type="text" name="lieu" placeholder="Ex : Faculté des Sciences du Sport, Strasbourg">
                </div>
                <div class="form-group">
                    <label>Public concerné & Tarif</label>
                    <input type="text" name="public_tarif" placeholder="Ex : Étudiants et professionnels - Gratuit sur inscription">
                </div>
            </div>

            <div class="form-group">
                <label>Description complète *</label>
                <textarea name="description" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label>Lien d'inscription ou d'information</label>
                <input type="url" name="lien_inscription" placeholder="https://...">
            </div>

    <?php elseif ($type_form === 'temoignage'): ?>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Partager un Témoignage ou un Projet de terrain</h2>
        <form method="POST">
            <input type="hidden" name="type_soumission" value="temoignage">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Type de partage *</label>
                    <select name="type_partage" required>
                        <option value="Témoignage professionnel">Témoignage professionnel</option>
                        <option value="Retour d'expérience">Retour d'expérience</option>
                        <option value="Projet de terrain">Projet de terrain</option>
                        <option value="Interview">Interview</option>
                        <option value="Initiative étudiante">Initiative étudiante</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Titre de votre partage *</label>
                    <input type="text" name="titre" required placeholder="Ex : Mise en place d'un programme APA en EHPAD">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Votre nom / prénom *</label>
                    <input type="text" name="auteur_nom" required>
                </div>
                <div class="form-group">
                    <label>Votre fonction / profil</label>
                    <input type="text" name="auteur_profil" placeholder="Ex : EAPA en centre de rééducation, Étudiant M2 APAS">
                </div>
            </div>

            <div class="form-group">
                <label>Votre témoignage ou présentation du projet *</label>
                <textarea name="contenu" rows="6" required></textarea>
            </div>
    <?php endif; ?>

        <!-- Case de consentement obligatoire (Section 11 & 16 du cahier des charges) -->
        <div style="margin: 1.2rem 0; padding: 1rem; background: #fff3e0; border-radius: 6px; border: 1px solid #ffe0b2;">
            <label style="display: flex; gap: 0.6rem; align-items: flex-start; cursor: pointer; font-weight: normal;">
                <input type="checkbox" name="consentement_droits" required style="width: auto; margin-top: 4px;">
                <span style="font-size: 0.9rem;">
                    Je certifie l'exactitude des informations transmises, je confirme disposer des droits de diffusion nécessaires pour les documents joints et j'accepte que cette contribution soit soumise à l'équipe de modération du CRAPAA avant publication (conformément aux <a href="cgu.php" target="_blank">CGU et à la politique RGPD</a>).
                </span>
            </label>
        </div>

        <button type="submit" class="btn-login" style="background: var(--secondary); color: #fff; padding: 0.7rem 1.4rem; font-size: 1rem;">
            Soumettre ma contribution à la modération
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>