-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: sdb-89.hosting.stackcp.net
-- Generation Time: Sep 14, 2025 at 06:30 PM
-- Server version: 10.11.14-MariaDB-log
-- PHP Version: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bannerbox_API_v2-3531313702d5`
--
CREATE DATABASE IF NOT EXISTS `bannerbox_API_v2-3531313702d5` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `bannerbox_API_v2-3531313702d5`;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#000000',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `icon`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
(26, 'World enviroment Day', 'World enviroment Day', '', '#3B82F6', 1, '2025-08-27 16:05:05', NULL),
(25, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', '', '#3B82F6', 1, '2025-08-27 16:04:47', NULL),
(24, 'Mothers Day', 'Mothers Day', '', '#3B82F6', 1, '2025-08-27 16:04:23', NULL),
(14, 'Ganesh chaturthi', 'Ganesh chaturthi', 'BsBriefcase', '#ffc800', 1, '2025-08-18 14:36:29', NULL),
(17, 'Gandhi Jayanti', 'Gandhi Jayanti', '', '#3B82F6', 1, '2025-08-27 16:02:16', NULL),
(16, 'Teacher\'s Day', 'Teacher\'s Day', 'BsClock', '#3B82F6', 1, '2025-08-18 15:16:16', '2025-08-27 16:01:46'),
(18, 'Father\'s Day', 'Father\'s Day', '', '#3B82F6', 1, '2025-08-27 16:02:34', NULL),
(19, 'Yoga Day', 'Yoga Day', '', '#3B82F6', 1, '2025-08-27 16:02:50', NULL),
(20, 'Javaharlal Nehrus Bday ', 'Javaharlal Nehrus Bday ', '', '#3B82F6', 1, '2025-08-27 16:03:14', NULL),
(21, 'National Farmers Day (Kisan DIwas)', 'National Farmers Day (Kisan DIwas)', '', '#3B82F6', 1, '2025-08-27 16:03:36', NULL),
(22, 'Martyrdom Of Mahatma Gandhi', 'Martyrdom Of Mahatma Gandhi', '', '#3B82F6', 1, '2025-08-27 16:03:52', NULL),
(23, 'Women\'s Day', 'Women\'s Day', '', '#3B82F6', 1, '2025-08-27 16:04:07', NULL),
(27, 'Indian Air Force Day', 'Indian Air Force Day', '', '#3B82F6', 1, '2025-08-27 16:05:40', NULL),
(28, 'International Youth Day', 'International Youth Day', '', '#3B82F6', 1, '2025-08-27 16:06:12', NULL),
(29, 'Navratri Starting', 'Navratri Starting', '', '#3B82F6', 1, '2025-08-27 16:06:25', NULL),
(30, 'Dashehra', 'Dashehra', '', '#3B82F6', 1, '2025-08-27 16:06:40', NULL),
(31, 'Sardar Patel Jayanti (National Unity Day)', 'Sardar Patel Jayanti (National Unity Day)', '', '#3B82F6', 1, '2025-08-27 16:06:58', NULL),
(32, 'Dhanteras', 'Dhanteras', '', '#3B82F6', 1, '2025-08-27 16:07:13', NULL),
(33, 'Diwali', 'Diwali', '', '#3B82F6', 1, '2025-08-27 16:07:30', NULL),
(34, 'New Year', 'New Year', '', '#3B82F6', 1, '2025-08-27 16:07:47', NULL),
(35, 'Bhai Dooj', 'Bhai Dooj', '', '#3B82F6', 1, '2025-08-27 16:08:03', NULL),
(36, 'World Aids Day', 'World Aids Day', '', '#3B82F6', 1, '2025-08-27 16:08:16', NULL),
(37, 'Indian Navy Day', 'Indian Navy Day', '', '#3B82F6', 1, '2025-08-27 16:08:29', NULL),
(38, 'Christmas', 'Christmas', '', '#3B82F6', 1, '2025-08-27 16:08:52', NULL),
(39, 'Dhuleti', 'Dhuleti', '', '#3B82F6', 1, '2025-08-27 16:09:02', NULL),
(40, 'Friendship Day', 'Friendship Day', '', '#3B82F6', 1, '2025-08-27 16:09:48', NULL),
(41, 'Holi', 'Holi', '', '#3B82F6', 1, '2025-08-27 16:10:00', NULL),
(42, 'National Education Day', 'National Education Day', '', '#3B82F6', 1, '2025-08-27 16:10:15', NULL),
(43, 'World Tourism Day', 'World Tourism Day', '', '#3B82F6', 1, '2025-08-27 16:10:34', NULL),
(44, 'APJ Abdul Kalam Bday', 'APJ Abdul Kalam Bday', '', '#3B82F6', 1, '2025-08-27 16:10:55', NULL),
(45, 'Dr. Babasaheb Ambedkar Jayanti', 'Dr. Baba Saheb Ambedkar Jayanti', '', '#0000ff', 1, '2025-08-27 16:11:10', '2025-09-08 13:11:45'),
(46, 'Water Day', 'Water Day', '', '#3B82F6', 1, '2025-08-27 16:11:21', NULL),
(47, 'World Population Day', 'World Population Day', '', '#3B82F6', 1, '2025-08-27 16:11:31', NULL),
(48, 'Hindi Diwas', 'Hindi Diwas', '', '#3B82F6', 1, '2025-08-27 16:11:55', NULL),
(49, 'National Youth Day', 'National Youth Day', '', '#3B82F6', 1, '2025-08-27 16:12:19', NULL),
(50, 'Valentine\'s Day', 'Valentine\'s Day', '', '#3B82F6', 1, '2025-08-27 16:12:37', NULL),
(51, 'shree datta jayanti', 'shree datta jayanti', '', '#3B82F6', 1, '2025-08-27 16:12:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posters`
--

