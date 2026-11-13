<?php
require_once 'config/db.php';
include 'includes/header.php';
?>

<section class="hero">
    <h1>M'orienter vers une Activité Physique Adaptée en Alsace</h1>
    <p>Les étapes clés, les dispositifs régionaux et l'accès aux structures et professionnels de proximité (Bas-Rhin et Haut-Rhin).</p>
</section>

<!-- Étapes du parcours d'orientation -->
<h2 style="color: var(--primary); margin-bottom: 1rem;">Comment démarrer une pratique adaptée ?</h2>
<div class="modules-grid" style="margin-bottom: 2.5rem;">
    <div class="card">
        <span class="badge badge-blue">Étape 1</span>
        <h3>Consulter son médecin</h3>
        <p>Échangez avec votre médecin traitant ou spécialiste afin d'évaluer votre état de santé, vos besoins et d'obtenir, si nécessaire, une prescription médicale d'Activité Physique Adaptée.</p>
    </div>
    <div class="card">
        <span class="badge badge-green">Étape 2</span>
        <h3>Contacter un dispositif ou une Maison Sport-Santé</h3>
        <p>En Alsace, les Maisons Sport-Santé et le dispositif régional Prescri'Mouv accompagnent les personnes vers un bilan de condition physique et une orientation personnalisée.</p>
    </div>
    <div class="card">
        <span class="badge badge-purple">Étape 3</span>
        <h3>Intégrer une structure ou un suivi EAPA</h3>
        <p>Rejoignez un programme encadré par un Enseignant en Activité Physique Adaptée (EAPA) en association, en établissement ou en cabinet libéral près de chez vous.</p>
    </div>
</div>

<!-- Assistant d'orientation rapide vers l'annuaire (sans collecte de données médicales) -->
<div class="form-box" style="border-top: 5px solid var(--secondary); margin-bottom: 2.5rem;">
    <h2 style="color: var(--primary); margin-bottom: 0.5rem;">Rechercher une solution près de chez vous</h2>
    <p style="color: var(--muted); margin-bottom: 1.2rem; font-size: 0.92rem;">
        Sélectionnez votre secteur géographique et votre recherche pour accéder directement aux structures et professionnels référencés (aucune donnée médicale personnelle n'est enregistrée).
    </p>

    <form action="annuaire.php" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: end;">
        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Ville ou Code postal (67 / 68)</label>
            <input type="text" name="ville_cp" placeholder="Ex : Strasbourg, Colmar, Mulhouse..." style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Type d'interlocuteur</label>
            <select name="entite" style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
                <option value="tous">Structures et Professionnels</option>
                <option value="structures">Maisons Sport-Santé / Structures</option>
                <option value="professionnels">Enseignants en APA (EAPA)</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; display: block; margin-bottom: 0.3rem;">Besoin ou activité recherchée</label>
            <input type="text" name="mot_cle" placeholder="Ex : Équilibre, cardiologie, seniors..." style="width: 100%; padding: 0.55rem; border: 1px solid var(--border); border-radius: 4px;">
        </div>

        <div>
            <button type="submit" class="btn-login" style="width: 100%; background: var(--secondary); color: #fff; padding: 0.6rem;">Trouver sur la carte</button>
        </div>
    </form>
</div>

<!-- Dispositifs institutionnels en Grand Est / Alsace -->
<div class="form-box">
    <h2 style="color: var(--primary); margin-bottom: 1rem;">Dispositifs ressources en Alsace</h2>
    <ul style="margin-left: 1.5rem; line-height: 1.8;">
        <li><strong>Prescri'Mouv (ARS Grand Est) :</strong> Parcours d'accompagnement sur prescription médicale destiné aux personnes atteintes d'une Affection de Longue Durée (ALD), d'hypertension ou d'obésité.</li>
        <li><strong>Maisons Sport-Santé d'Alsace :</strong> Accueil, information, orientation et réalisation de bilans de condition physique sur tout le territoire alsacien (Strasbourg, Mulhouse, Colmar, Haguenau, Sélestat, Saverne...).</li>
        <li><strong>Réseaux associatifs et hospitaliers :</strong> Programmes dédiés en oncologie, neurologie, cardiologie, diabétologie et prévention de la perte d'autonomie.</li>
    </ul>
</div>

<?php include 'includes/footer.php'; ?>