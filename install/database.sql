SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `cache` (
  `id` varchar(256) NOT NULL,
  `cachetime` int(20) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `downloads` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12757 ;

CREATE TABLE IF NOT EXISTS `exceptions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mod` varchar(32) NOT NULL,
  `time` int(20) NOT NULL,
  `os` varchar(8) NOT NULL,
  `version` int(5) NOT NULL,
  `exception` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

CREATE TABLE IF NOT EXISTS `mods` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(50) NOT NULL,
  `longdesc` text NOT NULL,
  `version` int(5) NOT NULL,
  `versionCode` varchar(5) NOT NULL,
  `lastupdate` int(20) NOT NULL DEFAULT '0',
  `opensource` varchar(256) NOT NULL,
  `devname` varchar(100) NOT NULL,
  `bugs` varchar(500) NOT NULL,
  `available` int(1) NOT NULL DEFAULT '0',
  `downloads` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `time` int(20) NOT NULL,
  `request` varchar(500) NOT NULL,
  `msg` varchar(255) NOT NULL,
  `success` int(1) NOT NULL DEFAULT '1',
  `exectime` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=70866 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