CREATE TABLE `posters` (
  `poster_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `tags` text DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posters`
--

INSERT INTO `posters` (`poster_id`, `title`, `description`, `image_url`, `thumbnail_url`, `category_id`, `tags`, `is_premium`, `is_featured`, `download_count`, `view_count`, `created_at`, `updated_at`) VALUES
(24, 'asdf', 'asdf', 'poster_1755239886_689ed5ce00cef.png', 'thumb_poster_1755239886_689ed5ce00cef.png', 2, 'asdf,asfd', 0, 0, 0, 0, '2025-08-15 06:38:06', NULL),
(25, 'Updated Poster Title', 'Updated description', 'poster_1755240348_689ed79ca2497.png', 'thumb_poster_1755240348_689ed79ca2497.png', 2, 'updated,poster,design', 0, 0, 0, 0, '2025-08-15 06:45:48', '2025-08-16 19:11:38'),
(26, 'Makar sankranti ', '', 'poster_1755240628_689ed8b41c346.png', 'thumb_poster_1755240628_689ed8b41c346.png', 3, 'sankranti , makar sankranti', 0, 0, 0, 0, '2025-08-15 06:50:28', NULL),
(27, 'Makar sankranti', 'Makar sankranti', 'poster_1755240812_689ed96c42533.png', 'thumb_poster_1755240812_689ed96c42533.png', 2, 'Makar sankranti ,sankranti', 0, 0, 0, 1, '2025-08-15 06:53:32', '2025-08-15 13:34:00'),
(28, 'Makar sankranti', 'Makar sankranti', 'poster_1755240904_689ed9c8b4471.png', 'thumb_poster_1755240904_689ed9c8b4471.png', 2, 'Makar sankranti , sankranti', 0, 0, 0, 0, '2025-08-15 06:55:05', NULL),
(29, 'Makar sankranti', 'Makar sankranti', 'poster_1755240932_689ed9e4a585c.png', 'thumb_poster_1755240932_689ed9e4a585c.png', 2, 'Makar sankranti', 0, 0, 0, 5, '2025-08-15 06:55:33', '2025-08-16 07:29:59'),
(30, 'Makar sankranti', 'Makar sankranti', 'poster_1755240957_689ed9fd175ce.png', 'thumb_poster_1755240957_689ed9fd175ce.png', 2, '', 0, 0, 0, 4, '2025-08-15 06:55:57', '2025-08-15 14:27:43'),
(31, 'Makar sankranti', 'Makar sankranti', 'poster_1755240992_689eda201510f.png', 'thumb_poster_1755240992_689eda201510f.png', 2, 'Makar sankranti', 0, 0, 0, 3, '2025-08-15 06:56:32', '2025-08-16 17:18:15'),
(32, 'Makar sankranti', 'Makar sankranti', 'poster_1755241021_689eda3d9510a.png', 'thumb_poster_1755241021_689eda3d9510a.png', 2, 'Makar sankranti', 0, 0, 0, 6, '2025-08-15 06:57:01', '2025-08-16 07:31:02'),
(33, 'Makar sankranti', 'Makar sankranti', 'poster_1755241056_689eda60dc52c.png', 'thumb_poster_1755241056_689eda60dc52c.png', 2, 'Makar sankranti', 0, 0, 0, 3, '2025-08-15 06:57:37', '2025-08-16 18:51:58'),
(34, 'Makar sankranti', 'Makar sankranti', 'poster_1755241100_689eda8c484c6.png', 'thumb_poster_1755241100_689eda8c484c6.png', 2, 'Makar sankranti', 0, 0, 0, 10, '2025-08-15 06:58:20', '2025-08-16 05:49:31'),
(35, 'Ganesh chaturthis', 'Ganesh Chaturthi', 'poster_1755524589_68a32ded344e7.png', 'thumb_poster_1755524589_68a32ded344e7.png', 14, 'Ganesh Chaturthi , गणेश चतुर्थी  ', 0, 0, 0, 10, '2025-08-18 14:43:09', '2025-09-06 13:10:06'),
(36, 'गणेश चतुर्थी', 'Ganesh Chaturthi', 'poster_1755524673_68a32e41e9eed.png', 'thumb_poster_1755524673_68a32e41e9eed.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 3, '2025-08-18 14:44:34', '2025-09-08 11:38:45'),
(37, 'Ganesh Chaturthi', 'Ganesh Chaturthi', 'poster_1755524704_68a32e6035bbc.png', 'thumb_poster_1755524704_68a32e6035bbc.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 0, '2025-08-18 14:45:04', NULL),
(38, 'Ganesh Chaturthi', 'Ganesh Chaturthi', 'poster_1755524736_68a32e80ab1e8.png', 'thumb_poster_1755524736_68a32e80ab1e8.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 8, '2025-08-18 14:45:37', '2025-09-12 12:01:45'),
(39, 'Ganesh Chaturthi', 'Ganesh Chaturthi', 'poster_1755524769_68a32ea15d997.png', 'thumb_poster_1755524769_68a32ea15d997.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 8, '2025-08-18 14:46:09', '2025-09-09 14:25:17'),
(40, 'Ganesh Chaturthi', 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 'poster_1755524793_68a32eb967825.png', 'thumb_poster_1755524793_68a32eb967825.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 0, '2025-08-18 14:46:33', NULL),
(41, 'गणेश चतुर्थी ', ' गणेश चतुर्थी ', 'poster_1755524829_68a32edddb7ca.png', 'thumb_poster_1755524829_68a32edddb7ca.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 4, '2025-08-18 14:47:10', '2025-09-07 20:23:22'),
(42, 'गणेश चतुर्थी', 'गणेश चतुर्थी', 'poster_1755524867_68a32f036d929.png', 'thumb_poster_1755524867_68a32f036d929.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 3, '2025-08-18 14:47:47', '2025-09-08 11:38:38'),
(43, 'Ganesh Chaturthi ', 'गणेश चतुर्थी ', 'poster_1755524901_68a32f25cd475.png', 'thumb_poster_1755524901_68a32f25cd475.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 0, '2025-08-18 14:48:21', NULL),
(44, 'Ganesh Chaturthi', 'Ganesh Chaturthi', 'poster_1755524931_68a32f4332799.png', 'thumb_poster_1755524931_68a32f4332799.png', 14, 'गणेश , गणेश चतुर्थी , Ganesh, Ganesh Chaturthi ', 0, 0, 0, 0, '2025-08-18 14:48:51', NULL),
(45, ' Teacher\'s Day', ' Teacher\'s Day', 'poster_1756307763_68af21336d8d6.png', 'thumb_poster_1756307763_68af21336d8d6.png', 16, 'शिक्षक दिन , Teacher\'s Day , 	', 0, 0, 0, 12, '2025-08-27 16:16:03', '2025-08-28 19:07:08'),
(46, ' Teacher\'s Day', ' Teacher\'s Day', 'poster_1756307803_68af215be5804.png', 'thumb_poster_1756307803_68af215be5804.png', 16, 'शिक्षक दिन , Teacher\'s Day , 	', 0, 0, 0, 0, '2025-08-27 16:16:44', NULL),
(47, 'शिक्षक दिन ', 'शिक्षक दिन , Teacher\'s Day , 	', 'poster_1756307839_68af217f355a3.png', 'thumb_poster_1756307839_68af217f355a3.png', 16, 'शिक्षक दिन , Teacher\'s Day ', 0, 0, 0, 4, '2025-08-27 16:17:19', '2025-09-03 09:15:24'),
(48, 'शिक्षक दिन , Teacher\'s Day , 	', 'शिक्षक दिन , Teacher\'s Day , 	', 'poster_1756307871_68af219f1a767.png', 'thumb_poster_1756307871_68af219f1a767.png', 16, 'शिक्षक दिन , Teacher\'s Day , 	', 0, 0, 0, 0, '2025-08-27 16:17:51', NULL),
(49, 'शिक्षक दिन , Teacher\'s Day , 	', 'शिक्षक दिन , Teacher\'s Day , 	', 'poster_1756307908_68af21c4a1476.png', 'thumb_poster_1756307908_68af21c4a1476.png', 16, 'शिक्षक दिन , Teacher\'s Day ', 0, 0, 0, 2, '2025-08-27 16:18:28', '2025-09-01 18:21:42'),
(51, 'शिक्षक दिन , Teacher\'s Day , 	', 'शिक्षक दिन , Teacher\'s Day , 	', 'poster_1756307988_68af22140b8fd.png', 'thumb_poster_1756307988_68af22140b8fd.png', 16, 'शिक्षक दिन , Teacher\'s Day , 	', 0, 0, 0, 0, '2025-08-27 16:19:48', NULL),
(52, 'शिक्षक दिन , Teacher\'s Day , 	', 'शिक्षक दिन , Teacher\'s Day , 	', 'poster_1756308018_68af2232ed51a.png', 'thumb_poster_1756308018_68af2232ed51a.png', 16, 'शिक्षक दिन , Teacher\'s Day , 	', 0, 0, 0, 0, '2025-08-27 16:20:19', NULL),
(53, 'शिक्षक दिन , Teacher\'s Day , 	', 'शिक्षक दिन , Teacher\'s Day , 	', 'poster_1756308048_68af225052d16.png', 'thumb_poster_1756308048_68af225052d16.png', 16, 'शिक्षक दिन , Teacher\'s Day , 	', 0, 0, 0, 0, '2025-08-27 16:20:48', NULL),
(54, 'Gandhi Jayanti', 'Gandhi Jayanti', 'poster_1756308159_68af22bf409d3.png', 'thumb_poster_1756308159_68af22bf409d3.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 1, '2025-08-27 16:22:39', '2025-09-13 12:03:31'),
(55, 'Gandhi Jayanti', 'Gandhi Jayanti', 'poster_1756308197_68af22e56da80.png', 'thumb_poster_1756308197_68af22e56da80.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 0, '2025-08-27 16:23:17', NULL),
(56, 'Gandhi Jayanti', 'Gandhi Jayanti', 'poster_1756308234_68af230a5a378.png', 'thumb_poster_1756308234_68af230a5a378.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 0, '2025-08-27 16:23:54', NULL),
(58, 'Gandhi Jayanti , गांधी जयंती', 'Gandhi Jayanti , गांधी जयंती', 'poster_1756308300_68af234c44ad8.png', 'thumb_poster_1756308300_68af234c44ad8.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 1, '2025-08-27 16:25:00', '2025-09-13 12:03:44'),
(59, 'Gandhi Jayanti , गांधी जयंती', 'Gandhi Jayanti , गांधी जयंती', 'poster_1756308330_68af236a0a32f.png', 'thumb_poster_1756308330_68af236a0a32f.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 0, '2025-08-27 16:25:30', NULL),
(60, 'Gandhi Jayanti , गांधी जयंती', 'Gandhi Jayanti , गांधी जयंती', 'poster_1756308379_68af239b1401e.png', 'thumb_poster_1756308379_68af239b1401e.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 3, '2025-08-27 16:26:19', '2025-09-08 11:52:02'),
(61, 'Gandhi Jayanti , गांधी जयंती', 'Gandhi Jayanti , गांधी जयंती', 'poster_1756308427_68af23cb2abd8.png', 'thumb_poster_1756308427_68af23cb2abd8.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 5, '2025-08-27 16:27:07', '2025-09-13 12:09:44'),
(62, 'Gandhi Jayanti , गांधी जयंती', 'Gandhi Jayanti , गांधी जयंती', 'poster_1756308452_68af23e4d6b38.png', 'thumb_poster_1756308452_68af23e4d6b38.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 4, '2025-08-27 16:27:33', '2025-09-02 14:51:49'),
(63, 'Gandhi Jayanti', 'Gandhi Jayanti ', 'poster_1756308487_68af240769fd7.png', 'thumb_poster_1756308487_68af240769fd7.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 0, '2025-08-27 16:28:07', NULL),
(64, 'Gandhi Jayanti , गांधी जयंती', 'Gandhi Jayanti , गांधी जयंती', 'poster_1756308518_68af2426ea54f.png', 'thumb_poster_1756308518_68af2426ea54f.png', 17, 'Gandhi Jayanti , गांधी जयंती', 0, 0, 0, 0, '2025-08-27 16:28:39', NULL),
(65, 'Father\'s DayFather\'s Day', 'Father\'s Day', 'poster_1756308624_68af24906b1eb.png', 'thumb_poster_1756308624_68af24906b1eb.png', 18, 'Father\'s Day', 0, 0, 0, 1, '2025-08-27 16:30:24', '2025-09-12 11:53:19'),
(66, 'Father\'s Day', 'Father\'s Day', 'poster_1756308703_68af24df0d24d.png', 'thumb_poster_1756308703_68af24df0d24d.png', 18, 'Father\'s Day', 0, 0, 0, 27, '2025-08-27 16:31:43', '2025-09-04 17:22:37'),
(67, 'Father\'s Day', 'Father\'s Day', 'poster_1756308747_68af250b6c2bc.png', 'thumb_poster_1756308747_68af250b6c2bc.png', 18, 'Father\'s Day', 0, 0, 0, 0, '2025-08-27 16:32:27', NULL),
(68, 'Father\'s Day', 'Father\'s Day', 'poster_1756308797_68af253d7b7e2.png', 'thumb_poster_1756308797_68af253d7b7e2.png', 18, 'Father\'s Day', 0, 0, 0, 0, '2025-08-27 16:33:17', NULL),
(69, 'Father\'s Day', 'Father\'s Day\r\n', 'poster_1756308829_68af255d64690.png', 'thumb_poster_1756308829_68af255d64690.png', 18, 'Father\'s Day', 0, 0, 0, 0, '2025-08-27 16:33:49', NULL),
(70, 'Father\'s Day', 'Father\'s Day', 'poster_1756308858_68af257a55d6c.png', 'thumb_poster_1756308858_68af257a55d6c.png', 18, 'Father\'s Day', 0, 0, 0, 1, '2025-08-27 16:34:18', '2025-09-06 12:08:26'),
(71, 'Father\'s Day', 'Father\'s Day', 'poster_1756308907_68af25abbe638.png', 'thumb_poster_1756308907_68af25abbe638.png', 18, 'Father\'s Day', 0, 0, 0, 0, '2025-08-27 16:35:08', NULL),
(72, 'Father\'s Day', 'Father\'s Day', 'poster_1756308933_68af25c59db14.png', 'thumb_poster_1756308933_68af25c59db14.png', 18, 'Father\'s Day', 0, 0, 0, 0, '2025-08-27 16:35:33', NULL),
(73, 'Father\'s Day', 'Father\'s Day', 'poster_1756308974_68af25ee9e0d0.png', 'thumb_poster_1756308974_68af25ee9e0d0.png', 18, 'Father\'s Day', 0, 0, 0, 0, '2025-08-27 16:36:14', NULL),
(74, 'Father\'s Day', 'Father\'s Day', 'poster_1756308996_68af2604644c6.png', 'thumb_poster_1756308996_68af2604644c6.png', 18, 'Father\'s Day', 0, 0, 0, 1, '2025-08-27 16:36:36', '2025-08-27 18:41:57'),
(75, 'Yoga Day ', 'Yoga Day , योग दिन', 'poster_1756309139_68af2693df1b2.png', 'thumb_poster_1756309139_68af2693df1b2.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 4, '2025-08-27 16:39:00', '2025-09-13 12:50:46'),
(76, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309165_68af26ad97eff.png', 'thumb_poster_1756309165_68af26ad97eff.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:39:25', NULL),
(77, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309184_68af26c0bdd54.png', 'thumb_poster_1756309184_68af26c0bdd54.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:39:44', NULL),
(78, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309208_68af26d8a8e0f.png', 'thumb_poster_1756309208_68af26d8a8e0f.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 6, '2025-08-27 16:40:08', '2025-09-08 13:27:33'),
(79, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309235_68af26f32fd67.png', 'thumb_poster_1756309235_68af26f32fd67.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:40:35', NULL),
(80, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309265_68af27112f822.png', 'thumb_poster_1756309265_68af27112f822.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:41:05', NULL),
(81, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309283_68af2723c65bf.png', 'thumb_poster_1756309283_68af2723c65bf.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:41:23', NULL),
(82, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309305_68af27391b6e9.png', 'thumb_poster_1756309305_68af27391b6e9.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 3, '2025-08-27 16:41:45', '2025-09-12 12:59:50'),
(83, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309331_68af27537223f.png', 'thumb_poster_1756309331_68af27537223f.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:42:11', NULL),
(84, 'Yoga Day , योग दिन', 'Yoga Day , योग दिन', 'poster_1756309365_68af2775b6935.png', 'thumb_poster_1756309365_68af2775b6935.png', 19, 'Yoga Day , योग दिन', 0, 0, 0, 0, '2025-08-27 16:42:46', NULL),
(85, 'National Farmers Day (किसान दिवस)', 'National Farmers Day (किसान दिवस)', 'poster_1756397390_68b07f4e92307.png', 'thumb_poster_1756397390_68b07f4e92307.png', 21, 'National Farmers Day (किसान दिवस)', 0, 0, 0, 0, '2025-08-28 17:09:50', NULL),
(86, 'National Farmers Day (किसान दिवस)', 'National Farmers Day (किसान दिवस)', 'poster_1756397475_68b07fa325b31.png', 'thumb_poster_1756397475_68b07fa325b31.png', 21, 'National Farmers Day (किसान दिवस)', 0, 0, 0, 0, '2025-08-28 17:11:15', NULL),
(87, 'National Farmers Day ', 'National Farmers Day , किसान दिवस', 'poster_1756397520_68b07fd06f6e7.png', 'thumb_poster_1756397520_68b07fd06f6e7.png', 21, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:12:00', NULL),
(88, 'National Farmers Day ', 'National Farmers Day ', 'poster_1756397566_68b07ffe8ebee.png', 'thumb_poster_1756397566_68b07ffe8ebee.png', 21, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:12:46', NULL),
(89, 'National Farmers Day , किसान दिवस', 'National Farmers Day , किसान दिवस', 'poster_1756397620_68b08034c1b69.png', 'thumb_poster_1756397620_68b08034c1b69.png', 21, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:13:41', NULL),
(90, 'National Farmers Day', 'National Farmers Day', 'poster_1756397664_68b08060bd007.png', 'thumb_poster_1756397664_68b08060bd007.png', 42, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:14:24', NULL),
(91, 'National Farmers Day', 'National Farmers Day', 'poster_1756397699_68b080838a76f.png', 'thumb_poster_1756397699_68b080838a76f.png', 21, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:14:59', NULL),
(92, 'National Farmers Day ', 'National Farmers Day , किसान दिवस', 'poster_1756397738_68b080aadd904.png', 'thumb_poster_1756397738_68b080aadd904.png', 21, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:15:39', NULL),
(93, 'National Farmers Day', 'National Farmers Day ', 'poster_1756397806_68b080ee7da02.png', 'thumb_poster_1756397806_68b080ee7da02.png', 21, 'National Farmers Day , किसान दिवस', 0, 0, 0, 0, '2025-08-28 17:16:46', NULL),
(95, 'Women\'s Day ', 'Women\'s Day', 'poster_1756397995_68b081ab122ea.png', 'thumb_poster_1756397995_68b081ab122ea.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:19:55', NULL),
(96, 'Women\'s Day , महिला दिन', 'Women\'s Day , महिला दिन', 'poster_1756398021_68b081c50066f.png', 'thumb_poster_1756398021_68b081c50066f.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:20:21', NULL),
(97, 'Women\'s Day ', 'Women\'s Day', 'poster_1756398052_68b081e43e06d.png', 'thumb_poster_1756398052_68b081e43e06d.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:20:52', NULL),
(98, 'Women\'s Day , महिला दिन', 'Women\'s Day , महिला दिन', 'poster_1756398107_68b0821b9d97d.png', 'thumb_poster_1756398107_68b0821b9d97d.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:21:48', NULL),
(99, 'Women\'s Day ', 'Women\'s Day', 'poster_1756398143_68b0823f968b8.png', 'thumb_poster_1756398143_68b0823f968b8.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:22:23', NULL),
(100, 'Women\'s Day , महिला दिन', 'Women\'s Day , महिला दिन', 'poster_1756398170_68b0825a90f44.png', 'thumb_poster_1756398170_68b0825a90f44.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:22:50', NULL),
(101, 'Women\'s Day ', 'Women\'s Day', 'poster_1756398205_68b0827d2b731.png', 'thumb_poster_1756398205_68b0827d2b731.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:23:25', NULL),
(102, 'Women\'s Day , महिला दिन', 'Women\'s Day , महिला दिन', 'poster_1756398232_68b08298c6acb.png', 'thumb_poster_1756398232_68b08298c6acb.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:23:52', NULL),
(103, 'Women\'s Day , महिला दिन', 'Women\'s Day , महिला दिन', 'poster_1756398267_68b082bb7bd6c.png', 'thumb_poster_1756398267_68b082bb7bd6c.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:24:27', NULL),
(104, 'Women\'s Day , महिला दिन', 'Women\'s Day , महिला दिन', 'poster_1756398338_68b0830201e89.png', 'thumb_poster_1756398338_68b0830201e89.png', 23, 'Women\'s Day , महिला दिन', 0, 0, 0, 0, '2025-08-28 17:25:39', NULL),
(105, 'Mothers Day', 'Mothers Day', 'poster_1756398452_68b0837430cef.png', 'thumb_poster_1756398452_68b0837430cef.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:27:32', NULL),
(106, 'Mother\'s Day ', 'Mother\'s Day', 'poster_1756398485_68b0839594510.png', 'thumb_poster_1756398485_68b0839594510.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:28:05', NULL),
(107, 'Mother\'s Day ', 'Mother\'s Day ', 'poster_1756398580_68b083f406892.png', 'thumb_poster_1756398580_68b083f406892.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:29:40', NULL),
(108, 'Mother\'s Day ', 'Mother\'s Day ', 'poster_1756398617_68b084198c638.png', 'thumb_poster_1756398617_68b084198c638.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:30:17', NULL),
(109, 'Mother\'s Day ', 'Mother\'s Day ', 'poster_1756398651_68b0843bd3df5.png', 'thumb_poster_1756398651_68b0843bd3df5.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:30:52', NULL),
(110, 'Mother\'s Day ', 'Mother\'s Day ', 'poster_1756398688_68b08460d19a2.png', 'thumb_poster_1756398688_68b08460d19a2.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 5, '2025-08-28 17:31:28', '2025-09-04 09:03:09'),
(111, 'Mother\'s Day ', 'Mother\'s Day', 'poster_1756398728_68b0848884565.png', 'thumb_poster_1756398728_68b0848884565.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:32:08', NULL),
(112, 'Mother\'s Day', 'Mother\'s Day ', 'poster_1756398773_68b084b530a56.png', 'thumb_poster_1756398773_68b084b530a56.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:32:53', NULL),
(113, 'Mother\'s Day ', 'Mother\'s Day  ', 'poster_1756398997_68b085950fea2.png', 'thumb_poster_1756398997_68b085950fea2.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:36:37', NULL),
(114, 'Mother\'s Day , मातृदिन ', 'Mother\'s Day , मातृदिन ', 'poster_1756399046_68b085c68f37d.png', 'thumb_poster_1756399046_68b085c68f37d.png', 24, 'Mother\'s Day , मातृदिन ', 0, 0, 0, 0, '2025-08-28 17:37:26', NULL),
(115, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399137_68b08621ac378.png', 'thumb_poster_1756399137_68b08621ac378.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 0, '2025-08-28 17:38:57', NULL),
(116, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399169_68b086417f8dc.png', 'thumb_poster_1756399169_68b086417f8dc.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 1, '2025-08-28 17:39:29', '2025-09-03 12:35:55'),
(117, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399322_68b086da4818c.png', 'thumb_poster_1756399322_68b086da4818c.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 1, '2025-08-28 17:42:02', '2025-09-03 12:35:46'),
(118, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399360_68b0870040de3.png', 'thumb_poster_1756399360_68b0870040de3.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 2, '2025-08-28 17:42:40', '2025-09-04 13:19:00'),
(119, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399388_68b0871cb7282.png', 'thumb_poster_1756399388_68b0871cb7282.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 0, '2025-08-28 17:43:08', NULL),
(120, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399420_68b0873c08551.png', 'thumb_poster_1756399420_68b0873c08551.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 0, '2025-08-28 17:43:40', NULL),
(121, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399447_68b08757513f5.png', 'thumb_poster_1756399447_68b08757513f5.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 0, '2025-08-28 17:44:07', NULL),
(122, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399481_68b0877910bc7.png', 'thumb_poster_1756399481_68b0877910bc7.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 1, '2025-08-28 17:44:41', '2025-09-03 12:36:17'),
(123, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399516_68b0879c331bb.png', 'thumb_poster_1756399516_68b0879c331bb.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 0, '2025-08-28 17:45:16', NULL),
(124, 'World\'s No Tobacco Day', 'World\'s No Tobacco Day', 'poster_1756399540_68b087b4af3c1.png', 'thumb_poster_1756399540_68b087b4af3c1.png', 25, 'World\'s No Tobacco Day', 0, 0, 0, 0, '2025-08-28 17:45:40', NULL),
(125, 'World environment Day , ', 'World environment Day  ', 'poster_1756399707_68b0885bbc610.png', 'thumb_poster_1756399707_68b0885bbc610.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:48:27', NULL),
(126, 'World environment Day ', 'World environment Day ', 'poster_1756399734_68b0887616cb2.png', 'thumb_poster_1756399734_68b0887616cb2.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:48:54', NULL),
(127, 'World environment Day  ', 'World environment Day', 'poster_1756399758_68b0888eb7687.png', 'thumb_poster_1756399758_68b0888eb7687.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:49:18', NULL),
(128, 'World environment Day ', 'World environment Day ', 'poster_1756399789_68b088ad27c47.png', 'thumb_poster_1756399789_68b088ad27c47.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:49:49', NULL),
(129, 'World environment Day ', 'World environment Day', 'poster_1756399828_68b088d4215a8.png', 'thumb_poster_1756399828_68b088d4215a8.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:50:28', NULL),
(130, 'World environment Day ', 'World environment Day', 'poster_1756399863_68b088f7b00d3.png', 'thumb_poster_1756399863_68b088f7b00d3.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:51:04', NULL),
(131, 'World environment Day', 'World environment Day ', 'poster_1756399899_68b0891b581ba.png', 'thumb_poster_1756399899_68b0891b581ba.png', 26, 'World environment Day  , जागतिक पर्यावरण दिन', 0, 0, 0, 0, '2025-08-28 17:51:39', NULL),
(132, 'Indian Air Force Day ', 'Indian Air Force Day', 'poster_1756400019_68b0899364536.png', 'thumb_poster_1756400019_68b0899364536.png', 27, 'Indian Air Force Day , भारतीय वायुसेना दिन', 0, 0, 0, 0, '2025-08-28 17:53:39', NULL),
(133, 'Indian Air Force Day , भारतीय वायुसेना दिन', 'Indian Air Force Day , भारतीय वायुसेना दिन', 'poster_1756400042_68b089aaf29f8.png', 'thumb_poster_1756400042_68b089aaf29f8.png', 27, 'Indian Air Force Day , भारतीय वायुसेना दिन', 0, 0, 0, 0, '2025-08-28 17:54:03', NULL),
(134, 'Indian Air Force Day', 'Indian Air Force Day ', 'poster_1756400082_68b089d20ce90.png', 'thumb_poster_1756400082_68b089d20ce90.png', 27, 'Indian Air Force Day , भारतीय वायुसेना दिन', 0, 0, 0, 0, '2025-08-28 17:54:42', NULL),
(135, 'Indian Air Force Day ', 'Indian Air Force Day', 'poster_1756400113_68b089f14c498.png', 'thumb_poster_1756400113_68b089f14c498.png', 27, 'Indian Air Force Day , भारतीय वायुसेना दिन', 0, 0, 0, 0, '2025-08-28 17:55:13', NULL),
(136, 'Indian Air Force Day , भारतीय वायुसेना दिन', 'Indian Air Force Day , भारतीय वायुसेना दिन', 'poster_1756400143_68b08a0f85bfd.png', 'thumb_poster_1756400143_68b08a0f85bfd.png', 27, 'Indian Air Force Day , भारतीय वायुसेना दिन', 0, 0, 0, 0, '2025-08-28 17:55:43', NULL),
(137, 'Indian Air Force Day ', 'Indian Air Force Day', 'poster_1756400177_68b08a31c7106.png', 'thumb_poster_1756400177_68b08a31c7106.png', 27, 'Indian Air Force Day , भारतीय वायुसेना दिन', 0, 0, 0, 0, '2025-08-28 17:56:18', NULL),
(138, ' International Youth Day', ' International Youth Day ', 'poster_1756400277_68b08a955c805.png', 'thumb_poster_1756400277_68b08a955c805.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 1, '2025-08-28 17:57:57', '2025-09-01 18:35:47'),
(139, ' International Youth Day ', ' International Youth Day  ', 'poster_1756400308_68b08ab4a2860.png', 'thumb_poster_1756400308_68b08ab4a2860.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 2, '2025-08-28 17:58:28', '2025-09-06 12:05:08'),
(140, ' International Youth Day ', ' International Youth Day ', 'poster_1756400338_68b08ad2557f3.png', 'thumb_poster_1756400338_68b08ad2557f3.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 1, '2025-08-28 17:58:58', '2025-09-01 18:34:55'),
(141, ' International Youth Day  ', ' International Youth Day ', 'poster_1756400368_68b08af048710.png', 'thumb_poster_1756400368_68b08af048710.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 0, '2025-08-28 17:59:28', NULL),
(142, ' International Youth Day ', ' International Youth Day  ', 'poster_1756400399_68b08b0faecde.png', 'thumb_poster_1756400399_68b08b0faecde.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 0, '2025-08-28 17:59:59', NULL),
(143, ' International Youth Day ', ' International Youth Day ', 'poster_1756400428_68b08b2ca0600.png', 'thumb_poster_1756400428_68b08b2ca0600.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 0, '2025-08-28 18:00:28', NULL),
(144, ' International Youth Day ', ' International Youth Day  ', 'poster_1756400459_68b08b4bee26f.png', 'thumb_poster_1756400459_68b08b4bee26f.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 1, '2025-08-28 18:01:00', '2025-09-01 18:35:13'),
(145, ' International Youth Day ', ' International Youth Day ', 'poster_1756400497_68b08b71144b0.png', 'thumb_poster_1756400497_68b08b71144b0.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 0, '2025-08-28 18:01:37', NULL),
(146, ' International Youth Day ', ' International Youth Day ', 'poster_1756400525_68b08b8ddc918.png', 'thumb_poster_1756400525_68b08b8ddc918.png', 28, ' International Youth Day , आंतरराष्ट्रीय युवा दिन', 0, 0, 0, 0, '2025-08-28 18:02:06', NULL),
(147, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400640_68b08c0013c41.png', 'thumb_poster_1756400640_68b08c0013c41.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 1, '2025-08-28 18:04:00', '2025-08-29 08:22:09'),
(148, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400726_68b08c56d144b.png', 'thumb_poster_1756400726_68b08c56d144b.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:05:27', NULL),
(149, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400756_68b08c748dfdf.png', 'thumb_poster_1756400756_68b08c748dfdf.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:05:56', NULL),
(150, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400778_68b08c8ae972c.png', 'thumb_poster_1756400778_68b08c8ae972c.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:06:19', NULL),
(151, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400803_68b08ca3db530.png', 'thumb_poster_1756400803_68b08ca3db530.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:06:44', NULL),
(152, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400825_68b08cb91f6b3.png', 'thumb_poster_1756400825_68b08cb91f6b3.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:07:05', NULL),
(153, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400852_68b08cd4b101d.png', 'thumb_poster_1756400852_68b08cd4b101d.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:07:33', NULL),
(154, 'Navratri , नवरात्री ', 'Navratri , नवरात्री ', 'poster_1756400882_68b08cf22f0b9.png', 'thumb_poster_1756400882_68b08cf22f0b9.png', 29, 'Navratri , नवरात्री ', 0, 0, 0, 0, '2025-08-28 18:08:02', NULL),
(155, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756400964_68b08d44b98fd.png', 'thumb_poster_1756400964_68b08d44b98fd.png', 30, 'Dashehra , दसरा', 0, 0, 0, 1, '2025-08-28 18:09:24', '2025-09-09 03:18:41'),
(156, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756400997_68b08d65e068f.png', 'thumb_poster_1756400997_68b08d65e068f.png', 30, 'Dashehra , दसरा', 0, 0, 0, 1, '2025-08-28 18:09:58', '2025-09-09 03:18:54'),
(157, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401025_68b08d819fd78.png', 'thumb_poster_1756401025_68b08d819fd78.png', 30, 'Dashehra , दसरा', 0, 0, 0, 5, '2025-08-28 18:10:25', '2025-09-09 03:33:57'),
(158, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401051_68b08d9bc114e.png', 'thumb_poster_1756401051_68b08d9bc114e.png', 30, '', 0, 0, 0, 0, '2025-08-28 18:10:51', NULL),
(159, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401081_68b08db9cef60.png', 'thumb_poster_1756401081_68b08db9cef60.png', 30, 'Dashehra , दसरा', 0, 0, 0, 1, '2025-08-28 18:11:21', '2025-09-07 10:08:20'),
(160, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401106_68b08dd2793cb.png', 'thumb_poster_1756401106_68b08dd2793cb.png', 30, 'Dashehra , दसरा', 0, 0, 0, 0, '2025-08-28 18:11:46', NULL),
(161, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401137_68b08df19b89f.png', 'thumb_poster_1756401137_68b08df19b89f.png', 30, 'Dashehra , दसरा', 0, 0, 0, 0, '2025-08-28 18:12:17', NULL),
(162, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401166_68b08e0e8c513.png', 'thumb_poster_1756401166_68b08e0e8c513.png', 30, 'Dashehra , दसरा', 0, 0, 0, 0, '2025-08-28 18:12:46', NULL),
(163, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401194_68b08e2a9ffe0.png', 'thumb_poster_1756401194_68b08e2a9ffe0.png', 30, 'Dashehra , दसरा', 0, 0, 0, 1, '2025-08-28 18:13:14', '2025-09-10 16:24:24'),
(164, 'Dashehra , दसरा', 'Dashehra , दसरा', 'poster_1756401218_68b08e4277bd8.png', 'thumb_poster_1756401218_68b08e4277bd8.png', 30, 'Dashehra , दसरा', 0, 0, 0, 5, '2025-08-28 18:13:38', '2025-09-10 04:00:59'),
(165, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401285_68b08e8584c43.png', 'thumb_poster_1756401285_68b08e8584c43.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:14:45', NULL),
(166, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401313_68b08ea1bab7e.png', 'thumb_poster_1756401313_68b08ea1bab7e.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:15:14', NULL),
(167, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401338_68b08ebaef1fc.png', 'thumb_poster_1756401338_68b08ebaef1fc.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:15:39', NULL),
(168, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401360_68b08ed0a0e55.png', 'thumb_poster_1756401360_68b08ed0a0e55.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:16:00', NULL),
(169, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401382_68b08ee6e6d1b.png', 'thumb_poster_1756401382_68b08ee6e6d1b.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:16:23', NULL),
(170, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401407_68b08eff060c6.png', 'thumb_poster_1756401407_68b08eff060c6.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:16:47', NULL),
(171, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401427_68b08f137cb11.png', 'thumb_poster_1756401427_68b08f137cb11.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:17:07', NULL),
(172, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401451_68b08f2b00142.png', 'thumb_poster_1756401451_68b08f2b00142.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:17:31', NULL),
(173, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401469_68b08f3dea860.png', 'thumb_poster_1756401469_68b08f3dea860.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:17:50', NULL),
(174, 'Sardar Patel Jayanti ', 'Sardar Patel Jayanti ', 'poster_1756401493_68b08f55e7555.png', 'thumb_poster_1756401493_68b08f55e7555.png', 31, 'Sardar Patel Jayanti ', 0, 0, 0, 0, '2025-08-28 18:18:14', NULL),
(175, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401572_68b08fa423b5f.png', 'thumb_poster_1756401572_68b08fa423b5f.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 6, '2025-08-28 18:19:32', '2025-09-06 08:20:39'),
(176, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401597_68b08fbd42ad8.png', 'thumb_poster_1756401597_68b08fbd42ad8.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 1, '2025-08-28 18:19:57', '2025-09-09 03:17:40'),
(177, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401616_68b08fd0e9c6b.png', 'thumb_poster_1756401616_68b08fd0e9c6b.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 2, '2025-08-28 18:20:17', '2025-09-06 07:59:49'),
(178, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401643_68b08febaa2c6.png', 'thumb_poster_1756401643_68b08febaa2c6.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 0, '2025-08-28 18:20:43', NULL),
(179, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401667_68b0900330ac2.png', 'thumb_poster_1756401667_68b0900330ac2.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 0, '2025-08-28 18:21:07', NULL),
(180, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401690_68b0901a58cde.png', 'thumb_poster_1756401690_68b0901a58cde.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 0, '2025-08-28 18:21:30', NULL),
(181, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401716_68b090342945e.png', 'thumb_poster_1756401716_68b090342945e.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 0, '2025-08-28 18:21:56', NULL),
(182, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401748_68b09054b6092.png', 'thumb_poster_1756401748_68b09054b6092.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 6, '2025-08-28 18:22:28', '2025-09-07 10:09:36'),
(183, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401772_68b0906c6dbfc.png', 'thumb_poster_1756401772_68b0906c6dbfc.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 0, '2025-08-28 18:22:52', NULL),
(184, 'Dhanteras , धनतेरस', 'Dhanteras , धनतेरस', 'poster_1756401796_68b0908438324.png', 'thumb_poster_1756401796_68b0908438324.png', 32, 'Dhanteras , धनतेरस', 0, 0, 0, 4, '2025-08-28 18:23:16', '2025-09-09 03:18:24'),
(185, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756401863_68b090c7e32a7.png', 'thumb_poster_1756401863_68b090c7e32a7.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 3, '2025-08-28 18:24:24', '2025-09-06 10:04:33'),
(186, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756401891_68b090e32159c.png', 'thumb_poster_1756401891_68b090e32159c.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 1, '2025-08-28 18:24:51', '2025-09-08 13:30:43'),
(187, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756401912_68b090f8845cf.png', 'thumb_poster_1756401912_68b090f8845cf.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 1, '2025-08-28 18:25:12', '2025-09-03 12:40:48'),
(188, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756401930_68b0910ab923a.png', 'thumb_poster_1756401930_68b0910ab923a.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 4, '2025-08-28 18:25:30', '2025-09-10 03:44:24'),
(189, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756401996_68b0914c41f29.png', 'thumb_poster_1756401996_68b0914c41f29.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 1, '2025-08-28 18:26:36', '2025-09-06 17:03:47'),
(190, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756402032_68b091707ffd7.png', 'thumb_poster_1756402032_68b091707ffd7.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 2, '2025-08-28 18:27:12', '2025-09-10 16:23:20'),
(191, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756402050_68b09182ac655.png', 'thumb_poster_1756402050_68b09182ac655.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 6, '2025-08-28 18:27:30', '2025-09-12 13:02:46'),
(192, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756402071_68b0919715b0e.png', 'thumb_poster_1756402071_68b0919715b0e.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 0, '2025-08-28 18:27:51', NULL),
(193, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756402094_68b091ae1cd49.png', 'thumb_poster_1756402094_68b091ae1cd49.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 0, '2025-08-28 18:28:14', NULL),
(194, ' Diwali , दिवाळी', ' Diwali , दिवाळी', 'poster_1756402121_68b091c9ab610.png', 'thumb_poster_1756402121_68b091c9ab610.png', 33, ' Diwali , दिवाळी', 0, 0, 0, 2, '2025-08-28 18:28:41', '2025-09-03 12:50:04'),
(195, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402227_68b09233df5a1.jpg', 'thumb_poster_1756402227_68b09233df5a1.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 0, '2025-08-28 18:30:28', NULL),
(196, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402250_68b0924ae305e.jpg', 'thumb_poster_1756402250_68b0924ae305e.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 3, '2025-08-28 18:30:51', '2025-09-13 10:33:02'),
(197, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402275_68b092635c3dc.jpg', 'thumb_poster_1756402275_68b092635c3dc.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 6, '2025-08-28 18:31:15', '2025-09-13 10:33:06'),
(198, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402300_68b0927c6e71c.jpg', 'thumb_poster_1756402300_68b0927c6e71c.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 2, '2025-08-28 18:31:40', '2025-09-10 04:19:44'),
(199, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402324_68b0929466877.jpg', 'thumb_poster_1756402324_68b0929466877.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 7, '2025-08-28 18:32:04', '2025-09-11 03:16:32'),
(200, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402343_68b092a7e5b13.jpg', 'thumb_poster_1756402343_68b092a7e5b13.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 5, '2025-08-28 18:32:24', '2025-09-08 12:03:17'),
(201, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402363_68b092bbd9c50.jpg', 'thumb_poster_1756402363_68b092bbd9c50.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 4, '2025-08-28 18:32:43', '2025-09-10 16:10:54'),
(202, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402380_68b092cc7e202.jpg', 'thumb_poster_1756402380_68b092cc7e202.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 0, '2025-08-28 18:33:00', NULL),
(203, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402400_68b092e06a841.jpg', 'thumb_poster_1756402400_68b092e06a841.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 10, '2025-08-28 18:33:20', '2025-09-05 07:57:07'),
(204, 'Bhai Dooj , भाऊबीज', 'Bhai Dooj , भाऊबीज', 'poster_1756402418_68b092f2e7a19.jpg', 'thumb_poster_1756402418_68b092f2e7a19.jpg', 35, 'Bhai Dooj , भाऊबीज', 0, 0, 0, 83, '2025-08-28 18:33:39', '2025-09-13 10:32:48'),
(205, 'Dhuleti , होळी', 'Dhuleti , होळी\r\n', 'poster_1756402618_68b093ba55713.png', 'thumb_poster_1756402618_68b093ba55713.png', 39, 'Dhuleti , होळी', 0, 0, 0, 1, '2025-08-28 18:36:58', '2025-09-06 08:25:20'),
(206, 'Dhuleti , होळी', 'Dhuleti , होळी\r\n', 'poster_1756402673_68b093f1d8937.png', 'thumb_poster_1756402673_68b093f1d8937.png', 35, 'Dhuleti , होळी , Holi', 0, 0, 0, 5, '2025-08-28 18:37:54', '2025-09-07 20:16:35'),
(207, 'Dhuleti', 'Dhuleti', 'poster_1756402706_68b0941203168.png', 'thumb_poster_1756402706_68b0941203168.png', 39, 'Dhuleti , होळी , Holi', 0, 0, 0, 0, '2025-08-28 18:38:26', NULL),
(208, 'Dhuleti  ', 'Dhuleti  ', 'poster_1756402733_68b0942dbe8c6.png', 'thumb_poster_1756402733_68b0942dbe8c6.png', 39, 'Dhuleti , होळी , Holi', 0, 0, 0, 5, '2025-08-28 18:38:54', '2025-09-06 17:08:20'),
(209, 'Dhuleti ', 'Dhuleti  ', 'poster_1756402764_68b0944cd6752.png', 'thumb_poster_1756402764_68b0944cd6752.png', 39, 'Dhuleti , होळी , Holi', 0, 0, 0, 0, '2025-08-28 18:39:24', NULL),
(212, 'Dhuleti ', 'Dhuleti  ', 'poster_1756402865_68b094b16bc42.png', 'thumb_poster_1756402865_68b094b16bc42.png', 39, 'Dhuleti , होळी , Holi', 0, 0, 0, 2, '2025-08-28 18:41:05', '2025-09-04 12:29:06'),
(211, 'Dhuleti  ', 'Dhuleti  ', 'poster_1756402823_68b09487dd76d.png', 'thumb_poster_1756402823_68b09487dd76d.png', 39, 'Dhuleti , ', 0, 0, 0, 0, '2025-08-28 18:40:24', NULL),
(213, 'Dhuleti  ', 'Dhuleti  ', 'poster_1756402896_68b094d0425af.png', 'thumb_poster_1756402896_68b094d0425af.png', 39, 'Dhuleti , होळी , Holi', 0, 0, 0, 1, '2025-08-28 18:41:36', '2025-09-06 17:09:14'),
(214, 'Dhuleti , होळी , Holi', 'Dhuleti , होळी , Holi', 'poster_1756402917_68b094e571549.png', 'thumb_poster_1756402917_68b094e571549.png', 39, 'Dhuleti , होळी , Holi', 0, 0, 0, 1, '2025-08-28 18:41:57', '2025-09-06 17:04:49'),
(215, 'Holi ', 'Holi,  ', 'poster_1756402986_68b0952a868f9.png', 'thumb_poster_1756402986_68b0952a868f9.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:43:06', NULL),
(216, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403018_68b0954a7e8b8.png', 'thumb_poster_1756403018_68b0954a7e8b8.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:43:38', NULL),
(217, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403056_68b095701a1d8.png', 'thumb_poster_1756403056_68b095701a1d8.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:44:16', NULL),
(218, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403078_68b09586a7ed1.png', 'thumb_poster_1756403078_68b09586a7ed1.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:44:39', NULL),
(219, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403103_68b0959f60910.png', 'thumb_poster_1756403103_68b0959f60910.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:45:03', NULL),
(220, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403124_68b095b4d95e9.png', 'thumb_poster_1756403124_68b095b4d95e9.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:45:25', NULL),
(221, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403185_68b095f145b18.png', 'thumb_poster_1756403185_68b095f145b18.png', 41, 'Holi, होळी ,', 0, 0, 0, 2, '2025-08-28 18:46:26', '2025-09-08 13:34:41'),
(222, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403210_68b0960a2fa5d.png', 'thumb_poster_1756403210_68b0960a2fa5d.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:46:50', NULL),
(223, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403228_68b0961ca6b7d.png', 'thumb_poster_1756403228_68b0961ca6b7d.png', 41, 'Holi, होळी ,', 0, 0, 0, 0, '2025-08-28 18:47:08', NULL),
(224, 'Holi, होळी ,', 'Holi, होळी ,', 'poster_1756403242_68b0962aeb0c1.png', 'thumb_poster_1756403242_68b0962aeb0c1.png', 41, 'Holi, होळी ,', 0, 0, 0, 2, '2025-08-28 18:47:23', '2025-09-06 08:40:02'),
(225, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403325_68b0967d3fc71.png', 'thumb_poster_1756403325_68b0967d3fc71.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:48:45', NULL),
(226, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403348_68b09694764b9.png', 'thumb_poster_1756403348_68b09694764b9.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:49:08', NULL),
(227, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403373_68b096ad4fa11.png', 'thumb_poster_1756403373_68b096ad4fa11.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 3, '2025-08-28 18:49:33', '2025-09-02 12:54:49'),
(228, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403398_68b096c62ec77.png', 'thumb_poster_1756403398_68b096c62ec77.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:49:58', NULL),
(229, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403418_68b096da09f83.png', 'thumb_poster_1756403418_68b096da09f83.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:50:18', NULL),
(230, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403440_68b096f0e9c23.png', 'thumb_poster_1756403440_68b096f0e9c23.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:50:41', NULL),
(231, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403466_68b0970a7b047.png', 'thumb_poster_1756403466_68b0970a7b047.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:51:06', NULL),
(232, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403484_68b0971cc65cd.png', 'thumb_poster_1756403484_68b0971cc65cd.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:51:24', NULL),
(233, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403501_68b0972d252a8.png', 'thumb_poster_1756403501_68b0972d252a8.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:51:41', NULL),
(234, 'National Education Day , राष्ट्रीय शिक्षण दिन', 'National Education Day , राष्ट्रीय शिक्षण दिन', 'poster_1756403525_68b097455d127.png', 'thumb_poster_1756403525_68b097455d127.png', 42, 'National Education Day , राष्ट्रीय शिक्षण दिन', 0, 0, 0, 0, '2025-08-28 18:52:05', NULL),
(235, 'World Tourism Day ', 'World Tourism Day ', 'poster_1757274242_68bde08221df2.png', 'thumb_poster_1757274242_68bde08221df2.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 5, '2025-09-07 20:44:02', '2025-09-08 18:20:55'),
(236, 'World Tourism Day , जागतिक पर्यटन दिन ', 'World Tourism Day , जागतिक पर्यटन दिन ', 'poster_1757274271_68bde09f187f0.png', 'thumb_poster_1757274271_68bde09f187f0.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 0, '2025-09-07 20:44:31', NULL),
(237, 'World Tourism Day ', 'World Tourism Day', 'poster_1757274309_68bde0c5e0305.png', 'thumb_poster_1757274309_68bde0c5e0305.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 3, '2025-09-07 20:45:10', '2025-09-08 08:49:47'),
(238, 'World Tourism Day ', 'World Tourism Day ', 'poster_1757274361_68bde0f96f5a5.png', 'thumb_poster_1757274361_68bde0f96f5a5.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 0, '2025-09-07 20:46:01', NULL),
(239, 'World Tourism Day ', 'World Tourism Day', 'poster_1757274576_68bde1d002801.png', 'thumb_poster_1757274576_68bde1d002801.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 3, '2025-09-07 20:49:36', '2025-09-13 10:12:34'),
(240, 'World Tourism Day , जागतिक पर्यटन दिन ', 'World Tourism Day , जागतिक पर्यटन दिन ', 'poster_1757274606_68bde1ee7ecb5.png', 'thumb_poster_1757274606_68bde1ee7ecb5.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 2, '2025-09-07 20:50:06', '2025-09-12 08:31:54'),
(241, 'World Tourism Day , जागतिक पर्यटन दिन ', 'World Tourism Day , जागतिक पर्यटन दिन ', 'poster_1757274633_68bde20981071.png', 'thumb_poster_1757274633_68bde20981071.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 23, '2025-09-07 20:50:33', '2025-09-13 02:39:46'),
(242, 'World Tourism Day , जागतिक पर्यटन दिन ', 'World Tourism Day , जागतिक पर्यटन दिन ', 'poster_1757274668_68bde22c268fc.png', 'thumb_poster_1757274668_68bde22c268fc.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 6, '2025-09-07 20:51:08', '2025-09-12 11:51:43'),
(243, 'World Tourism Day ', 'World Tourism Day', 'poster_1757274713_68bde259f1531.png', 'thumb_poster_1757274713_68bde259f1531.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 67, '2025-09-07 20:51:54', '2025-09-14 16:34:40'),
(244, 'World Tourism Day ', 'World Tourism Day', 'poster_1757274749_68bde27d391e9.png', 'thumb_poster_1757274749_68bde27d391e9.png', 43, 'World Tourism Day , जागतिक पर्यटन दिन ', 0, 0, 0, 0, '2025-09-07 20:52:29', NULL),
(245, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274802_68bde2b22ceb9.png', 'thumb_poster_1757274802_68bde2b22ceb9.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 0, '2025-09-07 20:53:22', NULL),
(246, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274826_68bde2ca97063.png', 'thumb_poster_1757274826_68bde2ca97063.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 1, '2025-09-07 20:53:46', '2025-09-12 11:52:56'),
(247, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274872_68bde2f88e66b.png', 'thumb_poster_1757274872_68bde2f88e66b.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 0, '2025-09-07 20:54:32', NULL),
(248, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274900_68bde314319cf.png', 'thumb_poster_1757274900_68bde314319cf.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 0, '2025-09-07 20:55:00', NULL),
(249, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274920_68bde328d0523.png', 'thumb_poster_1757274920_68bde328d0523.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 1, '2025-09-07 20:55:21', '2025-09-12 11:53:04'),
(250, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274942_68bde33ea9bbd.png', 'thumb_poster_1757274942_68bde33ea9bbd.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 1, '2025-09-07 20:55:42', '2025-09-12 11:52:46'),
(251, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274971_68bde35b58a53.png', 'thumb_poster_1757274971_68bde35b58a53.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 0, '2025-09-07 20:56:11', NULL),
(252, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757274998_68bde37672118.png', 'thumb_poster_1757274998_68bde37672118.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 0, '2025-09-07 20:56:38', NULL),
(253, 'APJ Abdul Kalam', 'APJ Abdul Kalam', 'poster_1757275025_68bde391dc18b.png', 'thumb_poster_1757275025_68bde391dc18b.png', 44, 'APJ Abdul Kalam', 0, 0, 0, 0, '2025-09-07 20:57:06', NULL),
(254, 'Ambedkar Jayanti ', 'Ambedkar Jayanti  ', 'poster_1757275130_68bde3fa833a1.png', 'thumb_poster_1757275130_68bde3fa833a1.png', 45, 'Ambedkar Jayanti , आंबेडकर जयंती', 0, 0, 0, 0, '2025-09-07 20:58:50', NULL),
(255, 'Ambedkar Jayanti', 'Ambedkar Jayanti  ', 'poster_1757275163_68bde41bae4ca.png', 'thumb_poster_1757275163_68bde41bae4ca.png', 45, 'Ambedkar Jayanti , आंबेडकर जयंती', 0, 0, 0, 6, '2025-09-07 20:59:23', '2025-09-14 16:35:48');
INSERT INTO `posters` (`poster_id`, `title`, `description`, `image_url`, `thumbnail_url`, `category_id`, `tags`, `is_premium`, `is_featured`, `download_count`, `view_count`, `created_at`, `updated_at`) VALUES
(256, 'Ambedkar Jayanti , आंबेडकर जयंती', 'Ambedkar Jayanti , आंबेडकर जयंती', 'poster_1757275204_68bde4446c6c0.png', 'thumb_poster_1757275204_68bde4446c6c0.png', 45, 'Ambedkar Jayanti , आंबेडकर जयंती', 0, 0, 0, 10, '2025-09-07 21:00:04', '2025-09-11 10:14:47'),
(257, 'Dr. Baba saheb Ambedkar Jayanti', 'Dr. Baba saheb Ambedkar Jayanti', 'poster_1757275420_68bde51c2bcf2.png', 'thumb_poster_1757275420_68bde51c2bcf2.png', 45, 'Ambedkar Jayanti , आंबेडकर जयंती', 0, 0, 0, 6, '2025-09-07 21:03:40', '2025-09-14 16:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` varchar(100) NOT NULL DEFAULT 'user',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `subscription_type` enum('basic','pro','premium') DEFAULT 'basic',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `role`, `email`, `password`, `full_name`, `phone`, `address`, `logo`, `subscription_type`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'vijaysalve8080@gmail.com', 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vijay Shivaji Salve', '9168585820', 'At Post chitegaon paithan road Aurangabad pin code 431105', 'logo_1757863114_68c6dccabb27c.png', 'premium', 1, '2025-09-11 17:14:48', '2025-08-05 13:34:35', '2025-09-14 16:18:34'),
(2, 'john_doe', 'user', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', NULL, NULL, NULL, 'pro', 1, NULL, '2025-08-05 13:34:35', NULL),
(3, 'jane_smith', 'user', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', NULL, NULL, NULL, 'basic', 1, NULL, '2025-08-05 13:34:35', NULL),
(4, 'testuser', 'user', 'test@example.com', '$2y$10$thQurUbhbHzJF.PGPSGNrey7HOb5Lh9kgdKC5KUZyNBJxip31xna6', 'Test User', NULL, NULL, NULL, 'basic', 1, '2025-08-06 16:58:55', '2025-08-05 08:09:31', '2025-08-06 22:28:55'),
(5, 'vijay123', 'user', 'vijaysalve8080@gmail.com', '$2y$10$RlXhm5uaYNLMtidlVeHu0ejXe/ziPvGdDYwqkgWZ9gI/mZfBA0JC6', 'vijay salve', '+919168585820', 'At Post chitegaon paithan road Aurangabad pin code 431105', 'logo_1757352790_68bf1356cbe3c.jpg', 'premium', 1, '2025-09-13 11:50:44', '2025-08-16 17:57:08', '2025-09-13 11:50:44'),
(6, 'Vijay', 'user', 'vijay@gmail.com', '$2y$10$PH9/fT558d6/Go677uWtI.dPe8ho5TJ24krjCwdzlFIvHCxbfaqlC', 'Vijay salve', NULL, NULL, NULL, 'basic', 1, NULL, '2025-08-16 18:59:47', NULL),
(7, 'salman', 'user', 'thesalmanattar@gmail.com', '$2y$10$Q5TyObXNAjZpcKKiwrC.COgXvnlrwsQTq.u3Okhev71U1wMmu0s6a', 'Good Luck Multiservices', '8788759260', 'Sadat Nagar, Paithan Road, Chitegaon', NULL, 'basic', 1, '2025-09-02 14:51:36', '2025-09-02 14:49:41', '2025-09-02 14:51:36'),
(8, 'levelonefitnessgym', 'user', 'piyushlade30@gmail.com', '$2y$10$DNvp4a/ow5TyaaoCEsimU.3QBy5T.8v8VobT5YPVrSbWhO5FUl3yS', 'LevelOne Fitness Unisex Gym ', '+918177834873', 'Underground of Dahihande Building, Bank of Maharashtra, Chhatrapati Sambhaji Nagar, Chikalthana ', 'logo_1756985718_68b97976c6cc6.jpg', 'basic', 1, '2025-09-04 09:02:28', '2025-09-04 09:02:17', '2025-09-04 12:35:18'),
(9, 'Sanju', 'user', 'sanjay@gmail.com', '$2y$10$r.H2b7Lv/tDal27lQy8ZtOmHELlpqPfKM6OcyRyTvReiwXkRCPhxC', 'Vijay S.Salve', '9168585280', 'At post Chitegaon ', 'logo_1757353271_68bf1537b5553.jpg', 'basic', 1, '2025-09-08 11:55:41', '2025-09-06 17:11:22', '2025-09-08 18:41:11'),
(10, 'Yogesh', 'user', 'yogeshdhavle516@gmail.com', '$2y$10$nJDjk.gHHv6maRGDTUq0z.4N4HIVgmqzJIG7V1D1PvY.0vi4IGaIu', 'Maharashtra salon ', '9604772021', 'At post chitegaon', 'logo_1757235872_68bd4aa04d5dd.jpg', 'basic', 1, '2025-09-07 10:07:24', '2025-09-07 10:03:16', '2025-09-07 10:09:15'),
(11, 'Vikas', 'user', 'vikas@gmail.com', '$2y$10$yg133RTtqZJ7tAtMFjTfK.u7xiUbh4Xvq.61U.6o9hLjZrnA3dQgu', 'Vikas ganraj ', '8446874394', 'At post Bidkin', 'logo_1757424148_68c02a145c26a.jpg', 'basic', 1, '2025-09-09 14:20:46', '2025-09-09 14:19:53', '2025-09-09 14:22:28'),
(12, 'Omkar', 'user', 'omkarchothe74@gmail.com', '$2y$10$n9FSD5WWN7LFO3IGUr0TWuqgIXVlwY5RUKigl03XZJsmnzCb1htU2', 'Omkar Chothe ', '9175055772', 'At post pangra', 'logo_1757517661_68c1975d3bd55.jpg', 'basic', 1, '2025-09-10 16:20:14', '2025-09-10 16:18:10', '2025-09-10 16:21:01'),
(13, 'salveajay105@gmail.com', 'user', 'salveajay105@gmail.com', '$2y$10$877naLPX6NiZe0WW2dy2a./2JpH/nBKIi.xE8Pz6ZojhFRGVGa886', 'Ajay Salve', '8530268264', 'At post Chitegaon ', 'logo_1757518137_68c19939b99a0.heic', 'basic', 1, '2025-09-10 16:26:38', '2025-09-10 16:25:57', '2025-09-10 16:28:57'),
(14, 'test@gmail.com', 'user', 'test@gmail.com', '$2y$10$H7913klQbi214s4rYm3GvOLXTjEtwkpUKdRM2zAmjV69GlXAve/am', 'Test user', '8866552233', 'At Post chitegaon paithan road Aurangabad pin code 431105', 'logo_1757557195_68c231cb976c7.jpg', 'basic', 1, '2025-09-13 11:50:23', '2025-09-11 03:18:37', '2025-09-13 11:50:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_categories_active_name` (`is_active`,`name`);

--
-- Indexes for table `posters`
--
ALTER TABLE `posters`
  ADD PRIMARY KEY (`poster_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_premium` (`is_premium`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_downloads` (`download_count`),
  ADD KEY `idx_views` (`view_count`),
  ADD KEY `idx_posters_trending` (`created_at`,`view_count`,`download_count`);
ALTER TABLE `posters` ADD FULLTEXT KEY `idx_search` (`title`,`description`,`tags`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_subscription` (`subscription_type`),
  ADD KEY `idx_users_subscription_active` (`subscription_type`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `posters`
--
ALTER TABLE `posters`
  MODIFY `poster_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
