<?php
require_once 'config/db.php';
include 'includes/header.php';
?>

<section class="hero">
    <h1>Mentions Légales & Conditions Générales d'Utilisation (CGU)</h1>
    <p>Informations juridiques, propriété intellectuelle et politique de protection des données (RGPD) du CRAPAA.</p>
</section>

<div style="display: grid; gap: 2rem;">
    <!-- 1. Mentions légales -->
    <article class="form-box">
        <h2 style="color: var(--primary); margin-bottom: 1rem;">1. Mentions légales</h2>
        <p style="margin-bottom: 0.8rem; line-height: 1.6;">
            <strong>Nom du projet :</strong> CRAPAA (Centre de Ressources des Activités Physiques Adaptées d'Alsace)<br>
            <strong>Cadre institutionnel :</strong> Projet universitaire de Master 2 - Faculté des Sciences du Sport (STAPS), Université de Strasbourg, Alsace.<br>
            <strong>Directeur de la publication :</strong> Le Responsable du Projet Master 2 APAS.<br>
            <strong>Hébergement :</strong> jsp encore
        </p>
    </article>

    <!-- 2. Objet et Champ d'application -->
    <article class="form-box">
        <h2 style="color: var(--primary); margin-bottom: 1rem;">2. Objet de la plateforme</h2>
        <p style="line-height: 1.6;">
            Le CRAPAA a pour vocation de centraliser les ressources scientifiques, les outils pratiques, l'annuaire des professionnels (EAPA) et des structures d'Activité Physique Adaptée en Alsace, ainsi que les offres d'emploi, stages et actualités du secteur. L'accès à la plateforme est ouvert aux étudiants, chercheurs, professionnels de santé et au grand public.
        </p>
    </article>

    <!-- 3. Propriété intellectuelle -->
    <article class="form-box">
        <h2 style="color: var(--primary); margin-bottom: 1rem;">3. Propriété intellectuelle et droits de diffusion</h2>
        <p style="line-height: 1.6;">
            Les documents, mémoires, articles et outils mis en ligne sur le CRAPAA sont soumis aux droits de leurs auteurs respectifs. En proposant une contribution via l'espace dédié, le contributeur garantit disposer des droits de diffusion nécessaires et accorde au CRAPAA l'autorisation de diffuser le contenu dans un cadre pédagogique et non commercial.
        </p>
    </article>

    <!-- 4. Données personnelles et RGPD -->
    <article id="rgpd" class="form-box" style="border-top: 5px solid var(--secondary);">
        <h2 style="color: var(--primary); margin-bottom: 1rem;">4. Politique de protection des données personnelles (RGPD)</h2>
        <p style="margin-bottom: 0.8rem; line-height: 1.6;">
            Conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi Informatique et Libertés :
        </p>
        <ul style="margin-left: 1.5rem; line-height: 1.8; margin-bottom: 1rem;">
            <li><strong>Données collectées :</strong> Les données enregistrées se limitent aux informations professionnelles des annuaires (nom, prénom, diplôme, structure, contact professionnel) et aux comptes utilisateurs (nom, prénom, email). Aucune donnée médicale personnelle n'est collectée ni stockée sur la plateforme.</li>
            <li><strong>Finalité :</strong> Les données de l'annuaire sont exclusivement destinées à faciliter l'orientation des usagers et la mise en relation professionnelle en Alsace.</li>
            <li><strong>Droits des utilisateurs :</strong> Tout utilisateur dispose d'un droit d'accès, de rectification et de suppression de ses données en contactant l'équipe via le formulaire de contact ou par l'intermédiaire du back-office.</li>
        </ul>
        <p style="font-size: 0.92rem; color: var(--muted);">
            Les contributions font l'objet d'une validation par un modérateur avant toute publication en ligne.
        </p>
    </article>
</div>

<?php include 'includes/footer.php'; ?>