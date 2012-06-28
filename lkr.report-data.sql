-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 03, 2011 at 03:12 PM
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

--
-- Dumping data for table `cnlf_guidelines`
--


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

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `machine_name`, `enabled`, `name`, `guideline_type`) VALUES
(3, 'max_sentence_length', 1, 'Sentences should not be longer than 25 words for descriptive text.', 'Text Structure'),
(5, 'acronyms_and_abbreviations', 1, 'Define acronyms and abbreviations.', 'Lexicology'),
(6, 'inches_and_feet', 1, 'Do not use quotation marks to represent feet or inches.', 'Orthography'),
(7, 'capitalise_first_letter', 1, 'Always capitalise the first word of a new sentence following any end punctuation.', 'Orthography'),
(8, 'numerical_first_letter', 1, 'Avoid starting a sentence with a numeral.', 'Orthography'),
(9, 'number_with_apostrophe', 1, 'Form the plural of a number by adding s without an apostrophe.', 'Orthography'),
(10, 'url_check', 1, 'If you include "www" in the site address, with or without the protocol name, the entire address is in lowercase.', 'Orthography'),
(11, 'personal_pronouns', 1, 'Avoid the use of personal pronouns, except "you".', 'Syntax'),
(12, 'firstly_and_secondly', 1, 'Do not add ''ly'' as in firstly and secondly.', 'Orthography'),
(13, 'time_format_check', 1, 'Avoid am and pm notation.', 'Orthography'),
(14, 'GMT_check', 1, 'Avoid using Greenwich Mean Time or GMT alone.', 'Orthography'),
(15, 'active_voice', 1, 'Use the active voice.', 'Syntax'),
(16, 'max_length_procedural_sentence', 1, 'Keep procedural sentences as short as possible (20 words maximum).', 'Text Structure'),
(17, 'phrasal_verbs', 1, 'Eliminate certain idiomatic phrasal verbs.', 'Syntax'),
(18, 'duplicate_words', 1, 'Avoided duplicate words.', 'Syntax');

-- --------------------------------------------------------

--
-- Table structure for table `report_vals`
--

CREATE TABLE IF NOT EXISTS `report_vals` (
  `id` varchar(255) CHARACTER SET latin1 NOT NULL,
  `value` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Storage of preference values for each analysis report.';

--
-- Dumping data for table `report_vals`
--

INSERT INTO `report_vals` (`id`, `value`) VALUES
('max_sentence_length', '25'),
('acronyms_and_abbreviations', '[A-Z]{2,}'),
('inches_and_feet', '[0-9]+[\\"\\'']\\s'),
('capitalise_first_letter', '^[^A-Z0-9]'),
('numerical_first_letter', '^[0-9]'),
('number_with_apostrophe', '[0-9]+[\\'']s'),
('duplicate_words', '(\\b[a-zA-Z]+\\b)\\s\\b\\1\\b'),
('firstly_and_secondly', '\\b(first|second|First|Second)ly\\b'),
('time_format_check', '\\b(am|AM|pm|PM)\\b'),
('GMT_check', '\\b(Greenwich Mean Time|GMT)\\b'),
('active_voice', '^\\b(to|To)\\b'),
('max_length_procedural_sentence', '20'),
('phrasal_verbs', 'stands for, touch and go, on the ball, on tenderhooks, fall on deaf ears, turn a blind eye, break the ice, bury the hatchet, smell a rat, in the bag, flat out, pass the buck, lesser evil, stumbling block, in the blink of an eye, give the green light');

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

--
-- Dumping data for table `stopwords`
--

INSERT INTO `stopwords` (`stopword_id`, `stopword`, `title_of_warning`, `warning_description`, `guideline_type`, `enabled`) VALUES
(1, 'text', 'Sample Title', 'A warning description goes here.', 'Custom', 1),
(13, ';', 'Do not use semicolons.', 'Use a colon instead (if appropriate).', 'Orthography', 1),
(14, '/', 'Do no use forward slashes.', 'Use "or" instead (if appropriate)', 'Orthography', 1),
(16, '(', 'Do not use parenthetical statements.', 'Put parenthetical statements into a new sentence.', 'Orthography', 1),
(17, '(s)', 'Do not use (s) to form plural nouns.', 'Use either the singular or the plural of the noun.', 'Orthography', 1),
(18, '&', 'Do not use an ampersand in place of the word "and".', 'Use "and" instead (if appropriate).', 'Orthography', 1),
(19, '=', 'Do not use "=" in place of text.', 'Use "is" instead (if appropriate)', 'Orthography', 1),
(20, 'should', 'In an instruction, write the verb in the imperative form.', 'Use imperative, commanding language for instructions.', 'Syntax', 1),
(21, '- ', 'Avoid suspended compound adjectives.', 'Do not use hyphens.', 'Morphology', 1),
(22, 'would', 'Avoid use of "would".', 'Do not use the word "would".', 'Syntax', 1),
(23, 'will', 'Avoid use of future tense (wherever possible).', 'Speak in the present tense wherever possible.', 'Syntax', 1);
