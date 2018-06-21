-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2017 at 03:49 PM
-- Server version: 5.6.26
-- PHP Version: 5.6.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tasueded_tams`
--

-- --------------------------------------------------------

--
-- Table structure for table `clinic_questions`
--

CREATE TABLE IF NOT EXISTS `clinic_questions` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `type` enum('yesno','posneg','text') NOT NULL,
  `textinput` enum('yes','no') NOT NULL,
  `placeholder` varchar(255) NOT NULL DEFAULT 'Specify',
  `cat` varchar(100) NOT NULL,
  `sortno` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clinic_questions`
--

INSERT INTO `clinic_questions` (`id`, `question`, `type`, `textinput`, `placeholder`, `cat`, `sortno`) VALUES
(1, ' Have you in the past noted yellowness of your eyes?', 'yesno', 'no', 'Specify', 'Medical History', 1),
(2, 'Have you had a cough that lasted for up to a month or more?', 'yesno', 'no', 'Specify', 'Medical History', 2),
(3, 'Have you had any penile discharge?', 'yesno', 'no', 'Specify', 'Medical History', 3),
(4, 'Have you had any vaginal itching/discharge before?', 'yesno', 'no', 'Specify', 'Medical History', 4),
(5, 'Do you suffer from any handicap?', 'yesno', 'no', 'Specify', 'Medical History', 5),
(6, 'Are you a sufferer of sickle cell disease?', 'yesno', 'no', 'Specify', 'Medical History', 6),
(7, 'Are you Asthmatic?', 'yesno', 'no', 'Specify', 'Medical History', 7),
(8, 'Do you have any current or long term medical problem for which you see or have seen a doctor on a regular basis? If so, specify; including the name and phone number of the doctor.', 'yesno', 'no', 'Specify', 'Medical History', 8),
(9, 'Have you ever been admitted overnight in the hospital? Did you have an operation? If so, specify reason for admission, date and type of surgeries, if any performed', 'yesno', 'no', 'Specify', 'Medical History', 9),
(10, 'Are there any other medical problems you think the doctor should know', 'yesno', 'no', 'Specify', 'Medical History', 10),
(11, 'Have you had any problem requiring the service of a psychologist or a mental health provider?', 'yesno', 'no', 'Specify', 'Medical History', 11),
(12, 'Have you been tested for H.I.V.?', 'yesno', 'no', 'Specify', 'Medical History', 12),
(13, 'What is the result?', 'yesno', 'no', 'Specify', 'Medical History', 12),
(14, 'Would you like tested for H.I.V.?', 'yesno', 'no', 'Specify', 'Medical History', 12),
(15, 'Do you have any visual disturbance?', 'yesno', 'no', 'Specify', 'Medical History', 12),
(16, 'What activity do enjoy in your spare time?', 'yesno', 'yes', 'Specify', 'Social History', 12),
(17, 'Have you ever represented your school in any sporting event?', 'yesno', 'yes', 'Specify', 'Social History', 12),
(18, 'Do you smoke?', 'yesno', 'yes', 'If yes, how many packs per day?', 'Social History', 12),
(19, 'Do you take alcohol?', 'yesno', 'yes', 'Specify', 'Social History', 12),
(20, 'Are you currently on any medication?', 'yesno', 'yes', 'If so, please list...', 'Drug History', 12),
(21, 'Do you have abnormal reaction (allergy) to any drug?', 'text', 'no', 'Specify', 'Drug History', 12),
(22, 'Have you been immunised against the following disease before?', 'text', 'no', 'Specify', 'Immunisation History', 12),
(23, 'When was last menstrual period? (LMP)', 'yesno', 'yes', 'Specify', 'For Female Students', 12),
(24, 'Are you currently pregnant?', 'yesno', 'yes', 'If yes state your LMP', 'For Female Students', 12);

-- --------------------------------------------------------

--
-- Table structure for table `clinic_response`
--

CREATE TABLE IF NOT EXISTS `clinic_response` (
  `response_id` int(11) NOT NULL,
  `stdid` varchar(30) NOT NULL,
  `queid` varchar(11) NOT NULL,
  `options` varchar(11) NOT NULL,
  `response` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clinic_response`
--

INSERT INTO `clinic_response` (`response_id`, `stdid`, `queid`, `options`, `response`) VALUES
(1, '20160987009', '1', 'yes', ''),
(2, '20160987009', '2', 'yes', ''),
(3, '20160987009', '3', 'yes', ''),
(4, '20160987009', '4', 'yes', ''),
(5, '20160987009', '5', 'yes', ''),
(6, '20160987009', '6', 'yes', ''),
(7, '20160987009', '7', 'yes', ''),
(8, '20160987009', '8', 'yes', ''),
(9, '20160987009', '9', 'yes', ''),
(10, '20160987009', '10', 'yes', ''),
(11, '20160987009', '11', 'yes', ''),
(12, '20160987009', '12', 'yes', ''),
(13, '20160987009', '13', 'yes', ''),
(14, '20160987009', '14', 'yes', ''),
(15, '20160987009', '15', 'yes', ''),
(16, '20160987009', '16', 'yes', 'nkl,;.'),
(17, '20160987009', '17', 'yes', 'nm,'),
(18, '20160987009', '18', 'yes', 'jk.'),
(19, '20160987009', '19', 'no', 'jkl.'),
(20, '20160987009', '20', 'yes', 'nmk.'),
(21, '20160987009', '21', '', 'jk.'),
(22, '20160987009', '22', '', 'jk,.'),
(23, '20160987009', '23', 'yes', 'njkl.'),
(24, '20160987009', '24', 'yes', 'nm,.'),
(136, '20160987008', '1', 'yes', ''),
(137, '20160987008', '2', 'no', ''),
(138, '20160987008', '3', 'yes', ''),
(139, '20160987008', '4', 'no', ''),
(140, '20160987008', '5', 'yes', ''),
(141, '20160987008', '6', 'no', ''),
(142, '20160987008', '7', 'yes', ''),
(143, '20160987008', '8', 'yes', ''),
(144, '20160987008', '9', 'no', ''),
(145, '20160987008', '10', 'yes', ''),
(146, '20160987008', '11', 'yes', ''),
(147, '20160987008', '12', 'yes', ''),
(148, '20160987008', '13', 'yes', ''),
(149, '20160987008', '14', 'yes', ''),
(150, '20160987008', '15', 'yes', ''),
(151, '20160987008', '16', 'yes', 'nkl,;.'),
(152, '20160987008', '17', 'yes', 'lkjhgfkjhg'),
(153, '20160987008', '18', 'yes', 'oiuyuioiu'),
(154, '20160987008', '19', 'no', 'jkl.'),
(155, '20160987008', '20', 'yes', 'nmk.'),
(156, '20160987008', '21', '', 'olosjsoos'),
(157, '20160987008', '22', '', 'jk,.'),
(158, '20160987008', '23', 'yes', 'njkl.'),
(159, '20160987008', '24', 'yes', 'just testing it');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clinic_questions`
--
ALTER TABLE `clinic_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `clinic_response`
--
ALTER TABLE `clinic_response`
  ADD PRIMARY KEY (`response_id`),
  ADD UNIQUE KEY `stdid` (`stdid`,`queid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clinic_questions`
--
ALTER TABLE `clinic_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT for table `clinic_response`
--
ALTER TABLE `clinic_response`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=160;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
