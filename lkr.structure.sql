-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 03, 2011 at 03:10 PM
-- Server version: 5.1.54
-- PHP Version: 5.3.5-1ubuntu7.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lkr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cnlf_guidelines`
--

CREATE TABLE IF NOT EXISTS `cnlf_guidelines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CNLF_id` varchar(255) NOT NULL,
  `machine_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=204 ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CNLF_id` varchar(255) DEFAULT NULL,
  `import_date` datetime DEFAULT NULL,
  `complete_date` datetime DEFAULT NULL,
  `closed_date` datetime DEFAULT NULL,
  `author_name` text,
  `email_address` text,
  `company_name` text,
  `domain` text,
  `source_language` varchar(255) NOT NULL DEFAULT 'en',
  `target_language` varchar(255) DEFAULT NULL,
  `original_file` varchar(255) DEFAULT NULL,
  `initial_warnings` int(11) NOT NULL DEFAULT '0',
  `total_send_backs` int(11) NOT NULL DEFAULT '0',
  `xliff_input` text,
  PRIMARY KEY (`job_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=384 ;

-- --------------------------------------------------------

--
-- Table structure for table `glossaryEntries`
--

CREATE TABLE IF NOT EXISTS `glossaryEntries` (
    `glossary_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `job_id` int(10) unsigned NOT NULL,
    `ref` VARCHAR(255) NOT NULL,
    `term` VARCHAR(255) NOT NULL,
    `translation` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`glossary_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `annotatorsRefs`
--

CREATE TABLE IF NOT EXISTS `annotatorsRefs` (
    `ref_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `job_id` int(10) unsigned NOT NULL,
    `file_id` int(10) unsigned DEFAULT NULL,
    `ref` VARCHAR(255) NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`ref_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `machine_name` varchar(255) CHARACTER SET latin1 NOT NULL COMMENT 'All-lowercase name with words separated by underscores. Allows for each lookup of an anlysis_id.',
  `enabled` tinyint(4) NOT NULL COMMENT '1 or 0',
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `guideline_type` text NOT NULL,
  PRIMARY KEY (`report_id`),
  UNIQUE KEY `machine_name` (`machine_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `report_vals`
--

CREATE TABLE IF NOT EXISTS `report_vals` (
  `id` varchar(255) CHARACTER SET latin1 NOT NULL,
  `value` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Storage of preference values for each analysis report.';

-- --------------------------------------------------------

--
-- Table structure for table `segments`
--

CREATE TABLE IF NOT EXISTS `segments` (
  `job_id` int(10) unsigned NOT NULL,
  `segment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_raw` text CHARACTER SET latin1 NOT NULL,
  `source` text CHARACTER SET latin1 NOT NULL,
  `edited` tinyint(1) DEFAULT NULL,
  `target_raw` text CHARACTER SET latin1,
  `file_id` int(10) unsigned DEFAULT 1,
  `trans_unit_id` varchar(255) DEFAULT NULL,
  `comment` text,
  `has_warning` tinyint(4) DEFAULT NULL,
  `translate` BIT(1) DEFAULT 1,
  UNIQUE KEY `job_id` (`job_id`,`segment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `stopwords`
--

CREATE TABLE IF NOT EXISTS `stopwords` (
  `stopword_id` int(11) NOT NULL AUTO_INCREMENT,
  `stopword` text NOT NULL,
  `title_of_warning` text NOT NULL,
  `warning_description` text NOT NULL,
  `guideline_type` varchar(255) NOT NULL DEFAULT 'Custom',
  `enabled` int(1) DEFAULT '1',
  PRIMARY KEY (`stopword_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;
