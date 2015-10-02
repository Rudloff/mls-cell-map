SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `cells` (
  `radio` enum('CDMA','GSM','LTE','UMTS') NOT NULL,
  `mcc` smallint(3) unsigned NOT NULL,
  `net` smallint(5) unsigned NOT NULL,
  `area` smallint(5) unsigned NOT NULL,
  `cell` int(10) unsigned NOT NULL,
  `unit` varchar(10) DEFAULT NULL,
  `lon` decimal(21,10) NOT NULL,
  `lat` decimal(21,10) NOT NULL,
  `range` mediumint(7) unsigned NOT NULL,
  `samples` smallint(5) unsigned NOT NULL,
  `changeable` tinyint(1) NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  `averageSignal` varchar(0) DEFAULT NULL,
  KEY `search` (`mcc`,`net`,`cell`,`area`),
  KEY `coords` (`lon`,`lat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cells_country` (
  `MCC` smallint(3) unsigned NOT NULL,
  `Country` varchar(255) NOT NULL,
  KEY `MCC` (`MCC`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cells_mnc` (
  `MCC` smallint(3) unsigned NOT NULL,
  `MNC` smallint(3) unsigned NOT NULL,
  `Network` text NOT NULL,
  KEY `MCC+MNC` (`MCC`,`MNC`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
