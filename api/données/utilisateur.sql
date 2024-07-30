-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 30 juil. 2024 à 14:08
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `test`
--

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `point_de_vente_id` int(11) NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `plain_password` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `point_de_vente_id`, `pseudo`, `mdp`, `nom`, `prenom`, `email`, `plain_password`, `role`) VALUES
(1, 1, 'mcloic', '$2y$13$2nu0Rf3xW4nyQPTt2M4kjO5alW/Z6wWRCG7Y0.bB0dAJnM5SfxmkC', 'Loic', 'Rasoarahona', 'goodcloud68@gmail.com', NULL, 'admin'),
(2, 1, 'Mathieu', '$2y$13$zMfX6ETr2UaCsTPVjd16fuz7Vp0vdfCJqeLn4P6tjaXBeN41Z369O', 'Andrianjafy', 'Mathieu', 'mathieucoco@gmail.com', NULL, NULL),
(3, 2, 'Hery', '$2y$13$xz./Tj26vjLH8vILt4eP1.QjvhO0u2uw.9O4PELMAvWmoCyC1ExCy', 'Razafimahefa', 'Hery', 'razafimahefa.hery@gmail.com', NULL, NULL),
(4, 1, 'Hiaro', '$2y$13$jtxOKVscVFc/VeZWfa38K.QtcO90nrrF9zwsSFMGi9iDVUdBhPxam', 'Hiaro', 'Nathanael', 'hiaronathanael178@gmail.com', NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1D1C63B33F95E273` (`point_de_vente_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `FK_1D1C63B33F95E273` FOREIGN KEY (`point_de_vente_id`) REFERENCES `point_de_vente` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
