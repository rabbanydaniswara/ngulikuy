-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 26, 2025 at 11:58 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ngulikuy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `jobId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workerId` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workerName` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jobType` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `customer` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customerPhone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customerEmail` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` int DEFAULT '0',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in-progress','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`jobId`, `workerId`, `workerName`, `jobType`, `startDate`, `endDate`, `customer`, `customerPhone`, `customerEmail`, `price`, `location`, `address`, `description`, `status`, `createdAt`, `updatedAt`) VALUES
('JOB001', 'KUL001', 'Rizkash', 'Other', '2025-10-27', '2025-10-29', 'Daniswara', '081234567890', 'daniswara@gmail.com', 300000, 'ren', 'ren', 'ren', 'completed', '2025-10-26 09:55:01', '2025-10-26 16:55:10'),
('JOB002', 'KUL003', 'Hadi Purnomo', 'Other', '2025-11-01', '2025-11-03', 'Daniswara', '081234567890', 'daniswara@gmail.com', 360000, 'ren', 'ren', 'ren', 'completed', '2025-10-26 10:01:34', '2025-10-26 17:01:42');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `jobId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workerId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customerId` int DEFAULT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `jobId`, `workerId`, `customerId`, `rating`, `comment`, `createdAt`) VALUES
(6, 'JOB001', 'KUL001', 6, 5, 'memuaskan\r\n', '2025-10-26 09:55:46'),
(7, 'JOB002', 'KUL003', 6, 5, 'sangat memuaskan', '2025-10-26 10:01:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `name`) VALUES
(1, 'admin', '$2y$10$oui3nmQUjDUxV4YRSitoeOgQVZCOzhzXbng7TTiwsUVYQgxzNBT1i', 'admin', 'Administrator'),
(2, 'user@gmail.com', '$2y$10$C2MCgSvxv9Ian.QWX9eu9.YimKPHatOmy/ExPEUuntm87tULhZcAu', 'customer', 'Customer User'),
(6, 'daniswara@gmail.com', '$2y$10$DGikOqv8868fnR3zBfcvFOo7LM/U.ahXjQL0CwUreIR1iDgEU1dXW', 'customer', 'Daniswara');

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills` json DEFAULT NULL,
  `status` enum('Available','Assigned','On Leave') COLLATE utf8mb4_unicode_ci DEFAULT 'Available',
  `rate` int DEFAULT '0',
  `experience` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` float DEFAULT '4',
  `completedJobs` int DEFAULT '0',
  `joinDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workers`
--

INSERT INTO `workers` (`id`, `name`, `email`, `phone`, `location`, `skills`, `status`, `rate`, `experience`, `description`, `photo`, `rating`, `completedJobs`, `joinDate`) VALUES
('KUL001', 'Rizkash', 'rizkash@gmail.com', '0827275584', 'Depok', '[\"Construction\", \"Moving\", \"Gardening\"]', 'Available', 100000, '1 Tahun', 'konstruksi dan renovasi rumah', 'uploads/workers/worker_68fdc838e441f_1761462328.png', 5, 0, '2025-10-18'),
('KUL002', 'Daniswara', 'daniswara@gmail.com', '08972724855', 'Depok', '[\"Construction\", \"Cleaning\", \"Painting\"]', 'Available', 150000, '1 Bulan', 'renovasi dan cat rumah.', 'uploads/workers/Screenshot 2025-07-12 102823.png', 4, 0, '2025-10-18'),
('KUL003', 'Hadi Purnomo', 'hadi@gmail.com', '08523345121', 'Gunung Sindur', '[\"Construction\", \"Gardening\", \"Electrical\"]', 'Available', 120000, '1 Bulan', 'Perbaikan dan Renovasi rumah.', 'uploads/workers/worker_68f34803209a1_1760774147.png', 5, 0, '2025-10-18'),
('KUL005', 'Fathan Antony', 'fathan@gmail.com', '0877223345', 'Bintaro', '[\"Construction\", \"Moving\", \"Cleaning\", \"Gardening\"]', 'Available', 200000, '1 Tahun', 'Renovasi dan Pembuatan rumah.', 'uploads/workers/worker_68fdad77bd466_1761455479.png', 4, 0, '2025-10-26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`jobId`),
  ADD KEY `workerId` (`workerId`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobId` (`jobId`),
  ADD KEY `workerId` (`workerId`),
  ADD KEY `customerId` (`customerId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`workerId`) REFERENCES `workers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`jobId`) REFERENCES `jobs` (`jobId`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`workerId`) REFERENCES `workers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`customerId`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
