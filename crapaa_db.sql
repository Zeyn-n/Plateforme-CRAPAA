-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 16 sep. 2026 à 16:08
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `crapaa_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `annuaire_acteurs`
--

DROP TABLE IF EXISTS `annuaire_acteurs`;
CREATE TABLE IF NOT EXISTS `annuaire_acteurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_structure` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_structure` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `annuaire_acteurs`
--

INSERT INTO `annuaire_acteurs` (`id`, `nom_structure`, `type_structure`, `adresse`, `latitude`, `longitude`, `telephone`, `email`) VALUES
(1, 'Hôpitaux Universitaires de Strasbourg - Service APA', 'Centre Hospitalier', '1 Place de l\'Hôpital, 67000 Strasbourg', 48.57550000, 7.74320000, '03 88 11 67 68', 'apa@chru-strasbourg.fr'),
(2, 'Association APA Santé Alsace', 'Association', '12 Rue de la Somme, 67000 Strasbourg', 48.58390000, 7.77100000, '03 88 00 11 22', 'contact@apasante.fr');

-- --------------------------------------------------------

--
-- Structure de la table `opportunites`
--

DROP TABLE IF EXISTS `opportunites`;
CREATE TABLE IF NOT EXISTS `opportunites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('stage','emploi','formation') COLLATE utf8mb4_unicode_ci NOT NULL,
  `structure_nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lieu` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ressources`
--

DROP TABLE IF EXISTS `ressources`;
CREATE TABLE IF NOT EXISTS `ressources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_ressource` enum('fiche_technique','memoire','these','article','cours') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichier_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut_validation` enum('en_attente','valide','rejete') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `auteur_id` int NOT NULL,
  `date_publication` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ressources`
--

INSERT INTO `ressources` (`id`, `titre`, `description`, `type_ressource`, `fichier_path`, `statut_validation`, `auteur_id`, `date_publication`) VALUES
(1, 'feur', 'savoir si chié 6kg c\'est normal ou pas', 'memoire', 'uploads/6aaab49b02040_feur.pdf', 'en_attente', 1, '2026-09-16 17:24:11');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('etudiant','enseignant','professionnel','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'etudiant',
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `date_inscription`) VALUES
(1, 'Dupont', 'Jean', 'jean.dupont@unistra.fr', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1aU4S0T5WjGqH5s4rG2yZqZ5T5WjGqH', 'etudiant', '2026-09-16 16:58:39'),
(2, 'wahl', 'matéo', 'wahlmateo@gmail.com', '$2y$10$gQLfsiyz8g0MbalaiVQPK.9.YQ1wj9DddhIWxTRpjdUiTtjUJZLYq', 'etudiant', '2026-09-16 17:59:41');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ressources`
--
ALTER TABLE `ressources`
  ADD CONSTRAINT `ressources_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
