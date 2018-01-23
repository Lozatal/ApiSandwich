-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  mar. 23 jan. 2018 à 14:52
-- Version du serveur :  5.7.19
-- Version de PHP :  7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `sandwich`
--

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id` varchar(50) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `livraison` datetime DEFAULT NULL,
  `etat` int(11) NOT NULL,
  `prix` float DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `nom`, `prenom`, `mail`, `livraison`, `etat`, `prix`, `token`) VALUES
('14238042-e4ce-11e7-8278-54ab3ae5c6db', 'toto', '', 'yo@yopmail.com', NULL, 0, NULL, 'm6PAD'),
('4c19ac2e-e4ce-11e7-a46e-54ab3ae5c6db', 'toto', '', 'yo@yopmail.com', NULL, 0, NULL, '9119688c7f9e7f8f15c2323b7d7f33bd01e1fb8a50c4d3f5474731e2122d2018'),
('32a33dda-e4d5-11e7-8498-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', NULL, 0, NULL, 'ea66e5a521097fe3d51dc34b74c6ca40be9ac08730e44c7355a51c3a3284aa5e'),
('59445910-e4d5-11e7-9ed4-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', NULL, 0, NULL, 'd3fbef731b210008953f4cf43d693715c1a858d21478921cbb52ccf94ed06256'),
('bc96a6d0-e4d5-11e7-9d31-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', NULL, 0, NULL, '62d62d72db70ce63435bcc474d0c3ca7f7de701dfae3d824766b7f0f9f9b1ae9'),
('10a11408-e4d7-11e7-b07a-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', NULL, 0, NULL, 'e97e97f774b422fa5bda8baa107dd06d13281a165f10a3bc8af601501c94d573'),
('440493ce-e4d7-11e7-aad5-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-10 10:10:00', 0, NULL, '3daea28f486bf2dd9c0e203a4abb1120f4c551376a17ad25f11bcab28f11e3d4'),
('9fd1a4a0-e4da-11e7-b68f-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-10 10:10:00', 0, NULL, '9920ba49367e1628f5c332b2306f387d3a9bcbfa23c9f95c27a19ddf9da18231'),
('da4926a8-e4da-11e7-aa1e-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-10 10:10:00', 1, NULL, 'ba2d4c5a065da553b430b69989cd9762b442b3bf504482a7ac6861b446bfefe3'),
('e860000e-e4da-11e7-ab1a-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-10 10:10:00', 1, NULL, 'cef7e3517eba4340a71caf1e87240513bee48e047c259a60330cca7f650128e6'),
('f1953d8e-f555-11e7-9b19-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, '9b0b0be3f88f7e16a7c6e4ea2637022861b353209fcef65f9452e23b268fee14'),
('7be1ef78-f556-11e7-8b28-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, 'eaeb6778716610e85bd6f162371968981ce4a3392ef8b3b92caac4bce8dc7012'),
('dfcc14f0-fac4-11e7-8be5-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, '258f6fd256288190453ce6ea5b774585a0b681225298328025287812ad75e45b'),
('cb575f3c-fac6-11e7-914d-54ab3ae5c6db', '', '', '', NULL, 1, NULL, '5ffa96f84a1a5220a8b37652326498451cafce4e1ee521b44fedc63a9cac79fe'),
('4e4b478c-fac7-11e7-96f8-54ab3ae5c6db', '', '', '', NULL, 1, NULL, '99e8de33bc17a5688b390d5bbb8db282ee34ae3e9129751f34d1075e14c7ca08'),
('674a9f8a-fac7-11e7-af6d-54ab3ae5c6db', '', '', '', NULL, 1, NULL, '21fa3b388ce5ded141f671f199a0c6fd8edfee4094fd7db9addecae841984cf8'),
('9c401c7e-fac7-11e7-b8c9-54ab3ae5c6db', 'non', 'toto', 'test@yopmail.com', NULL, 1, NULL, '4d6e76313ef7d65456a6189e0c6a23426803abe275e0ed2e3114d797057b3c2d'),
('add4275a-fac7-11e7-804c-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', NULL, 1, NULL, 'f62ceb52405533a139d8c26642cb4581c31d0b5e69a1640acdbfdc47451f3e7c'),
('b89d46da-fac7-11e7-9406-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, '5d5099df48cce88913d3b875b10a0ed1722ece4304e03365fccc0b599ffddb8f'),
('c1597f96-fac7-11e7-a230-54ab3ae5c6db', '', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, 'd1849000c7e9203bc9a4e5b02d21525d99f24f9f26a6b749e877bf23c29e49b6'),
('c9f39998-fac7-11e7-a48e-54ab3ae5c6db', '', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, 'bbdf5101b35d10589602c61bf03b8c06242fe9f06ef4a50042801060a2259995'),
('605ab550-fac9-11e7-a999-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, '2c0cd452b888e5e118914ae71357d74188aaa9de40c1ed40ea25c122ab977041'),
('813f16b2-fac9-11e7-a3a9-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, '878eeca3a460e1cbf546118047a75f92efd85802c636b39a9ce16d4ce822507d'),
('48f1df80-facc-11e7-880c-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, 'f7cdc86449393aedcbfeac280d4daf025c39581e0d96a92c4e30734a67f15410'),
('df4b58f4-003e-11e8-a4b6-54ab3ae5c6db', 'toto', 'bob', 'yo@yopmail.com', '2017-10-02 10:10:00', 1, NULL, '2d0bcdcb24b2bf1a93f32c7a43f1f9fd096efc88927d598c80a42d927d7b5d16');

-- --------------------------------------------------------

--
-- Structure de la table `item`
--

DROP TABLE IF EXISTS `item`;
CREATE TABLE IF NOT EXISTS `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sand_id` int(11) NOT NULL,
  `comm_id` varchar(50) NOT NULL,
  `tai_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `item`
--

INSERT INTO `item` (`id`, `sand_id`, `comm_id`, `tai_id`, `quantite`) VALUES
(1, 10, '7be1ef78-f556-11e7-8b28-54ab3ae5c6db', 2, 1),
(2, 10, '7be1ef78-f556-11e7-8b28-54ab3ae5c6db', 2, 1),
(3, 10, '7be1ef78-f556-11e7-8b28-54ab3ae5c6db', 2, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
