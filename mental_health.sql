-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 04:47 PM
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
-- Database: `mental_health`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialist_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `specialist_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`) VALUES
(1, 4, 3, '2025-10-24', '14:00:00', 'Pending', NULL, '2025-10-24 06:02:21'),
(6, 3, 1, '2025-11-20', '03:00:00', 'Confirmed', NULL, '2025-11-06 07:15:27'),
(12, 3, 8, '2025-11-07', '10:00:00', 'Confirmed', NULL, '2025-11-06 12:38:18'),
(13, 3, 8, '2025-11-10', '09:00:00', 'Confirmed', NULL, '2025-11-06 14:13:30'),
(14, 3, 8, '2025-11-19', '10:00:00', 'Confirmed', NULL, '2025-11-06 14:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `orientation_0` text DEFAULT NULL,
  `orientation_1` text DEFAULT NULL,
  `orientation_2` text DEFAULT NULL,
  `orientation_3` text DEFAULT NULL,
  `orientation_4` text DEFAULT NULL,
  `emotions_0` text DEFAULT NULL,
  `emotions_1` text DEFAULT NULL,
  `emotions_2` text DEFAULT NULL,
  `emotions_3` text DEFAULT NULL,
  `emotions_4` text DEFAULT NULL,
  `memory_initial` text DEFAULT NULL,
  `memory_recall` text DEFAULT NULL,
  `thoughts_0` text DEFAULT NULL,
  `thoughts_1` text DEFAULT NULL,
  `thoughts_2` text DEFAULT NULL,
  `thoughts_3` text DEFAULT NULL,
  `decisions_0` text DEFAULT NULL,
  `decisions_1` text DEFAULT NULL,
  `decisions_2` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `user_id`, `score`, `summary`, `created_at`, `orientation_0`, `orientation_1`, `orientation_2`, `orientation_3`, `orientation_4`, `emotions_0`, `emotions_1`, `emotions_2`, `emotions_3`, `emotions_4`, `memory_initial`, `memory_recall`, `thoughts_0`, `thoughts_1`, `thoughts_2`, `thoughts_3`, `decisions_0`, `decisions_1`, `decisions_2`) VALUES
(6, 3, 3, 'Moderate symptoms', '2025-11-06 07:07:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 3, 0, 'Mild symptoms', '2025-11-06 07:08:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 3, 6, 'Severe symptoms', '2025-11-06 07:15:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 3, 6, 'Severe symptoms', '2025-11-06 07:15:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 3, 6, 'Severe symptoms', '2025-11-06 14:12:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 'Your new mental health assessment has been submitted.', 0, '2025-10-30 03:29:13'),
(2, 3, 'Your new mental health assessment has been submitted.', 0, '2025-11-03 07:17:23'),
(3, 3, 'Your new mental health assessment has been submitted.', 0, '2025-11-06 07:07:15'),
(4, 3, 'Your new mental health assessment has been submitted.', 0, '2025-11-06 07:08:11'),
(5, 3, 'Your new mental health assessment has been submitted.', 0, '2025-11-06 07:15:12'),
(6, 3, 'Your new mental health assessment has been submitted.', 0, '2025-11-06 07:15:12'),
(7, 3, 'Your new mental health assessment has been submitted.', 0, '2025-11-06 14:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`user_id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(3, 'yananhui093@gmail.com', 'd338f59886908b8f64b50caf76fa20b40ef597e15978903ac2a1e56e8d15433e', '2025-11-10 12:53:03', '2025-11-10 10:53:03'),
(5, 'dessvillaflor@gmail.com', '8dfc15c19012b2b18e1e706a765a4d2df8941202eeae57d2f6d8a5932d02c437', '2025-11-10 12:57:37', '2025-11-10 10:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `pre_assessments`
--

CREATE TABLE `pre_assessments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `q1` int(11) DEFAULT NULL,
  `q2` int(11) DEFAULT NULL,
  `q3` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Patient','Specialist') DEFAULT 'Patient',
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `age`, `gender`, `created_at`) VALUES
(1, 'Test User', 'test@example.com', '$2y$10$5xdOW4v13ZMpsN63cB5Tl.Cb7FgRrSqlSQh/75Z023RLsgHXGab1O', 'Patient', NULL, NULL, '2025-10-23 10:59:00'),
(3, 'Michael Dungog', 'yananhui093@gmail.com', '$2y$10$HY4IYAu5DshtoZocYk6rdu2W5teOztPEy6xJq6cdu.GbL4X.bJZva', 'Patient', NULL, 'Male', '2025-10-23 11:22:08'),
(4, 'Michael Dungog', 'mrdungog2024@plm.edu.ph', '$2y$10$stCBfqXW8h.SsyHPTwcVFuKTPfyQEHrc3r3YzRn7ZabsaJ0YYib1i', 'Patient', NULL, 'Male', '2025-10-24 06:01:04'),
(5, 'Dess Villaflor', 'dessvillaflor@gmail.com', '$2y$10$k5rOd/ZxNBSVl3bbrL3RFOSW9M.FrSP8MFE2CZp6T6nehqLX9LS5.', 'Patient', NULL, 'Female', '2025-10-30 03:31:45'),
(8, 'Dr. Santos', 'santos@gmail.com', 'password123', 'Specialist', NULL, NULL, '2025-11-06 12:32:59'),
(9, 'Dr. Reyes', 'reyes@gmai;.com', 'password123', 'Specialist', NULL, NULL, '2025-11-06 12:32:59'),
(10, 'Admin User', 'admin@example.com', 'admin123', '', NULL, NULL, '2025-11-07 05:30:37'),
(12, 'Admin User', 'admin2@example.com', '$2y$10$HBy8CEVX4bWPILoo.a5b3u9kgukdz5thuIHBF8Qmnt2oYJ/rayotq', '', NULL, 'Other', '2025-11-07 07:21:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `specialist_id` (`specialist_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `pre_assessments`
--
ALTER TABLE `pre_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pre_assessments`
--
ALTER TABLE `pre_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`specialist_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pre_assessments`
--
ALTER TABLE `pre_assessments`
  ADD CONSTRAINT `pre_assessments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
