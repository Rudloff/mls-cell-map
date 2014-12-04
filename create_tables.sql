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
  `averageSignal` varchar(10) DEFAULT NULL,
  KEY (`cell`),
  KEY `coords` (`lon`,`lat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cells_mnc` (
  `MCC` int(3) DEFAULT NULL,
  `MCC (int)` int(4) DEFAULT NULL,
  `MNC` varchar(3) DEFAULT NULL,
  `MNC (int)` int(4) DEFAULT NULL,
  `ISO` varchar(3) DEFAULT NULL,
  `Country` varchar(33) DEFAULT NULL,
  `Country Code` int(4) DEFAULT NULL,
  `Network` varchar(72) DEFAULT NULL,
  KEY `MCC+MNC` (`MCC`,`MNC`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
