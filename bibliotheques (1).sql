-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 31 mai 2025 à 02:03
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bibliotheques`
--

-- --------------------------------------------------------

--
-- Structure de la table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','etudiant') DEFAULT 'etudiant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `agents`
--

INSERT INTO `agents` (`id`, `nom`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN', 'admin@bibliotheque.com', '1234', 'admin', '2025-05-14 01:29:53', '2025-05-28 21:11:54'),
(2, 'radouane', 'radouane@gmail.com', '123', 'etudiant', '2025-05-14 02:23:45', '2025-05-28 21:12:02'),
(3, 'Pierre Durand', 'pierre.durand@email.com', '123', 'etudiant', '2025-05-30 23:43:29', '2025-05-30 23:43:29');

-- --------------------------------------------------------

--
-- Structure de la table `emprunts`
--

CREATE TABLE `emprunts` (
  `id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `date_emprunt` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_retour_prevue` datetime NOT NULL,
  `date_retour` datetime DEFAULT NULL,
  `penalite` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `emprunts`
--

INSERT INTO `emprunts` (`id`, `livre_id`, `etudiant_id`, `agent_id`, `date_emprunt`, `date_retour_prevue`, `date_retour`, `penalite`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-05-14 01:29:54', '2025-05-29 02:29:54', '2025-05-30 21:54:35', 1.50, '2025-05-14 01:29:54', '2025-05-30 20:54:35'),
(2, 2, 2, 1, '2025-05-14 01:29:54', '2025-05-24 02:29:54', '2025-05-19 23:28:37', 0.00, '2025-05-14 01:29:54', '2025-05-19 22:28:37'),
(3, 3, 3, 1, '2025-05-14 01:29:54', '2025-05-09 02:29:54', '2025-05-14 07:18:42', 0.00, '2025-05-14 01:29:54', '2025-05-15 13:33:25'),
(4, 3, 2, 1, '2025-05-06 00:25:47', '2025-05-14 01:25:47', '2025-05-18 23:43:51', 0.00, '2025-05-16 00:27:03', '2025-05-19 19:20:48'),
(5, 3, 1, 2, '2025-05-19 21:24:59', '2025-05-30 23:24:59', '2025-05-29 20:25:13', 0.00, '2025-05-19 21:24:59', '2025-05-30 22:43:18'),
(6, 6, 2, 2, '2025-05-19 21:25:15', '2025-05-28 20:25:13', NULL, 0.00, '2025-05-19 21:25:15', '2025-05-30 22:44:56'),
(7, 4, 3, 2, '2025-05-19 21:25:20', '2025-05-25 20:25:13', NULL, 0.00, '2025-05-19 21:25:20', '2025-05-30 22:44:01'),
(8, 5, 1, 2, '2025-05-19 21:25:24', '2025-05-01 01:25:47', '2025-05-30 23:41:18', 240.00, '2025-05-19 21:25:24', '2025-05-30 22:41:53'),
(9, 7, 1, 2, '2025-05-19 22:28:30', '2025-05-30 00:28:30', NULL, 0.00, '2025-05-19 22:28:30', '2025-05-30 22:42:17'),
(10, 8, 1, 2, '2025-05-28 14:03:05', '2025-06-12 16:03:05', NULL, 0.00, '2025-05-28 14:03:05', '2025-05-28 14:03:05'),
(11, 9, 2, 2, '2025-05-30 18:31:28', '2025-06-14 20:31:28', NULL, 0.00, '2025-05-30 18:31:28', '2025-05-30 18:31:28'),
(13, 10, 1, 1, '2025-05-30 22:25:07', '2025-06-15 00:25:07', NULL, 0.00, '2025-05-30 22:25:07', '2025-05-30 22:25:07'),
(14, 1, 1, 1, '2025-05-30 22:45:27', '2025-06-15 00:45:27', NULL, 0.00, '2025-05-30 22:45:27', '2025-05-30 22:45:27'),
(15, 3, 2, 1, '2025-05-30 23:18:47', '2025-06-15 01:18:47', NULL, 0.00, '2025-05-30 23:18:47', '2025-05-30 23:18:47'),
(16, 3, 1, 1, '2025-05-30 23:21:47', '2025-06-15 01:21:47', NULL, 0.00, '2025-05-30 23:21:47', '2025-05-30 23:21:47'),
(17, 2, 10, 1, '2025-05-30 23:33:26', '2025-06-15 01:33:26', '2025-05-31 00:33:38', 0.00, '2025-05-30 23:33:26', '2025-05-30 23:33:38'),
(18, 15, 2, 2, '2025-05-30 23:40:59', '2025-06-15 01:40:59', '2025-05-31 00:41:31', 0.00, '2025-05-30 23:40:59', '2025-05-30 23:41:31');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

CREATE TABLE `etudiants` (
  `id` int(11) NOT NULL,
  `matricule` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id`, `matricule`, `nom`, `email`, `telephone`, `created_at`, `updated_at`) VALUES
(1, 'ET20230001', 'Jean Dupont', 'jean.dupont@email.com', '0612345678', '2025-05-14 01:29:54', '2025-05-14 01:29:54'),
(2, 'ET20230002', 'Radouane bouradouan', 'marie.martin@email.com', '0623456789', '2025-05-14 01:29:54', '2025-05-30 23:39:59'),
(3, 'ET20230003', 'Pierre Durand', 'pierre.durand@email.com', '0634567890', '2025-05-14 01:29:54', '2025-05-14 01:29:54'),
(10, 'ET20230004', 'RADOUANE', 'radouane@gmail.com', '34567890°', '2025-05-30 22:20:35', '2025-05-30 22:20:35');

