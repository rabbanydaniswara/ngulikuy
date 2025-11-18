-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 15, 2025 at 06:27 AM
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
('JOB002', NULL, 'Arvian Syidq', 'Construction', '2025-10-31', '2025-11-08', 'Daniswara', '08972724855', 'daniswara@gmail.com', 810000, 'Jakarta Utara, Priok.', 'Jakarta Utara, Priok.', 'Pembuatan kamar/ruangan gaming.', 'completed', '2025-10-30 14:17:50', '2025-10-30 21:18:19'),
('JOB003', NULL, 'Arvian Syidq', 'Construction', '2025-11-01', '2025-11-03', 'Daniswara', '08972724855', 'daniswara@gmail.com', 270000, 'Ciputat', 'Ciputat', 'pembuatan ruangan gaming', 'completed', '2025-10-31 06:23:59', '2025-10-31 13:25:14'),
('JOB004', NULL, 'Arvian Syidq', 'Construction', '2025-11-02', '2025-11-08', 'Daniswara', '08972724855', 'daniswara@gmail.com', 630000, 'Tangerang Selatan Ciputat, Pisangan.', 'Tangerang Selatan Ciputat, Pisangan.', 'Pembuatan rumah modern minimalis', 'completed', '2025-11-01 10:34:28', '2025-11-01 17:35:12'),
('JOB005', NULL, 'Arvian Syidq', 'Construction', '2025-11-02', '2025-11-08', 'Daniswara', '08972724855', 'daniswara@gmail.com', 630000, 'Jakarta Barat', 'Jakarta Barat', 'renovas', 'pending', '2025-11-01 10:38:41', '2025-11-01 20:57:29');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `action` varchar(100) NOT NULL,
  `attempts` int DEFAULT '1',
  `last_attempt` datetime DEFAULT CURRENT_TIMESTAMP,
  `blocked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer','worker') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path ke foto profil user',
  `worker_profile_id` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `name`, `phone`, `photo`, `worker_profile_id`) VALUES
(1, 'admin', '$2y$10$oui3nmQUjDUxV4YRSitoeOgQVZCOzhzXbng7TTiwsUVYQgxzNBT1i', 'admin', 'Administrator', NULL, NULL, NULL),
(2, 'user@gmail.com', '$2y$10$C2MCgSvxv9Ian.QWX9eu9.YimKPHatOmy/ExPEUuntm87tULhZcAu', 'customer', 'Customer User', '081250800137', NULL, NULL),
(6, 'daniswara@gmail.com', '$2y$10$DGikOqv8868fnR3zBfcvFOo7LM/U.ahXjQL0CwUreIR1iDgEU1dXW', 'customer', 'Daniswara Rabbany', '08972724855', 'uploads/avatars/avatar_e84727a4057065ebc0f087856af247a8.png', NULL),
(8, 'udin@gmail.com', '$2y$10$E8MuxG1iDNS628qISWvRO.gK9iCnzWiGqMzDsEvaJQ9DT3l83NsGW', 'worker', 'Udin Sludin', '08972724877', NULL, 'KUL009'),
(9, 'arvian@gmail.com', '$2y$10$F3gs7wWnuqHhgkAfy8JHguJV4kqpOCcWV4FE49Y5E08.64CjxLjEi', 'worker', 'Arvian Syidq', '08973735922', NULL, 'KUL010');

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
('KUL002', 'Daniswara', 'daniswara@gmail.com', '08972724855', 'Depok', '[\"Construction\", \"Cleaning\", \"Painting\"]', 'Available', 150000, '1 Bulan', 'renovasi dan cat rumah.', 'uploads/workers/worker_690262c3057e0_1761764035.png', 4, 0, '2025-10-18'),
('KUL003', 'Hadi Purnomo', 'hadi@gmail.com', '08523345121', 'Gunung Sindur', '[\"Construction\", \"Gardening\", \"Electrical\"]', 'Available', 120000, '1 Bulan', 'Perbaikan dan Renovasi rumah.', 'uploads/workers/worker_69026337d8281_1761764151.png', 5, 0, '2025-10-18'),
('KUL005', 'Fathan Antony', 'fathan@gmail.com', '0877223345', 'Bintaro', '[\"Construction\", \"Moving\", \"Cleaning\", \"Gardening\"]', 'Available', 200000, '1 Tahun', 'Renovasi dan Pembuatan rumah.', 'uploads/workers/worker_6902633fac547_1761764159.png', 5, 0, '2025-10-26'),
('KUL006', 'George Floyd', 'floyd@gmail.com', '089223344556', 'Disitu', '[\"Moving\", \"Cleaning\", \"Plumbing\", \"Painting\"]', 'Available', 50000, '3 Tahun', 'Breathtaking', 'https://upload.wikimedia.org/wikipedia/en/9/9c/George_Floyd.png', 5, 0, '2025-10-29'),
('KUL007', 'Firman Djibran', 'firman@gmail.com', '087373556679', 'Jakarta Barat', '[\"Construction\", \"Moving\", \"Cleaning\"]', 'Available', 75000, '2 Tahun', 'Pengalaman yang sangat mumpuni dibidang Konstruksi, renovasi, dan perbaikan rumah.', 'uploads/workers/worker_414777040bccfeb1ae6626b185c24ddc.png', 4, 0, '2025-10-30'),
('KUL009', 'Udin Sludin', 'udin@gmail.com', '08972724877', 'Ciputat', '[\"Construction\", \"Moving\", \"Cleaning\", \"Electrical\"]', 'Available', 110000, '3 Tahun', 'Konstruksi rumah, renovasi, dan perbaikan rumah.', 'uploads/workers/worker_10ac8fa252dfa5b36fad4a2b24110c9a.jpg', 4, 0, '2025-11-09'),
('KUL010', 'Arvian Syidq', 'arvian@gmail.com', '08973735922', 'Tanjung Priok', '[\"Construction\", \"Moving\", \"Cleaning\"]', 'Available', 110000, '3 Tahun', 'Pembuatan/Pembangunan Rumah, Renovasi, perbaikan.', 'uploads/workers/worker_3bc49e9bba7eee59eddf570125a115b6.png', 4, 0, '2025-11-15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`jobId`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_customer_email` (`customerEmail`),
  ADD KEY `idx_created` (`createdAt`),
  ADD KEY `idx_worker_id` (`workerId`),
  ADD KEY `idx_created_at` (`createdAt`),
  ADD KEY `idx_customer_status` (`customerEmail`,`status`),
  ADD KEY `idx_worker_status` (`workerId`,`status`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_action` (`identifier`,`action`),
  ADD KEY `idx_blocked_until` (`blocked_until`),
  ADD KEY `idx_last_attempt` (`last_attempt`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_worker` (`workerId`),
  ADD KEY `idx_created` (`createdAt`),
  ADD KEY `idx_worker_id` (`workerId`),
  ADD KEY `idx_job_id` (`jobId`),
  ADD KEY `idx_customer_id` (`customerId`),
  ADD KEY `idx_created_at` (`createdAt`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `worker_profile_id_unique` (`worker_profile_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_worker_profile` (`worker_profile_id`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_status_rating` (`status`,`rating`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
