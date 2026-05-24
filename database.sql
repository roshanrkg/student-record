-- Student Record Management System Database Script
-- Compatible with MySQL 5.x and newer

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `tbl_login`
--
CREATE TABLE IF NOT EXISTS `tbl_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_login`
--
-- Password is 'admin123' securely hashed with PHP password_hash (BCRYPT)
INSERT INTO `tbl_login` (`id`, `username`, `password`, `email`, `name`) VALUES
(1, 'admin', '$2y$10$wN92QO5eP7.Gq1U/4f50lOHsJ2632sV6Y8B321.xR8nQ7g5dOFe2O', 'admin@mail.com', 'System Administrator')
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `countries`
--
CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `countries`
--
INSERT INTO `countries` (`id`, `name`) VALUES
(1, 'India'),
(2, 'United States'),
(3, 'United Kingdom'),
(4, 'Canada')
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `states`
--
CREATE TABLE IF NOT EXISTS `states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `states`
--
INSERT INTO `states` (`id`, `country_id`, `name`) VALUES
-- India
(1, 1, 'Maharashtra'),
(2, 1, 'Karnataka'),
(3, 1, 'Delhi'),
-- United States
(4, 2, 'California'),
(5, 2, 'New York'),
(6, 2, 'Texas'),
-- United Kingdom
(7, 3, 'England'),
(8, 3, 'Scotland'),
-- Canada
(9, 4, 'Ontario'),
(10, 4, 'Quebec')
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `cities`
--
CREATE TABLE IF NOT EXISTS `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cities`
--
INSERT INTO `cities` (`id`, `state_id`, `name`) VALUES
-- Maharashtra
(1, 1, 'Mumbai'),
(2, 1, 'Pune'),
(3, 1, 'Nagpur'),
-- Karnataka
(4, 2, 'Bengaluru'),
(5, 2, 'Mysuru'),
-- Delhi
(6, 3, 'New Delhi'),
-- California
(7, 4, 'Los Angeles'),
(8, 4, 'San Francisco'),
-- New York
(9, 5, 'New York City'),
(10, 5, 'Buffalo'),
-- Texas
(11, 6, 'Houston'),
(12, 6, 'Austin'),
-- England
(13, 7, 'London'),
(14, 7, 'Manchester'),
-- Scotland
(15, 8, 'Edinburgh'),
(16, 8, 'Glasgow'),
-- Ontario
(17, 9, 'Toronto'),
(18, 9, 'Ottawa'),
-- Quebec
(19, 10, 'Montreal')
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `tbl_course`
--
CREATE TABLE IF NOT EXISTS `tbl_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) NOT NULL UNIQUE,
  `course_name` varchar(150) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_course`
--
INSERT INTO `tbl_course` (`id`, `course_code`, `course_name`) VALUES
(1, 'CS101', 'Bachelor of Technology (Computer Science)'),
(2, 'BA202', 'Master of Business Administration (Finance)'),
(3, 'SC303', 'Bachelor of Science (Physics)')
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `subject`
--
CREATE TABLE IF NOT EXISTS `subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL UNIQUE,
  `subject_name` varchar(150) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `tbl_course` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `subject`
--
INSERT INTO `subject` (`id`, `course_id`, `subject_code`, `subject_name`) VALUES
-- Computer Science (course_id: 1)
(1, 1, 'CS-301', 'Database Management Systems'),
(2, 1, 'CS-302', 'Data Structures & Algorithms'),
(3, 1, 'CS-303', 'Web Technologies (PHP & MySQL)'),
-- MBA Finance (course_id: 2)
(4, 2, 'FIN-401', 'Corporate Financial Management'),
(5, 2, 'FIN-402', 'Investment & Portfolio Analysis'),
-- Physics (course_id: 3)
(6, 3, 'PHY-101', 'Classical Mechanics'),
(7, 3, 'PHY-102', 'Quantum Mechanics')
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `session`
--
CREATE TABLE IF NOT EXISTS `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_name` varchar(50) NOT NULL UNIQUE,
  `status` int(1) NOT NULL DEFAULT '1', -- 1 for active, 0 for inactive
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `session`
--
INSERT INTO `session` (`id`, `session_name`, `status`) VALUES
(1, '2025-2026', 1),
(2, '2026-2027', 0)
ON DUPLICATE KEY UPDATE `id`=`id`;


--
-- Table structure for table `registration`
--
CREATE TABLE IF NOT EXISTS `registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_reg_no` varchar(100) NOT NULL UNIQUE,
  `course_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_name` varchar(150) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `mobile` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `tbl_course` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  FOREIGN KEY (`state_id`) REFERENCES `states` (`id`),
  FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping dummy students
--
INSERT INTO `registration` (`id`, `student_reg_no`, `course_id`, `session_id`, `student_name`, `gender`, `dob`, `email`, `mobile`, `address`, `country_id`, `state_id`, `city_id`) VALUES
(1, 'REG-2025-001', 1, 1, 'John Doe', 'Male', '2003-05-15', 'john.doe@example.com', '9876543210', '123 Main St, Apartment 4B', 2, 4, 7),
(2, 'REG-2025-002', 2, 1, 'Priya Sharma', 'Female', '2001-11-20', 'priya.sharma@example.com', '9988776655', 'Flat 402, Royal Residency, Kothrud', 1, 1, 2)
ON DUPLICATE KEY UPDATE `id`=`id`;
