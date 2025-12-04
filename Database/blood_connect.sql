-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 05:10 PM
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
-- Database: `blood_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `availability_logs`
--

CREATE TABLE `availability_logs` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `availability` varchar(32) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `availability_logs`
--

INSERT INTO `availability_logs` (`id`, `donor_id`, `availability`, `changed_at`) VALUES
(1, 7, 'not available', '2025-12-01 16:59:48'),
(2, 6, 'not available', '2025-12-01 16:59:50'),
(3, 5, 'not available', '2025-12-01 16:59:50'),
(4, 1, 'not available', '2025-12-01 16:59:51'),
(5, 3, 'not available', '2025-12-01 16:59:53'),
(6, 4, 'not available', '2025-12-01 16:59:54'),
(7, 7, 'available', '2025-12-01 16:59:55'),
(8, 6, 'available', '2025-12-01 17:00:02');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donated_on` date NOT NULL,
  `units` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_id`, `donated_on`, `units`, `created_at`) VALUES
(1, 1, '2024-12-10', 1, '2025-12-01 17:41:34'),
(2, 2, '2024-11-05', 1, '2025-12-01 17:41:34'),
(3, 1, '2024-08-20', 1, '2025-12-01 17:41:34'),
(4, 3, '2024-07-14', 1, '2025-12-01 17:41:34'),
(5, 2, '2024-06-01', 1, '2025-12-01 17:41:34'),
(6, 1, '2023-12-02', 1, '2025-12-01 17:41:34'),
(7, 4, '2024-09-22', 1, '2025-12-01 17:41:34'),
(8, 2, '2024-03-18', 1, '2025-12-01 17:41:34'),
(9, 5, '2024-02-10', 1, '2025-12-01 17:41:34'),
(10, 3, '2024-10-11', 1, '2025-12-01 17:41:34'),
(11, 1, '2024-05-06', 1, '2025-12-01 17:41:34'),
(12, 2, '2024-01-08', 1, '2025-12-01 17:41:34'),
(13, 6, '2024-04-25', 1, '2025-12-01 17:41:34'),
(14, 2, '2024-12-01', 1, '2025-12-01 17:41:34'),
(15, 3, '2024-12-05', 1, '2025-12-01 17:41:34'),
(16, 7, '2025-12-04', 1, '2025-12-01 18:18:27'),
(17, 2, '2025-12-20', 1, '2025-12-01 18:19:18'),
(18, 2, '2025-12-05', 3, '2025-12-03 15:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `city` varchar(100) NOT NULL,
  `area` varchar(150) DEFAULT NULL,
  `last_donation_date` date DEFAULT NULL,
  `availability` enum('available','not_available') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`id`, `name`, `phone`, `blood_group`, `city`, `area`, `last_donation_date`, `availability`, `created_at`) VALUES
(1, 'Rafi Ahmed', '01710000001', 'O+', 'Dhaka', 'Mirpur', '2024-08-01', 'not_available', '2025-12-01 14:36:26'),
(2, 'Sadia Khan', '01710000002', 'A+', 'Dhaka', 'Dhanmondi', '2024-02-01', 'available', '2025-12-01 14:36:26'),
(3, 'Tariq Islam', '01710000003', 'B+', 'Chattogram', 'Pahartali', '2024-05-20', 'available', '2025-12-01 14:36:26'),
(4, 'Mona Rahman', '01710000004', 'O-', 'Khulna', 'Sonadanga', '2023-09-10', 'available', '2025-12-01 14:36:26'),
(5, 'Mehedi Hasan', '014582585', 'B+', 'Dhaka', 'Savar', '2025-12-09', 'not_available', '2025-12-01 14:54:07'),
(6, 'Mehedi Hasan', '014582585', 'B+', 'Dhaka', 'Savar', '2025-12-09', 'not_available', '2025-12-01 14:54:14'),
(7, 'Md. Mehedi Hasan Hira', '0110231232', 'O+', 'Dhaka', 'savaar', '2025-12-04', 'not_available', '2025-12-01 16:23:05'),
(8, 'HealthMonitor', '1656', 'B-', 'cumilla', 'chanda', '2025-12-06', 'available', '2025-12-03 05:50:26'),
(9, 'Sadia Akter', '03233135', 'O+', 'Khulna', 'alomdanga', '2025-12-20', 'available', '2025-12-03 14:24:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `availability_logs`
--
ALTER TABLE `availability_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `availability_logs`
--
ALTER TABLE `availability_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `availability_logs`
--
ALTER TABLE `availability_logs`
  ADD CONSTRAINT `availability_logs_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
