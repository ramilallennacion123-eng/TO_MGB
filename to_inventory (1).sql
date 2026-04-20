-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 01:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `to_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `travel_clearances`
--

CREATE TABLE `travel_clearances` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `purpose` longtext NOT NULL,
  `location` varchar(255) NOT NULL,
  `travel_date` varchar(200) NOT NULL,
  `planner_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending_planner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travel_clearances`
--

INSERT INTO `travel_clearances` (`id`, `name`, `purpose`, `location`, `travel_date`, `planner_id`, `status`, `created_at`) VALUES
(6, 'Ramil Allen Nacion', '[\"ANTIFRAGILE\"]', 'SSSSSSSSS', 'December 12-13, 2026', 201, 'pending_planner', '2026-04-07 03:03:01'),
(7, 'Aries M. Bado', '[\"To attend FY 2027 Budget Proposal\"]', 'NCR', 'April 9-10, 2026', 201, 'approved_planner', '2026-04-08 00:25:47'),
(8, 'Ramil Allen Nacion', '[\"For the General Meeting\"]', 'Daraga, Albay', 'April 18 - April 20, 2026', 201, 'pending_planner', '2026-04-20 12:06:40');

-- --------------------------------------------------------

--
-- Table structure for table `travel_orders`
--

CREATE TABLE `travel_orders` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `division_unit` varchar(100) DEFAULT NULL,
  `departure_date` date NOT NULL,
  `official_station` varchar(150) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `arrival_date` date NOT NULL,
  `purpose` text NOT NULL,
  `per_diems` varchar(150) DEFAULT NULL,
  `assistants` text DEFAULT NULL,
  `appropriation` varchar(150) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `officer_id` int(11) NOT NULL,
  `applicant_signature` varchar(255) NOT NULL,
  `status` enum('pending_do','pending_rd','approved','rejected_do','rejected_rd','completed') DEFAULT 'pending_do',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `do_signature` varchar(255) DEFAULT NULL,
  `rd_signature` varchar(255) DEFAULT NULL,
  `applicant_sig_x` int(11) DEFAULT 350,
  `applicant_sig_y` int(11) DEFAULT 480,
  `do_sig_x` int(11) DEFAULT 50,
  `do_sig_y` int(11) DEFAULT 320,
  `rd_sig_x` int(11) DEFAULT 480,
  `rd_sig_y` int(11) DEFAULT 320,
  `rejection_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travel_orders`
--

INSERT INTO `travel_orders` (`id`, `name`, `salary`, `position`, `division_unit`, `departure_date`, `official_station`, `destination`, `arrival_date`, `purpose`, `per_diems`, `assistants`, `appropriation`, `remarks`, `officer_id`, `applicant_signature`, `status`, `created_at`, `do_signature`, `rd_signature`, `applicant_sig_x`, `applicant_sig_y`, `do_sig_x`, `do_sig_y`, `rd_sig_x`, `rd_sig_y`, `rejection_remarks`) VALUES
(15, 'Aries M. Bado', '', 'Senior I.T. Support Specialist', 'ORD', '2026-04-09', 'MGB', 'NCR', '2026-04-10', '[\"To attend MGB FY 2027 Budget Proposal\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69d5a0d0377fc8.60318104.png', 'completed', '2026-04-08 00:26:56', '../uploads/signatures/chief_101.png', '../uploads/signatures/rd_106.png', 226, 750, -20, 571, 342, 572, NULL),
(16, 'marvin bobby sodsod', '', 'Science Research Specialist II', '', '2026-04-08', 'CENRO Iriga', 'Baao, Camarines Sur', '2026-04-10', '[\"To conduct investigation on the complaint against the anjinator construction And supply corp. in Brgy. Agdangan, Baao, Camarines Sur\",\"to conduct research and interview at DAR Baoo office regarding the lot Status reg the quarry site of ACSC\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69d5b693060ae9.15034865.png', 'completed', '2026-04-08 01:59:47', '../uploads/signatures/chief_101.png', '../uploads/signatures/rd_106.png', 182, 822, -8, 638, 340, 642, NULL),
(21, 'Celerino Calmada', '', '', '', '2026-04-17', '', '', '2026-04-19', '[\"\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e02d4a01ae49.54922341.png', 'completed', '2026-04-16 00:28:58', '../uploads/signatures/chief_101.png', '../uploads/signatures/rd_106.png', 183, 771, -9, 567, 335, 575, NULL),
(22, 'Ramil Allen Nacion', '', '', '', '0000-00-00', '', '', '0000-00-00', '[\"\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e085a642b2c6.91005196.png', 'completed', '2026-04-16 06:45:58', '../uploads/signatures/chief_101.png', '../uploads/signatures/rd_106.png', 207, 757, -12, 574, 334, 574, NULL),
(23, 'Naoi Rei', '', 'Main Rapper', 'IVE', '2026-04-21', '', 'Seoul-tan kudarat', '2026-04-21', '[\"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut sit amet euismod lacus, ac feugiat lectus. Vivamus pharetra suscipit tincidunt. Cras ut fermentum ex, sed facilisis lacus. Duis purus elit, cursus sed ante quis, sodales porttitor mi.\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e5689a659238.39963529.png', 'completed', '2026-04-19 23:43:22', '../uploads/signatures/chief_101.png', '../uploads/signatures/rd_106.png', 238, 778, -14, 611, 329, 609, NULL),
(24, 'Ramil Allen Nacion', '', 'OJT', 'ORD', '2026-04-20', 'Legazpi', 'Daraga', '2026-04-21', '[\"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut sit amet euismod lacus, ac feugiat lectus. Vivamus pharetra suscipit tincidunt. \"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e5f41ceb8444.54624514.png', 'pending_do', '2026-04-20 09:38:36', NULL, NULL, 350, 480, 50, 320, 480, 320, NULL),
(25, 'Celerino Calmada', '', 'OJT', 'ORD', '2026-04-21', 'Washington', 'Legazpi', '2026-04-22', '[\"To conduct investigation on the complaint against the anjinator construction And supply corp. in Brgy. Agdangan, Baao, Camarines Sur\",\"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut sit amet euismod lacus, ac feugiat lectus. \"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e5f469129719.56669624.png', 'approved', '2026-04-20 09:39:53', '../uploads/signatures/chief_101.png', '../uploads/signatures/rd_106.png', 350, 480, 50, 320, 480, 320, NULL),
(26, 'Kenneth Palmiano', '', '', '', '0000-00-00', '', '', '0000-00-00', '[\"\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e5f961267402.09226845.png', 'rejected_rd', '2026-04-20 10:01:05', '../uploads/signatures/chief_101.png', NULL, 350, 480, 50, 320, 480, 320, NULL),
(29, 'Life Advincula', '', '', '', '0000-00-00', '', '', '0000-00-00', '[\"\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e6158d0dbca9.20941821.png', 'rejected_do', '2026-04-20 12:01:17', NULL, NULL, 350, 480, 50, 320, 480, 320, NULL),
(30, 'Ramil Allen Nacion', '', '', '', '0000-00-00', '', '', '0000-00-00', '[\"\"]', '', '[\"\"]', '', '', 101, 'uploads/signatures/sig_69e6208de11f26.40727476.png', 'rejected_do', '2026-04-20 12:48:13', NULL, NULL, 350, 480, 50, 320, 480, 320, 'just because');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `name` text NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `user_signature` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `password`, `role`, `position`, `user_signature`) VALUES
(1, 'celerino', 'celerino', 'calmada', 'ict', NULL, NULL),
(2, 'ramil', 'Ramil Allen Nacion', '$2y$10$wtIWdf2rnn0li.ZWwRhALejBj0sr.Qg0FQyzsi/t0yyKlCHKg4ke6', 'ict', 'INTERN', NULL),
(101, 'antonio', 'Antonio Marasigan', '$2y$10$xpQy6G119Kx8zhJNKvzBOuNuYJzPkGEcfndDp4jfegzTDklyMLWIi', 'chief', 'Engr. V/ Chief MMD', '../uploads/signatures/chief_101.png'),
(102, 'arlene', 'Arlen E. Dayao', 'dayao', 'chief', 'CHIEF ', '../uploads/signatures/chief_102.png'),
(106, 'rd', 'Guillermo A. Molina Jr. IV', '$2y$10$ahe1qLZ9I2awFtLOLEd.OOGczqXZ3y9at6T8Yq05M66Ns1PQ8Gxh.', 'rd', 'Regional Director', '../uploads/signatures/rd_106.png'),
(201, 'josie', 'Josie Jacob', '$2y$10$nbObkMm8A8pNFTRoMzSiVehFfkJBIKWKX/V81ZvuzzYW07tfggY9.', 'planner', '', NULL),
(203, 'Rei', 'Naoi Rei', '$2y$10$lWKSvi682xxql0DTBUETOONx/TlO4q/Fdt1Fj70vJ4hmI53Q4s0i.', 'ict', 'Rapper', NULL),
(204, 'aries', 'aries', '$2y$10$T7hmq/9TYvujYOr.EB3cLOwt5eNef9ZVndAyvVeVCUAXF7i9cM/EK', 'ict', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `travel_clearances`
--
ALTER TABLE `travel_clearances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `travel_orders`
--
ALTER TABLE `travel_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `travel_clearances`
--
ALTER TABLE `travel_clearances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `travel_orders`
--
ALTER TABLE `travel_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
