-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2017 at 10:52 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tams_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `accom_accomodation`
--

CREATE TABLE IF NOT EXISTS `accom_accomodation` (
  `accomid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location` int(11) NOT NULL,
  `building_name` varchar(255) NOT NULL,
  `caretaker_name` varchar(255) NOT NULL,
  `caretaker_phone` varchar(50) NOT NULL,
  `building_address` text NOT NULL,
  `pay_amount` double NOT NULL,
  `pay_mode` varchar(50) NOT NULL,
  `no_of_rooms` varchar(10) NOT NULL DEFAULT '0',
  `building_type` int(2) NOT NULL,
  `gender` varchar(20) NOT NULL,
  PRIMARY KEY (`accomid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `accom_accomodation`
--

INSERT INTO `accom_accomodation` (`accomid`, `location`, `building_name`, `caretaker_name`, `caretaker_phone`, `building_address`, `pay_amount`, `pay_mode`, `no_of_rooms`, `building_type`, `gender`) VALUES
(9, 12, 'Kensarowiwa Hall', 'Kazeem Popoola', '0807456998', '16 Lagos grarage Off Ondo road Ijebu ode', 65000, 'Session', '0-5', 1, 'Mix');

-- --------------------------------------------------------

--
-- Table structure for table `accom_accomodation_features`
--

CREATE TABLE IF NOT EXISTS `accom_accomodation_features` (
  `fid` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `accomid` int(10) unsigned NOT NULL,
  `featid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid`),
  KEY `accomid` (`accomid`,`featid`),
  KEY `accomid_2` (`accomid`),
  KEY `featid` (`featid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `accom_accomodation_features`
--

INSERT INTO `accom_accomodation_features` (`fid`, `accomid`, `featid`) VALUES
(14, 0, 1),
(15, 0, 2),
(16, 0, 3),
(17, 0, 5),
(18, 0, 6),
(19, 0, 7),
(20, 9, 1),
(21, 9, 2),
(22, 9, 4),
(23, 9, 5),
(24, 9, 6),
(25, 9, 7);

-- --------------------------------------------------------

--
-- Table structure for table `accom_building_type`
--

CREATE TABLE IF NOT EXISTS `accom_building_type` (
  `buidid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`buidid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `accom_building_type`
--

INSERT INTO `accom_building_type` (`buidid`, `name`, `description`) VALUES
(1, 'A Room Self-contain', NULL),
(2, '2-bed Room Flat', NULL),
(3, 'Single rooms', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `accom_features`
--

CREATE TABLE IF NOT EXISTS `accom_features` (
  `featid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `featname` varchar(255) NOT NULL,
  PRIMARY KEY (`featid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `accom_features`
--

INSERT INTO `accom_features` (`featid`, `featname`) VALUES
(1, 'Water closet'),
(2, 'Eletrcicity'),
(3, 'Water'),
(4, 'Mordern building'),
(5, 'Furnished '),
(6, 'Fenced'),
(7, 'Security Gate');

-- --------------------------------------------------------

--
-- Table structure for table `accom_hostel_location`
--

CREATE TABLE IF NOT EXISTS `accom_hostel_location` (
  `locid` int(10) NOT NULL AUTO_INCREMENT,
  `locname` varchar(255) NOT NULL,
  PRIMARY KEY (`locid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `accom_hostel_location`
--

INSERT INTO `accom_hostel_location` (`locid`, `locname`) VALUES
(1, 'Ijagun'),
(2, 'Ojuri'),
(3, 'Imaweje'),
(4, 'Abapawa'),
(5, 'Ijele'),
(6, 'Mobalufon'),
(7, 'Texaco'),
(8, 'Latogun'),
(9, 'Adefisan'),
(10, 'Cele'),
(11, 'Orunfe'),
(12, 'Lagos Garage'),
(13, 'Ondo Road'),
(14, 'Igbeba'),
(15, 'IIbadan Road'),
(16, 'Ibadan Garage');

-- --------------------------------------------------------

--
-- Table structure for table `accom_student_location`
--

CREATE TABLE IF NOT EXISTS `accom_student_location` (
  `stdid` varchar(12) NOT NULL,
  `locid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`stdid`),
  KEY `locid` (`locid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
