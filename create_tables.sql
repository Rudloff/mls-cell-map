CREATE TABLE IF NOT EXISTS `cells` (
  `radio` varchar(4) DEFAULT NULL,
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
  `averageSignal` varchar(10) DEFAULT NULL,
  KEY (`cell`),
  KEY `coords` (`lon`,`lat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cells_mnc` (
  `MCC` smallint(3) unsigned NOT NULL,
  `MNC` smallint(3) unsigned NOT NULL,
  `Network` text NOT NULL,
  KEY `MCC+MNC` (`MCC`,`MNC`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cells_country` (
  `MCC` smallint(3) unsigned NOT NULL,
  `Country` varchar(255) NOT NULL,
  KEY `MCC` (`MCC`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
