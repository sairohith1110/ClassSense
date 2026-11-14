-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2026 at 04:04 PM
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
-- Database: `classsense`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `class_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `teacher_id`, `class_name`, `subject`, `class_code`, `created_at`, `day_of_week`, `start_time`) VALUES
(1, 1, 'CSE-A', 'Data Structures', 'A65602', '2026-09-02 06:08:06', NULL, NULL),
(2, 1, 'CSE-A', 'Data Structures', '10740B', '2026-09-02 08:33:42', NULL, NULL),
(3, 3, 'CSE-A', 'Data Structures', '90406A', '2026-09-02 09:12:53', NULL, NULL),
(4, 1, 'CSE-A', 'Data Structures', 'EAA596', '2026-09-03 06:17:59', NULL, NULL),
(5, 1, 'CSE-A', 'Data Structures', 'C0ED3E', '2026-09-03 06:59:14', 'Monday', '12:26:00'),
(6, 1, 'CSE-A', 'Data Structures', 'D4A9BE', '2026-09-03 06:59:26', 'Monday', '12:26:00'),
(7, 1, 'CSE-B', 'WEB development', '044510', '2026-09-04 06:11:36', 'Friday', '12:00:00'),
(8, 8, 'datastructures', 'linked list', 'BF87B1', '2026-11-12 15:43:27', 'Monday', '09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `class_id`, `created_at`) VALUES
(2, 1, 1, '2026-09-02 06:44:47'),
(3, 1, 1, '2026-09-02 08:30:06'),
(4, 1, 1, '2026-09-02 08:30:42'),
(5, 1, 1, '2026-09-02 08:31:07'),
(6, 3, 1, '2026-09-02 09:14:33'),
(7, 9, 1, '2026-11-13 04:48:32');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `class_id`, `teacher_id`, `title`, `content`, `created_at`) VALUES
(1, 8, 8, 'datastructures', 'today i will explain about the topic linked list.', '2026-11-13 04:26:54'),
(2, 8, 8, 'datastructures', 'today i will explain about the topic linked list.', '2026-11-13 04:27:31'),
(3, 8, 8, 'datastructures', 'today i will explain about the topic linked list.', '2026-11-13 04:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `understanding_signals`
--

CREATE TABLE `understanding_signals` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `signal_type` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `topic` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `understanding_signals`
--

INSERT INTO `understanding_signals` (`id`, `student_id`, `class_id`, `signal_type`, `created_at`, `topic`) VALUES
(1, 1, 1, 'understand', '2026-09-04 12:00:58', 'Linkedlist'),
(2, 1, 1, 'confused', '2026-09-02 09:32:02', NULL),
(3, 1, 1, 'need_example', '2026-09-02 09:32:03', NULL),
(4, 1, 1, 'too_fast', '2026-09-02 09:32:04', NULL),
(5, 9, 1, 'understand', '2026-11-13 05:00:10', 'Linkedlist');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'rohith', 'sairohith1110@gmail.com', '$2y$10$L8zOJ5EKAN3dmznPfyxhiuHzHK..uhgrmz73jnU5v.T.Fv4gx9M8e', 'teacher', '2026-09-01 17:31:44'),
(3, 'sai', 'reddysairohith278@gmail.com', '$2y$10$9u1jdo/hFpwrJHCzmQzkY.ZxJZf6TATRGNDsez35C/1jlnQxM8TeW', 'student', '2026-09-02 09:00:11'),
(4, 'rohith reddy', 'sairohith1110@gmail.com', '$2y$10$lPwZk1YHqfAe/8YshZsu4u2Np9gpAC9LczxAhrhdzr1J4kAv47vGK', 'student', '2026-11-11 05:55:28'),
(5, 'rohtih reddy', 'sairohith1110@gmail.com', '$2y$10$4J1IV0Ayk2Ak2IgCys8IAOcsWWLL94zUudgTNJFE3Yuu1UHb5nWv2', 'student', '2026-11-11 05:59:23'),
(6, 'rohith reddy', 'sairohith1110@gmail.com', '$2y$10$uHzrJ5aYwT3zj.91fS6bk.IgT/4gHvo2brHH8vG1Bvm0OGAXdVXBC', 'student', '2026-11-11 06:00:07'),
(7, 'sai rohith reddy', 'sairohith1110@gmail.com', '$2y$10$2KUQg6eUuLWslwIlcTf3t..SurPsLhBtnKg6.k2yrso.H5WdMiYoy', 'student', '2026-11-11 06:04:58'),
(8, 'sai1', 'sairohith1110@gmail.com', '$2y$10$ik2HgxDqzGYwXxOjrjfdM.LvOwolaeUAjhJBx6Sc3rkebo9FbGRVy', 'student', '2026-11-11 06:13:19'),
(9, 'sai1', 'sairohith1110@gmail.com', '$2y$10$4mg82ODAIcOkRV4mv8pL1.APRgLPUxerd7gxgJDT19zvIN0rNu8LS', 'teacher', '2026-11-13 04:44:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_code` (`class_code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `understanding_signals`
--
ALTER TABLE `understanding_signals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `understanding_signals`
--
ALTER TABLE `understanding_signals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