-- --------------------------------------------------------

--
-- Structure de la table `livres`
--

CREATE TABLE `livres` (
  `id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `auteur` varchar(100) NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `emplacement` varchar(11) NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `livres`
--

INSERT INTO `livres` (`id`, `isbn`, `titre`, `auteur`, `categorie`, `emplacement`, `disponible`, `created_at`, `updated_at`) VALUES
(1, '978-2070360428', 'L\'Étranger', 'Albert Camus', 'Littérature', 'cat1', 0, '2025-05-14 01:29:54', '2025-05-30 22:45:27'),
(2, '978-2253160692', '1984', 'George Orwell', 'Science-Fiction', 'cat2', 0, '2025-05-14 01:29:54', '2025-05-30 23:45:44'),
(3, '978-2290032722', 'Le Petit Prince', 'Antoine de Saint-Exupéry', 'Littérature', 'Cat3', 0, '2025-05-14 01:29:54', '2025-05-30 23:21:47'),
(4, '978-2080707096', 'Voyage au centre de la Terre', 'Jules Verne', 'Science-Fiction', 'cat4', 1, '2025-05-14 01:29:54', '2025-05-30 18:55:31'),
(5, '978-2253010690', 'Les Misérables', 'Victor Hugo', 'Littérature', 'CAT5', 1, '2025-05-14 01:29:54', '2025-05-30 23:37:51'),
(6, '978-2070409318', 'Le Comte de Monte-Cristo', 'Alexandre Dumas', 'Littérature', 'CAT09', 1, '2025-05-14 01:29:54', '2025-05-30 23:19:05'),
(7, '978-2253085636', 'Les Fleurs du Mal', 'Charles Baudelaire', 'Poésie', 'CAT12', 1, '2025-05-14 01:29:54', '2025-05-30 18:55:39'),
(8, '978-2253089528', 'Candide', 'Voltaire', 'Philosophie', 'cat102120', 0, '2025-05-14 01:29:54', '2025-05-30 23:45:25'),
(9, '978-2253089529', 'Physique Quantique', 'Stephen Hawking', 'Science', 'CAT120', 1, '2025-05-14 01:29:54', '2025-05-30 18:55:54'),
(10, '978-2253089530', 'Histoire de France', 'Jacques Bainville', 'Histoire', 'CAT123456', 1, '2025-05-14 01:29:54', '2025-05-30 23:19:06'),
(11, '0-306-40615-2', 'TEST', 'ss', 'Littérature', 'CAT12345645', 0, '2025-05-19 01:57:37', '2025-05-30 23:59:43'),
(12, '978-3-16-148410-0', 'ss', 'john cina', 'Économie', 'CAT09876', 1, '2025-05-19 01:58:17', '2025-05-30 18:55:44'),
(13, '0306406152', 'DRF', 'DDD', 'Littérature', 'CAT45362903', 0, '2025-05-19 02:17:07', '2025-05-30 23:45:48'),
(15, '1234567898765', 'ANTIGONE', 'JEAN ANOUI', 'Philosophie', '', 0, '2025-05-30 21:49:17', '2025-05-30 23:45:22'),
(16, '978-2070360426', 'TEST', 'DDD', 'Science', '', 1, '2025-05-30 22:45:47', '2025-05-30 22:45:47'),
(17, 'poiuyt', 'walid', 'on ecrit', 'Littérature', '', 1, '2025-05-31 00:02:43', '2025-05-31 00:02:43');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `livre_id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `date_reservation` datetime NOT NULL,
  `date_retour_prevue` datetime NOT NULL,
  `date_retour_effective` datetime DEFAULT NULL,
  `statut` enum('en_attente','active','terminee','annulee') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `livre_id`, `etudiant_id`, `date_reservation`, `date_retour_prevue`, `date_retour_effective`, `statut`) VALUES
(15, 15, 3, '2025-05-31 01:45:22', '2025-06-14 01:45:22', NULL, 'active'),
(16, 8, 3, '2025-05-31 01:45:25', '2025-06-14 01:45:25', NULL, 'active'),
(17, 2, 2, '2025-05-31 01:45:44', '2025-06-14 01:45:44', NULL, 'active'),
(18, 13, 2, '2025-05-31 01:45:48', '2025-06-14 01:45:48', NULL, 'active'),
(19, 11, 2, '2025-05-31 01:59:43', '2025-06-14 01:59:43', NULL, 'active');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `emprunts`
--
ALTER TABLE `emprunts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livre_id` (`livre_id`),
  ADD KEY `etudiant_id` (`etudiant_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `idx_date_retour` (`date_retour`),
  ADD KEY `idx_date_retour_prevue` (`date_retour_prevue`);

--
-- Index pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `livres`
--
ALTER TABLE `livres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `idx_categorie` (`categorie`),
  ADD KEY `idx_disponible` (`disponible`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livre_id` (`livre_id`),
  ADD KEY `etudiant_id` (`etudiant_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `emprunts`
--
ALTER TABLE `emprunts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `etudiants`
--
ALTER TABLE `etudiants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `livres`
--
ALTER TABLE `livres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `emprunts`
--
ALTER TABLE `emprunts`
  ADD CONSTRAINT `emprunts_ibfk_1` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emprunts_ibfk_2` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emprunts_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
