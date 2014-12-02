-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Ven 21 Novembre 2014 à 13:50
-- Version du serveur: 5.5.40
-- Version de PHP: 5.4.35-0+deb7u2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `cells`
--

-- --------------------------------------------------------

--
-- Structure de la table `cells`
--

CREATE TABLE IF NOT EXISTS `cells` (
  `radio` varchar(3) DEFAULT NULL,
  `mcc` int(3) DEFAULT NULL,
  `net` int(1) DEFAULT NULL,
  `area` int(2) DEFAULT NULL,
  `cell` int(5) DEFAULT NULL,
  `unit` varchar(10) DEFAULT NULL,
  `lon` decimal(12,10) DEFAULT NULL,
  `lat` decimal(12,10) DEFAULT NULL,
  `range` int(4) DEFAULT NULL,
  `samples` int(3) DEFAULT NULL,
  `changeable` int(1) DEFAULT NULL,
  `created` int(10) DEFAULT NULL,
  `updated` int(10) DEFAULT NULL,
  `averageSignal` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cells_mnc` (
  `MCC` int(3) DEFAULT NULL,
  `MCC (int)` int(4) DEFAULT NULL,
  `MNC` varchar(3) DEFAULT NULL,
  `MNC (int)` int(4) DEFAULT NULL,
  `ISO` varchar(3) DEFAULT NULL,
  `Country` varchar(33) DEFAULT NULL,
  `Country Code` int(4) DEFAULT NULL,
  `Network` varchar(72) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
