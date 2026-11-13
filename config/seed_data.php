<?php
require_once 'db.php';

// Script d'insertion de données de démonstration pour le projet CRAPAA (Alsace)
try {
    // 1. Pages de contenu (Découvrir l'APA)
    $pdo->exec("INSERT IGNORE INTO pages_contenu (id, slug, titre, contenu, ordre) VALUES 
        (1, 'definition-apa', 'Qu est-ce que l Activite Physique Adaptee ?', 'L Activite Physique Adaptee (APA) englobe tous les mouvements de la vie quotidienne, de loisir, de sport ou d exercice, realises par des personnes ayant des besoins particuliers, en raison d un age avance, d un handicap ou d une maladie chronique.', 1),
        (2, 'role-eapa', 'Le role de l Enseignant en APA (EAPA)', 'L EAPA est un professionnel de l intervention en activite physique pour des publics a besoins specifiques. Titulaire d une Licence ou d un Master STAPS APA-S, il concoit, conduit et evalue des programmes securises et personnalises.', 2),
        (3, 'prescription-dispositifs', 'Prescription medicale et dispositifs en Alsace', 'En France, la loi de modernisation de notre systeme de sante permet au medecin traitant de prescrire une activite physique adaptee aux patients atteints d une Affection de Longue Durée (ALD). En Alsace, le dispositif regional PrescriMouv soutient cette dynamique.', 3)
    ");

    // 2. Structures APA (Bas-Rhin et Haut-Rhin)
    $pdo->exec("INSERT IGNORE INTO structures (id, nom, type_etablissement, adresse, code_postal, ville, latitude, longitude, telephone, email, site_web, activites, publics, pathologies, statut) VALUES 
        (1, 'Maison Sport-Sante Strasbourg', 'Maison Sport-Santé', '10 rue Blaise Pascal', '67000', 'Strasbourg', 48.5839, 7.7455, '0388000001', 'contact@mss-strasbourg.fr', 'https://www.strasbourg.fr', 'Bilans de condition physique, orientation, ateliers collectifs', 'Seniors, adultes en ALD', 'Cardiologie, obésité, sédentarité', 'publie'),
        (2, 'Centre de Readaptation de Mulhouse', 'Centre Hospitalier', '24 avenue d Altkirch', '68100', 'Mulhouse', 47.7394, 7.3384, '0389000002', 'contact@ch-mulhouse.fr', 'https://www.ghrm.fr', 'Rehabilitation cardiaque et neurologique, APA sur prescription', 'Patients hospitalises et en ambulatoire', 'AVC, infarctus, insuffisance respiratoire', 'publie'),
        (3, 'Association Sante Sport Colmar', 'Association', '5 boulevard du General Leclerc', '68000', 'Colmar', 48.0793, 7.3585, '0389000003', 'contact@santesportcolmar.fr', 'https://www.colmar.fr', 'Gym douce, marche nordique adaptee', 'Seniors, personnes âgees', 'Troubles de l equilibre, arthrose', 'publie')
    ");

    // 3. Professionnels EAPA
    $pdo->exec("INSERT IGNORE INTO professionnels (id, structure_id, nom, prenom, diplome, specialites, publics, pathologies, activites, adresse, code_postal, ville, latitude, longitude, telephone, email, statut) VALUES 
        (1, 1, 'Muller', 'Claire', 'Master 2 STAPS APAS', 'Oncologie, Activite physique et cancer', 'Adultes, femmes en parcours de soins', 'Cancers du sein, fatigue chronique', 'Renforcement musculaire adapte, yoga sante', '10 rue Blaise Pascal', '67000', 'Strasbourg', 48.5842, 7.7460, '0601020304', 'claire.muller@crapaa.fr', 'publie'),
        (2, 2, 'Schmidt', 'Thomas', 'Licence STAPS APA-S', 'Neurologie et vieillissement', 'Seniors et patients neuro', 'Maladie de Parkinson, sclerose en plaques', 'Gym d equilibre, prevention des chutes', '24 avenue d Altkirch', '68100', 'Mulhouse', 47.7400, 7.3390, '0605060708', 'thomas.schmidt@crapaa.fr', 'publie')
    ");

    // 4. Ressources scientifiques & outils
    $pdo->exec("INSERT IGNORE INTO ressources (id, categorie_principale, type_document, titre, auteurs, annee_publication, organisme, thematique, population, pathologie, mots_cles, resume, mode_diffusion, statut) VALUES 
        (1, 'scientifique', 'Article scientifique', 'Effets de l activite physique adaptee sur la qualite de vie de patients atteints de Parkinson', 'Weber, M. et al.', 2024, 'Universite de Strasbourg', 'Pathologies chroniques', 'Adultes et seniors', 'Maladie de Parkinson', 'parkinson, equilibre, marche', 'Etude realisee aupres de 120 patients mettant en evidence l impact positif d un programme de 12 semaines d APA sur les capacites motrices.', 'reference_seule', 'publie'),
        (2, 'pratique', 'Fiche exercice', 'Protocole d evaluation du Test Timed Up and Go (TUG)', 'Faculte des Sciences du Sport Strasbourg', 2023, 'CRAPAA', 'Pédagogie', 'Seniors', 'Risque de chute', 'chute, evaluation, equilibre', 'Fiche technique decrivant le protocole d execution, le materiel requis et les normes d interpretation du test TUG.', 'reference_seule', 'publie')
    ");

    // 5. Opportunités (Emploi / Stage / Formation)
    $pdo->exec("INSERT IGNORE INTO opportunites (id, categorie, titre, organisme, lieu, type_contrat, description, contact_lien, date_expiration, statut) VALUES 
        (1, 'emploi', 'Enseignant en APA (H/F) - Centre de Sante', 'Association Sport-Sante Alsace', 'Strasbourg (67)', 'CDI - 35h', 'Encadrement de seances collectives et individuelles d APA pour des patients atteints de maladies metaboliques.', 'recrutement@santesante67.fr', '2026-12-31', 'publie'),
        (2, 'stage', 'Stage Master 2 APAS - Coordination de projets', 'Maison Sport-Sante Mulhouse', 'Mulhouse (68)', 'Stage 6 mois', 'Participation au developpement des parcours de prescription d APA et evaluation de la condition physique des usagers.', 'stage@mss-mulhouse.fr', '2026-09-30', 'publie')
    ");

    // 6. Actualités & Événements
    $pdo->exec("INSERT IGNORE INTO actualites_evenements (id, type_contenu, categorie, titre, organisateur, description, date_debut, lieu, statut) VALUES 
        (1, 'evenement', 'Colloque', 'Colloque Regional APA Grand Est', 'Faculte STAPS Strasbourg', 'Journee d echange sur la prescription d activite physique et les retours d experience des Maisons Sport-Sante.', '2026-06-15 09:00:00', 'Strasbourg - Campus Esplanade', 'publie'),
        (2, 'actualite', 'Actualité institutionnelle', 'Lancement du nouveau portail CRAPAA', 'Equipe Projet Master 2', 'Mise en ligne officielle de la plateforme regionale de ressources en Activite Physique Adaptee.', '2026-03-01 00:00:00', 'Strasbourg', 'publie')
    ");

    echo "Jeu de données de test inséré avec succès dans la base de données.";
} catch (PDOException $e) {
    echo "Erreur lors de l'insertion des données : " . $e->getMessage();
}