SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `categorie` (`id`, `nom`, `description`) VALUES
(1,	'bio',	'sandwichs ingrédients bio et locaux'),
(2,	'végétarien',	'sandwichs végétariens - peuvent contenir des produits laitiers'),
(3,	'traditionnel',	'sandwichs traditionnels : jambon, pâté, poulet etc ..'),
(4,	'chaud',	'sandwichs chauds : américain, burger, '),
(5,	'veggie',	'100% Veggie'),
(16,	'world',	'Tacos, nems, burritos, nos sandwichs du monde entier');

INSERT INTO `sand2cat` (`sand_id`, `cat_id`) VALUES
(4,	3),
(4,	4),
(5,	3),
(5,	1),
(6,	4),
(6,	16);

INSERT INTO `sandwich` (`id`, `nom`, `description`, `type_pain`, `img`) VALUES
(4,	'le bucheron',	'un sandwich de bucheron : frites, fromage, saucisse, steack, lard grillé, mayo',	'baguette campagne',	NULL),
(5,	'jambon-beurre',	'le jambon-beurre traditionnel, avec des cornichons',	'baguette',	NULL),
(6,	'fajitas poulet',	'fajitas au poulet avec ses tortillas de mais, comme à Puebla',	'tortillas',	NULL),
(7,	'le forestier',	'un bon sandwich au gout de la forêt',	'pain complet',	NULL);

INSERT INTO `taille_sandwich` (`id`, `nom`, `description`) VALUES
(1,	'petite faim',	'le sandwich rapide pour les petites faims, même si elles sont sérieuses'),
(2,	'complet',	'le sandwich taille optimale pour un casse-croûte à toute heure'),
(3,	'grosse faim',	'à partager, ou pour les affamés'),
(4,	'ogre',	'pour les faims d\'ogres, et encore ....');

INSERT INTO `tarif` (`taille_id`, `sand_id`, `prix`) VALUES
(1,	4,	6.00),
(2,	4,	6.50),
(3,	4,	7.00),
(4,	4,	8.00),
(1,	5,	3.50),
(2,	5,	4.00),
(3,	5,	5.00),
(4,	5,	6.00),
(1,	6,	5.00),
(2,	6,	7.00),
(3,	6,	9.00),
(4,	6,	12.00);
