\# Plateforme CRAPAA – Ressources APA (Alsace)



<div align="center">



!\[PHP](https://img.shields.io/badge/PHP-777BB4?logo=php\&logoColor=white\&style=for-the-badge) 

!\[MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql\&logoColor=white\&style=for-the-badge) 

!\[Leaflet](https://img.shields.io/badge/Leaflet-199900?logo=leaflet\&logoColor=white\&style=for-the-badge) 

!\[Status](https://img.shields.io/badge/Status-En\_cours\_de\_développement-orange?style=for-the-badge)



</div>



\## À propos du projet



Développement du portail web \*\*CRAPAA\*\* (Centre de Ressources des Activités Physiques Adaptées d'Alsace). Ce projet est mené dans le cadre d'un \*\*partenariat technique et de développement\*\* avec les étudiants de la promotion \*\*Master 2 APA\*\* de la Faculté des Sciences du Sport (Université de Strasbourg).



L'objectif est de concevoir une plateforme régionale centralisant les ressources documentaires, un annuaire cartographié des structures et professionnels de santé/sport, un module d'offres d'emploi/stages, ainsi qu'un espace de contribution sécurisé avec un back-office d'administration complet.



> \*\*Statut actuel :\*\* Plateforme activement en cours de développement, implémentation progressive des modules du cahier des charges.



\## Fonctionnalités principales



\- \*\*Architecture \& Navigation :\*\* Portail responsive structuré autour de 10 rubriques principales et d'un moteur de recherche global.

\- \*\*Bibliothèque documentaire \& Outils :\*\* Consultation et filtrage multicritères de ressources scientifiques et pratiques.

\- \*\*Annuaire \& Carte interactive :\*\* Représentation géographique des structures et professionnels en Alsace via \*\*LeafletJS\*\* et regroupement de marqueurs (\*MarkerCluster\*).

\- \*\*Emploi, Stages \& Formation :\*\* Module d'opportunités professionnelles avec gestion des expirations.

\- \*\*Espace Communauté \& Orientation :\*\* Guides d'orientation régionaux et mise en relation.

\- \*\*Espace Contribuer :\*\* Formulaires de soumission externe sécurisés avec modération.

\- \*\*Back-Office d'administration :\*\* Tableau de bord statistique, file de modération, gestion des rôles utilisateurs et export des données au format \*\*CSV\*\*.

\- \*\*Conformité :\*\* Pages légales, CGU et conformité RGPD.



\## Stack Technique



\- \*\*Backend :\*\* PHP (scripts PDO sécurisés)

\- \*\*Base de données :\*\* MySQL

\- \*\*Frontend :\*\* HTML5, CSS3 (Design personnalisé sur mesure)

\- \*\*Cartographie :\*\* LeafletJS, Leaflet.markercluster

\- \*\*Environnement local :\*\* WampServer



\## Installation et Lancement Local



1\. Clonez ce dépôt dans votre répertoire serveur local (ex: <code>wamp64/www/</code>) :

<pre>git clone https://github.com/Zeyn-n/Plateforme-CRAPAA.git</pre>

2\. Importez le script <code>crapaa\_db.sql</code> dans votre gestionnaire MySQL (phpMyAdmin).

3\. Configurez vos identifiants de connexion dans <code>config/db.php</code>.

4\. Lancez le projet via votre navigateur (<code>http://localhost/CRAPAA/</code>).



\---

\*Partenariat technique – IUT Robert Schuman x Master 2 APA (Université de Strasbourg)\*

