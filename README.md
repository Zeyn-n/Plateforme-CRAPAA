<div align="center">

# Plateforme CRAPAA
### Centre de Ressources des Activités Physiques Adaptées d'Alsace

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet-199900?style=for-the-badge&logo=leaflet&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

![Statut](https://img.shields.io/badge/Statut-En%20cours%20de%20d%C3%A9veloppement-orange?style=flat-square)
![Collaboration](https://img.shields.io/badge/Collaboration-Mat%C3%A9o%20Wahl%20x%20Master%202%20APAS-blue?style=flat-square)

</div>

---

## À propos du projet

Développement du portail web **CRAPAA**, mené par **Matéo Wahl** en collaboration avec les étudiants de la promotion **Master 2 APAS**.

L'objectif est de concevoir une plateforme régionale centralisant :
- Les ressources documentaires scientifiques et pratiques ;
- Un annuaire cartographié des structures et professionnels de santé/sport en Alsace ;
- Un module d'offres d'emploi, de stages et de formations ;
- Un espace de contribution sécurisé couplé à un back-office d'administration complet.

> [!NOTE]
> **Statut actuel :** Plateforme activement en cours de développement — implémentation progressive des modules du cahier des charges.

---

## Fonctionnalités principales

- **Architecture & Navigation :** Portail responsive structuré autour de 10 rubriques principales et d'un moteur de recherche global.
- **Bibliothèque documentaire & Outils :** Consultation et filtrage multicritères de ressources scientifiques et pratiques.
- **Annuaire & Carte interactive :** Géolocalisation des structures et professionnels en Alsace via **LeafletJS** avec regroupement dynamique de marqueurs (*MarkerCluster*).
- **Emploi, Stages & Formation :** Diffusion d'opportunités professionnelles avec gestion automatique des dates d'expiration.
- **Espace Communauté & Orientation :** Guides d'orientation régionaux et outils de mise en relation.
- **Espace Contribuer :** Formulaires de soumission externe sécurisés avec validation avant publication.
- **Back-Office d'administration :** Tableau de bord statistique, file de modération, gestion des rôles utilisateurs et export des données au format **CSV**.
- **Conformité :** Pages légales, CGU et respect des normes **RGPD**.

---

## Stack Technique

| Couche | Technologies |
| :--- | :--- |
| **Backend** | PHP (scripts PDO sécurisés) |
| **Base de données** | MySQL |
| **Frontend** | HTML5, CSS3 (Design personnalisé sur mesure) |
| **Cartographie** | LeafletJS, `Leaflet.markercluster` |
| **Environnement local** | WampServer |

---

## Installation et Lancement Local

### 1. Cloner le dépôt
Placez-vous dans le répertoire de votre serveur local (ex : `wamp64/www/`) et exécutez :

```bash
git clone https://github.com/Zeyn-n/Plateforme-CRAPAA.git
```

### 2. Configurer la base de données
- Ouvrez votre gestionnaire MySQL (**phpMyAdmin**).
- Créez une nouvelle base de données et importez le script `crapaa_db.sql`.

### 3. Paramétrer la connexion
Renseignez vos identifiants de connexion MySQL dans le fichier :

```text
config/db.php
```

### 4. Lancer l'application
Ouvrez votre navigateur et accédez à l'URL locale :

```text
http://localhost/CRAPAA/
```

---

<div align="center">
  <sub>Projet développé par <b>Matéo Wahl</b> en collaboration avec la promotion <b>Master 2 APAS</b></sub>
</div>
