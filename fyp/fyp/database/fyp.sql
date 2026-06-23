-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 10:05 PM
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
-- Database: `fyp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default_avatar.png',
  `appointment_calendar` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `password`, `full_name`, `email`, `phone_number`, `address`, `profile_image`, `appointment_calendar`, `updated_at`) VALUES
(6, '$2y$10$.6IshKqQWDKxNNKEzmEVTembb3Z3AD524MYJRxoumU/eBu98jrlGe', 'Sim Heng Jing', 'hengjingchen102@gmail.com', '0137787266', '19 Jalan Eco Cascadia 5/1', 'uploads/admin_avatars/default_avatar.png', 'https://calendar.app.google/9n4Vz8ys8n51WdCv9', '2026-06-22 18:47:29'),
(10, '$2y$10$dfE93KztYTa8RjQNOBSZyus1CBDWEngxVJjvn6TzW8A3IRnNaKGQ2', 'Sim Heng Ping', 'simhengping@gmail.com', '0137787266', '19 Jalan Eco Cascadia 5/1', 'uploads/admin_avatars/default_avatar.png', 'https://calendar.app.google/Cn9fpqqfKBCHEyGr9', '2026-06-22 19:22:12'),
(11, '$2y$10$Ax5cUn4jTl9X9swTnS206evUC4jGOrMONaGiWBVtUFYeQj9M.4d9q', 'Ng Yong Lok', 'jmslok1234@gmail.com', '0137787266', '19 Jalan Eco Cascadia 5/1', 'uploads/admin_avatars/default_avatar.png', 'https://calendar.app.google/sfiWtgpYopYd7Mm47', '2026-06-22 19:22:14');

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `conversation_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`conversation_id`, `customer_id`, `staff_id`) VALUES
(1, 31, 6);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `sender` enum('customer','staff') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `session_id`, `sender`, `message`, `created_at`, `is_read`) VALUES
(47, 'sijun', 'customer', 'meow', '2026-04-26 21:45:10', 1),
(48, 'sijun', 'staff', 'xixi', '2026-04-26 21:45:19', 0),
(49, 'sijun', 'customer', 'price', '2026-04-26 22:05:39', 1),
(50, 'sijun', 'customer', 'price', '2026-04-26 22:12:19', 1),
(51, 'sijun', 'staff', 'Our aluminum windows and doors are customized based on dimensions. Please provide an approximate size, and I will give you a quote.', '2026-04-26 22:12:19', 0),
(73, 'Sim Heng Jing', 'customer', 'Request a quotation', '2026-05-16 05:38:06', 1),
(74, 'Sim Heng Jing', 'staff', '456', '2026-05-16 05:38:06', 0),
(76, 'Sim Heng Jing', 'staff', 'hoi', '2026-05-16 06:04:38', 0),
(77, 'Sim Heng Jing', 'staff', 'yo', '2026-05-16 06:04:44', 0),
(78, 'Sim Heng Jing', 'customer', 'hi', '2026-05-16 06:39:33', 1),
(79, 'Sim Heng Jing', 'staff', 'yo', '2026-05-16 08:47:14', 0),
(80, 'Sim Heng Jing', 'customer', 'meow', '2026-05-16 09:12:37', 1),
(81, 'Sim Heng Jing', 'customer', 'hi', '2026-05-18 14:18:33', 1),
(82, 'Sim Heng Jing', 'customer', 'm', '2026-05-18 14:18:42', 1),
(83, 'Sim Heng Jing', 'customer', 'n', '2026-05-18 14:18:54', 1),
(84, 'Sim Heng Jing', 'customer', 'halo', '2026-05-18 14:19:10', 1),
(85, 'simhengjing', 'customer', 'View business hours', '2026-05-18 15:49:09', 1),
(86, 'simhengjing', 'staff', 'Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.', '2026-05-18 15:49:09', 0),
(87, 'simhengjing', 'staff', 'ho', '2026-05-19 00:08:34', 0),
(88, 'simhengjing', 'staff', 'ho', '2026-05-19 00:08:34', 0),
(89, 'simhengjing', 'staff', 'ho', '2026-05-19 00:08:34', 0),
(90, 'simhengjing', 'staff', 'ho', '2026-05-19 00:08:34', 0),
(91, 'simhengjing', 'staff', '>', '2026-05-19 00:08:39', 0),
(92, 'simhengjing', 'staff', '>', '2026-05-19 00:08:39', 0),
(93, 'simhengjing', 'staff', '>', '2026-05-19 00:08:39', 0),
(94, 'simhengjing', 'staff', '>', '2026-05-19 00:08:39', 0),
(95, 'SIM HENG PING', 'customer', 'Need help setting up account', '2026-05-19 00:08:50', 1),
(96, 'SIM HENG PING', 'staff', '123', '2026-05-19 00:08:50', 0),
(97, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 00:09:02', 0),
(98, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 00:09:02', 0),
(99, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 00:09:02', 0),
(100, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 00:09:02', 0),
(101, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 00:33:30', 0),
(102, 'SIM HENG PING', 'staff', 'no', '2026-05-19 00:33:35', 0),
(103, 'SIM HENG PING', 'staff', 'ok', '2026-05-19 00:34:38', 0),
(104, 'SIM HENG PING', 'customer', 'hi', '2026-05-19 03:35:38', 1),
(105, 'SIM HENG PING', 'customer', 'oh', '2026-05-19 03:36:12', 1),
(106, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 03:36:31', 0),
(107, 'SIM HENG PING', 'staff', 'hi', '2026-05-19 03:36:43', 0),
(108, 'SIM HENG PING', 'customer', 'meow', '2026-05-19 03:37:00', 1),
(109, 'SIM HENG PING', 'customer', 'can i ask..', '2026-05-19 03:37:21', 1),
(110, 'NG YONG LOK', 'customer', 'Need help setting up account', '2026-05-19 06:51:29', 1),
(111, 'NG YONG LOK', 'staff', '123', '2026-05-19 06:51:29', 0),
(112, 'NG YONG LOK', 'staff', 'hi', '2026-05-19 06:51:57', 0),
(113, 'NG YONG LOK', 'staff', 'hi', '2026-05-19 06:52:31', 0),
(114, 'SIM HENG PING', 'staff', 'cannot', '2026-06-01 14:17:03', 0),
(134, 'guest_ka1nr', 'customer', 'Need help setting up account', '2026-06-16 06:42:38', 1),
(135, 'guest_ka1nr', 'staff', 'To set up your account, please visit our website and click on the Products page. You can browse our products and contact us for a quote in WhatsApp. Our staff will assist you in creating an account and placing your order.', '2026-06-16 06:42:38', 0),
(136, 'guest_ka1nr', 'customer', 'View business hours', '2026-06-16 06:42:41', 1),
(137, 'guest_ka1nr', 'staff', 'Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.', '2026-06-16 06:42:41', 0),
(138, 'guest_ka1nr', 'customer', 'Request a quotation', '2026-06-16 06:42:43', 1),
(139, 'guest_ka1nr', 'staff', '456', '2026-06-16 06:42:43', 0),
(140, 'guest_ka1nr', 'customer', 'Account', '2026-06-16 06:49:32', 1),
(141, 'guest_ka1nr', 'staff', 'To set up your account, please visit our website and click on the Products page. You can browse our products and contact us for a quote in WhatsApp. Our staff will assist you in creating an account and placing your order.', '2026-06-16 06:49:32', 0),
(142, 'guest_ka1nr', 'customer', 'business hours', '2026-06-16 06:49:32', 1),
(143, 'guest_ka1nr', 'staff', 'Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.', '2026-06-16 06:49:32', 0),
(144, 'guest_ka1nr', 'customer', 'Price', '2026-06-16 06:49:33', 1),
(145, 'guest_ka1nr', 'staff', 'Our aluminum windows and doors are customized based on dimensions. Please provide an approximate size, and I will give you a quote.', '2026-06-16 06:49:33', 0),
(146, 'guest_ka1nr', 'staff', 'hi what problem?', '2026-06-16 06:50:33', 0),
(147, 'guest_ka1nr', 'staff', 'you need help?', '2026-06-16 06:50:47', 0),
(148, 'guest_ka1nr', 'customer', 'Account', '2026-06-16 06:51:02', 1),
(149, 'guest_ka1nr', 'staff', 'To set up your account, please visit our website and click on the Products page. You can browse our products and contact us for a quote in WhatsApp. Our staff will assist you in creating an account and placing your order.', '2026-06-16 06:51:02', 0),
(150, 'guest_ka1nr', 'staff', 'you can contact us in WhatsApp, try go to product page and inquire', '2026-06-16 06:51:45', 0),
(151, 'guest_ka1nr', 'customer', 'business hours', '2026-06-16 06:55:24', 1),
(152, 'guest_ka1nr', 'staff', 'Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.', '2026-06-16 06:55:24', 0),
(153, 'SIM HENG PING', 'staff', 'hh', '2026-06-17 08:57:25', 0),
(154, 'SIM HENG PING', 'staff', 'joking', '2026-06-17 08:57:36', 0),
(155, 'guest_ka1nr', 'staff', 'hi', '2026-06-18 16:18:32', 0),
(156, 'Ahman', 'customer', 'Account', '2026-06-18 16:20:36', 1),
(157, 'Ahman', 'staff', 'To set up your account, please visit our website and click on the Products page. You can browse our products and contact us for a quote in WhatsApp. Our staff will assist you in creating an account and placing your order.', '2026-06-18 16:20:36', 0),
(158, 'Ahman', 'customer', 'Price', '2026-06-18 16:20:36', 1),
(159, 'Ahman', 'staff', 'Our aluminum windows and doors are customized based on dimensions. Please provide an approximate size, and I will give you a quote.', '2026-06-18 16:20:36', 0),
(160, 'Ahman', 'customer', 'business hours', '2026-06-18 16:20:38', 1),
(161, 'Ahman', 'staff', 'Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.', '2026-06-18 16:20:38', 0),
(162, 'guest_ka1nr', 'customer', 'ghj', '2026-06-18 16:24:32', 1),
(163, 'Kal', 'customer', 'Price', '2026-06-22 08:35:21', 1),
(164, 'Kal', 'staff', 'Our aluminum windows and doors are customized based on dimensions. Please provide an approximate size, and I will give you a quote.', '2026-06-22 08:35:21', 0),
(165, 'Kal', 'customer', 'Account', '2026-06-22 08:35:23', 1),
(166, 'Kal', 'staff', 'To set up your account, please visit our website and click on the Products page. You can browse our products and contact us for a quote in WhatsApp. Our staff will assist you in creating an account and placing your order.', '2026-06-22 08:35:23', 0),
(167, 'Kal', 'customer', 'business hours', '2026-06-22 08:35:25', 1),
(168, 'Kal', 'staff', 'Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.', '2026-06-22 08:35:25', 0),
(169, 'Kal', 'staff', 'hi', '2026-06-22 08:35:43', 0),
(170, 'Kal', 'staff', 'halo', '2026-06-22 08:36:01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `race` varchar(50) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT '../images/default-user.png',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `phone`, `address`, `gender`, `race`, `status`, `password`, `created_at`, `profile_image`, `updated_at`) VALUES
(60, 'Aiman', 'jmslok123456@gmail.com', '0137787266', '16, Jln Eco Cascadia 1/9', 'Male', 'Malay', 1, '$2y$10$L4l5WK7WhukADimksfZq5.7j.W2mmWD8zMw0r6VhuwEzWcOZV.XOa', '2026-06-19 13:37:44', 'uploads/customer_avatars/defaultAvatar_MelayuMale.png', '2026-06-21 13:03:04'),
(61, 'Ahman', 'kurohimeyuhi05@gmail.com', '0137787266', '20, JALAN SETIA INDAH 12/11, TAMAN SETIA INDAH', 'Male', 'Malay', 1, '$2y$10$N1L3iMq2YJNfKTdS8mOxE.4Z9RIe3oHNFBhvhZiGHJJ.jJSq5xi1m', '2026-06-19 13:39:07', 'uploads/customer_avatars/defaultAvatar_MelayuMale.png', '2026-06-21 12:53:43'),
(62, 'Fashira', 'sengka331@gmail.com', '0137787266', '22, Jalan Setia Indah 12/11, Taman Setia Indah', 'Female', 'Malay', 1, '$2y$10$khsNS/AJMeuCw3qNss2eYOzTn2ZyAb4DHBe4OTAeiGyRi9ypjHpQm', '2026-06-19 13:40:23', 'uploads/customer_avatars/defaultAvatar_MelayuFemale.png', '2026-06-21 09:18:18'),
(63, 'Kelvin', 'jmslok1408@gmail.com', '0125642444', '11, JALAN SETIA INDAH 18/11, TAMAN SETIA INDAH', 'Male', 'Chinese', 1, '$2y$10$Flulp1k0mCdIIflKgE0k7e6.AxaAyuqodrMDOZtgp4tP7i9hDvRd2', '2026-06-19 13:41:28', 'uploads/customer_avatars/defaultAvatar_CinaMale.png', '2026-06-21 09:18:18'),
(64, 'Lim Ren Han', 'renhanlim@gmail.com', '0135172352', '19, JALAN ECO CASCADIA 5/1 TAMAN SETIA ECO CASCADIA', 'Male', 'Chinese', 1, '$2y$10$einvWvPit1Tne1Hihcgw1u4Sz1/9ECJgPZ6b/0IVKy1I.rH9gkqny', '2026-06-19 13:43:14', 'uploads/customer_avatars/defaultAvatar_CinaMale.png', '2026-06-21 09:18:18'),
(65, 'Shahira', 'wowmiao9@gmail.com', '0137519660', '41, Jalan Setia Indah 8/8, Taman Setia Indah', 'Female', 'Indian', 1, '$2y$10$X9K3fpB/cCIesyv1GniKWerRQx5ed477nGcbT1fpZigp3Ryt2bxhO', '2026-06-19 13:44:26', 'uploads/customer_avatars/defaultAvatar_IndianFemale.png', '2026-06-21 09:18:18'),
(66, 'Kal', 'hengjingchen102@gmail.com', '0137787266', '20, JALAN SETIA INDAH 12/11, TAMAN SETIA INDAH', 'Male', 'Other', 1, '$2y$10$n73zz4mx3hX6q03q9BCXD.RRJSzo7DOCnX5jsEn54HPGGq7rLUIVi', '2026-06-19 13:46:18', '../uploads/customer_avatars/user_66_1782153346.jpg', '2026-06-22 18:35:46'),
(67, 'hengsijun', 'hengsijun00@gmail.com', '0137787266', '12, Jalan Eco World5/1, taman eco cascadia', 'Female', 'Indian', 1, '$2y$10$6FMt4FHyTngu1iV5zgLGIO4JpuaKzI.9NfcvkU3yZr7z.Kda9ot5S', '2026-06-19 16:54:44', 'uploads/customer_avatars/defaultAvatar_IndianFemale.png', '2026-06-21 09:18:18'),
(69, 'Thivya', 'haimian04@gmail.com', '0137519660', '22, Jalan Mount Austin, Taman Austin Height9/11', 'Male', 'Malay', 1, '$2y$10$JfubpsQ7O0v22T5.lDH9cuwufXdff0hVwcQv5aXQ5Dpoq9Dew12Em', '2026-06-21 09:10:36', 'uploads/customer_avatars/defaultAvatar_MelayuMale.png', '2026-06-21 09:18:18'),
(70, 'sim heng ping', 'simhengping@gmail.com', '0137519660', '19，jalan eco cascadia 5/1，johor bahru', 'Male', 'Chinese', 1, '$2y$10$AN.jDTKoPfey1G7HKpw/meTDnuFiWcnPugcoxHvGHIqM4c0Y0RlR.', '2026-06-22 08:16:48', 'uploads/customer_avatars/defaultAvatar_CinaMale.png', '2026-06-22 08:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `inv_id` int(11) NOT NULL,
  `qtn_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `final_amount` decimal(10,2) NOT NULL,
  `status` enum('Draft','Sent','Paid','Overdue','Cancelled') DEFAULT 'Draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `stage` enum('deposit','progress','final') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`inv_id`, `qtn_id`, `customer_id`, `staff_id`, `invoice_number`, `final_amount`, `status`, `issue_date`, `due_date`, `paid_date`, `file_path`, `created_at`, `stage`) VALUES
(77, 211, 66, 6, 'INV-20260622-198', 9851.63, 'Paid', '2026-06-22', '2026-06-29', '2026-06-22', 'uploads/invoices/INV-20260622-198_1782139518.pdf', '2026-06-22 14:45:18', 'deposit'),
(78, 211, 66, 6, 'INV-20260622-971', 5910.97, 'Paid', '2026-06-22', '2026-06-29', '2026-06-22', 'uploads/invoices/INV-20260622-971_1782139824.pdf', '2026-06-22 14:50:24', 'progress'),
(79, 211, 66, 6, 'INV-20260622-362', 3940.65, 'Paid', '2026-06-22', '2026-06-29', '2026-06-22', 'uploads/invoices/INV-20260622-362_1782139973.pdf', '2026-06-22 14:52:53', 'final'),
(80, 212, 66, 6, 'INV-20260622-632', 1569.38, 'Paid', '2026-06-22', '2026-06-29', '2026-06-22', 'uploads/invoices/INV-20260622-632_1782141467.pdf', '2026-06-22 15:17:47', 'deposit'),
(81, 212, 66, 6, 'INV-20260622-258', 941.63, 'Paid', '2026-06-22', '2026-06-29', '2026-06-22', 'uploads/invoices/INV-20260622-258_1782141509.pdf', '2026-06-22 15:18:29', 'progress'),
(82, 213, 66, 6, 'INV-20260622-885', 976.50, 'Paid', '2026-06-22', '2026-06-29', '2026-06-23', 'uploads/invoices/INV-20260622-885_1782146527.pdf', '2026-06-22 16:42:07', 'deposit'),
(83, 213, 66, 6, 'INV-20260622-541', 585.90, 'Paid', '2026-06-22', '2026-06-29', '2026-06-23', 'uploads/invoices/INV-20260622-541_1782146571.pdf', '2026-06-22 16:42:51', 'progress'),
(84, 213, 66, 6, 'INV-20260622-651', 390.60, 'Paid', '2026-06-22', '2026-06-29', '2026-06-23', 'uploads/invoices/INV-20260622-651_1782146669.pdf', '2026-06-22 16:44:29', 'final');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `customer_id`, `request_date`, `status`) VALUES
(1, 3, '2026-03-17 07:19:53', 'Completed'),
(2, 23, '2026-03-17 07:22:19', 'Completed'),
(3, 3, '2026-03-20 05:33:40', 'Completed'),
(4, 7, '2026-03-20 06:09:44', 'Completed'),
(5, 3, '2026-03-20 06:10:06', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

CREATE TABLE `payment_records` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `staff_notes` text DEFAULT NULL,
  `qtn_id` int(11) DEFAULT NULL,
  `stage` enum('deposit','progress','final') NOT NULL DEFAULT 'deposit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_records`
--

INSERT INTO `payment_records` (`id`, `customer_id`, `file_path`, `uploaded_at`, `status`, `staff_notes`, `qtn_id`, `stage`) VALUES
(70, 66, 'uploads/payments/1782139508_I am Receipt.png', '2026-06-22 14:45:08', 'Verified', 'nice', 211, 'deposit'),
(71, 66, 'uploads/payments/1782139819_I am Receipt.png', '2026-06-22 14:50:19', 'Verified', '', 211, 'progress'),
(72, 66, 'uploads/payments/1782139967_ErpFYP.drawio.png', '2026-05-22 14:52:47', 'Verified', '', 211, 'final'),
(73, 66, 'uploads/payments/1782141459_ErpFYP.drawio.png', '2026-06-22 15:17:39', 'Verified', '', 212, 'deposit'),
(74, 66, 'uploads/payments/1782141501_1.drawio.png', '2026-06-22 15:18:21', 'Verified', '', 212, 'progress'),
(75, 66, 'uploads/payments/1782146521_1.drawio.png', '2026-06-22 16:42:01', 'Verified', '', 213, 'deposit'),
(76, 66, 'uploads/payments/1782146561_1.drawio.png', '2026-06-22 16:42:41', 'Verified', '', 213, 'progress'),
(77, 66, 'uploads/payments/1782146660_ErpFYP.drawio.png', '2026-06-22 16:44:20', 'Verified', '', 213, 'final');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `door_brand` varchar(100) DEFAULT NULL,
  `material` varchar(50) DEFAULT NULL,
  `design_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_date` date DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `price_per_sqft` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `door_brand`, `material`, `design_type`, `description`, `stock_date`, `status`, `image`, `price_per_sqft`) VALUES
(7, 'ghost sliding door', 'Aluminum', 'Sliding Door', 'a smooth sliding door that provided soft close when closing', '2026-03-30', 1, '1774846892_project5.jpg', 88.00),
(10, 'Slim Frame Linked Hanging Door', 'Aluminum', 'Folding Door', 'High cost‑performance folding doors and practical sliding doors. Ideal for kitchens, balconies, space separation, etc. Combines aesthetics, smooth operation, and durability.\\\\r\\\\n', '2026-05-12', 1, 'door_1779036504.jpg', 76.00),
(11, 'Slim Frame Sliding Door', 'Aluminum', 'Sliding Door', 'High cost‑performance folding doors and practical sliding doors. Ideal for kitchens, balconies, space separation, etc. Combines aesthetics, smooth operation, and durability', '2026-05-17', 1, 'product_1779116681_1.jpg', 73.00),
(12, 'Reeded Glass Aluminium Swing Door', 'Aluminum', 'Swing Door', 'Installation services for everyday household toilet doors and kitchen doors. Practical, durable, easy to clean, and adaptable to different space styles.\\\\r\\\\n', '2026-05-17', 1, '1779004939_672133295_1503866534780014_2402826916215001281_n.jpg', 93.00),
(13, 'Slim Frame Sliding Door ', 'Aluminum', 'Sliding Door', 'High cost‑performance folding doors and practical sliding doors. Ideal for kitchens, balconies, space separation, etc. Combines aesthetics, smooth operation, and durability.', '2026-05-17', 1, '1779010462_656808785_1488207826345885_7230838290352857884_n.jpg', 58.00),
(14, 'Folding Upward Window', 'Aluminum', 'Swing Door', 'Suitable for shopfronts, offices, and commercial spaces. Takes into account storefront image, ease of use, and basic safety requirements.', '2026-05-17', 1, '1779018001_684292675_1514393053727362_6471954994174484233_n.jpg', 60.00),
(15, '4 Panel Slim Frame Sliding Glass Door', 'Aluminum', 'Sliding Door', 'High cost‑performance folding doors and practical sliding doors. Ideal for kitchens, balconies, space separation, etc. Combines aesthetics, smooth operation, and durability', '2026-05-17', 1, '1779038808_518989887_1288609959639007_7206247889812177755_n.jpg', 85.00),
(18, 'Aluminium Slim Hanging Door', 'Aluminum', 'Sliding Door', 'Installation services for everyday household toilet doors and kitchen doors. Practical, durable, easy to clean, and adaptable to different space styles.', '2026-05-18', 0, 'product_1779118220_1.jpg', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`) VALUES
(1, 7, '1774846892_project5.jpg', 0, '2026-06-09 03:07:56'),
(2, 10, 'door_1779036504.jpg', 0, '2026-06-09 03:07:56'),
(3, 11, 'product_1779116681_1.jpg', 0, '2026-06-09 03:07:56'),
(4, 12, '1779004939_672133295_1503866534780014_2402826916215001281_n.jpg', 0, '2026-06-09 03:07:56'),
(5, 13, '1779010462_656808785_1488207826345885_7230838290352857884_n.jpg', 0, '2026-06-09 03:07:56'),
(6, 14, '1779018001_684292675_1514393053727362_6471954994174484233_n.jpg', 0, '2026-06-09 03:07:56'),
(7, 15, '1779038808_518989887_1288609959639007_7206247889812177755_n.jpg', 0, '2026-06-09 03:07:56'),
(8, 18, 'product_1779118220_1.jpg', 0, '2026-06-09 03:07:56'),
(9, 11, 'product_1779116681_2.jpg', 1, '2026-06-09 03:07:56'),
(10, 18, 'product_1779118220_2.jpg', 1, '2026-06-09 03:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `project_progress`
--

CREATE TABLE `project_progress` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `qtn_id` int(11) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  `progress_step` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_progress`
--

INSERT INTO `project_progress` (`id`, `customer_id`, `qtn_id`, `staff_id`, `progress_step`, `status`, `notes`, `updated_at`) VALUES
(84, 66, 149, 6, 'Deposit 50%', 'Completed', '[Manual completion by staff] ', '2026-06-20 10:16:49'),
(85, 66, 149, 6, 'Order', 'Completed', '[Manual completion by staff] ', '2026-06-20 10:15:31'),
(86, 66, 149, 6, 'Fabrication', 'Completed', '[Manual completion by staff] ', '2026-06-20 10:16:19'),
(87, 66, 149, 6, '30% on going job', 'Completed', 'Auto-completed after payment verification', '2026-06-20 10:17:56'),
(88, 64, 154, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 11:58:24'),
(89, 62, 159, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 14:48:56'),
(90, 66, 163, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 15:10:26'),
(91, 66, 168, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 15:33:48'),
(92, 66, 169, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 15:40:22'),
(93, 66, 170, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 15:45:25'),
(94, 66, 171, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 16:02:18'),
(95, 66, 174, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 16:09:51'),
(96, 66, 175, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 16:18:33'),
(97, 66, 176, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 16:42:28'),
(98, 66, 178, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 16:47:44'),
(99, 66, 181, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 17:22:19'),
(100, 66, 182, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 17:36:03'),
(101, 66, 183, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 17:56:05'),
(102, 66, 184, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 18:00:17'),
(103, 66, 186, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-20 18:09:42'),
(104, 66, 185, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 01:54:28'),
(105, 66, 187, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 02:26:23'),
(106, 66, 188, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 02:38:00'),
(107, 66, 189, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 02:42:20'),
(108, 66, 190, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 02:48:38'),
(109, 66, 191, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 02:58:46'),
(110, 66, 192, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 03:12:06'),
(111, 66, 193, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 03:30:42'),
(112, 66, 194, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 03:32:06'),
(113, 66, 195, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 03:48:59'),
(114, 66, 195, 6, '30% on going job', 'Completed', 'Auto-completed after payment verification', '2026-06-21 03:50:01'),
(115, 66, 196, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 03:56:47'),
(116, 66, 198, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 04:01:05'),
(117, 66, 199, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 04:06:44'),
(118, 66, 200, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 04:18:11'),
(119, 66, 204, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 05:08:20'),
(120, 66, 201, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 05:12:30'),
(121, 66, 203, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 05:16:05'),
(122, 66, 205, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 10:13:16'),
(123, 66, 206, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-21 15:53:09'),
(124, 66, 208, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification\n[Manual completion by staff] ', '2026-06-22 08:30:19'),
(125, 66, 210, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-22 14:38:21'),
(126, 66, 211, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-22 14:45:23'),
(127, 66, 211, 6, '30% on going job', 'Completed', 'Auto-completed after payment verification', '2026-06-22 14:50:29'),
(128, 66, 211, 6, '20% complete job', 'Completed', 'Auto-completed after payment verification', '2026-06-22 14:52:58'),
(129, 66, 211, 6, 'Order', 'Completed', '[Manual completion by staff] ', '2026-06-22 15:12:56'),
(130, 66, 211, 6, 'Fabrication', 'Completed', '[Manual completion by staff] ', '2026-06-22 15:13:00'),
(131, 66, 211, 6, 'Installation', 'Completed', '[Manual completion by staff] ', '2026-06-22 15:13:03'),
(132, 66, 212, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification (auto-fill)', '2026-06-22 15:18:34'),
(133, 66, 212, 6, 'Order', 'Completed', 'Auto-completed after payment verification (auto-fill)', '2026-06-22 15:18:34'),
(134, 66, 212, 6, 'Fabrication', 'Completed', 'Auto-completed after payment verification (auto-fill)', '2026-06-22 15:18:34'),
(135, 66, 212, 6, 'Installation', 'Completed', 'Auto-completed after payment verification (auto-fill)', '2026-06-22 15:18:34'),
(136, 66, 212, 6, '30% on going job', 'Completed', 'Auto-completed after payment verification (auto-fill)', '2026-06-22 15:18:34'),
(137, 66, 213, 6, 'Deposit 50%', 'Completed', 'Auto-completed after payment verification', '2026-06-22 16:42:12'),
(138, 66, 213, 6, '30% on going job', 'Completed', 'Auto-completed after payment verification', '2026-06-22 16:42:56'),
(139, 66, 213, 6, '20% complete job', 'Completed', 'Auto-completed after payment verification', '2026-06-22 16:44:33'),
(140, 66, 213, 6, 'Order', 'Completed', '[Manual completion by staff] ', '2026-06-22 16:44:48'),
(141, 66, 213, 6, 'Fabrication', 'Completed', '[Manual completion by admin] ', '2026-06-22 19:23:42'),
(142, 66, 212, 6, '20% complete job', 'Completed', '[Manual completion by admin] ', '2026-06-22 19:53:56');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `qtn_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `qtn_number` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Accepted','Rejected','Updated') DEFAULT 'Pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`qtn_id`, `customer_id`, `staff_id`, `qtn_number`, `file_path`, `total_amount`, `status`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(211, 66, 6, 'QT-20260622-941', 'uploads/quotations/QT-20260622-941_1782139474.pdf', 19703.25, 'Accepted', NULL, '2026-06-22 14:44:34', '2026-06-22 14:44:53'),
(212, 66, 6, 'QT-20260622-502', 'uploads/quotations/QT-20260622-502_1782141371.pdf', 3138.75, 'Accepted', NULL, '2026-06-22 15:16:11', '2026-06-22 15:17:26'),
(213, 66, 6, 'QT-20260622-254', 'uploads/quotations/QT-20260622-254_1782146500.pdf', 1953.00, 'Accepted', NULL, '2026-06-22 16:41:39', '2026-06-22 16:41:48');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_history`
--

CREATE TABLE `quotation_history` (
  `history_id` int(11) NOT NULL,
  `qtn_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `action` enum('created','updated','status_change','pdf_regenerated') NOT NULL,
  `old_status` enum('Pending','Accepted','Rejected','Updated') DEFAULT NULL,
  `new_status` enum('Pending','Accepted','Rejected','Updated') DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_history`
--

INSERT INTO `quotation_history` (`history_id`, `qtn_id`, `staff_id`, `action`, `old_status`, `new_status`, `rejection_reason`, `file_path`, `total_amount`, `notes`, `created_at`) VALUES
(158, 211, 6, 'created', NULL, 'Pending', NULL, 'uploads/quotations/QT-20260622-941_1782139474.pdf', 19703.25, 'Quotation created.', '2026-06-22 14:44:39'),
(159, 212, 6, 'created', NULL, 'Pending', NULL, 'uploads/quotations/QT-20260622-502_1782141371.pdf', 3138.75, 'Quotation created.', '2026-06-22 15:16:16'),
(160, 213, 6, 'created', NULL, 'Pending', NULL, 'uploads/quotations/QT-20260622-254_1782146500.pdf', 1953.00, 'Quotation created.', '2026-06-22 16:41:44');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `item_id` int(11) NOT NULL,
  `qtn_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `area` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `width_mm` int(11) DEFAULT NULL,
  `height_mm` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`item_id`, `qtn_id`, `description`, `quantity`, `area`, `width_mm`, `height_mm`, `unit_price`, `discount`, `product_id`) VALUES
(270, 162, '3\'6\" (W) × 8\'0\" (H)', 1.00, 28.0000, 1000, 2500, 75.00, 0.00, 18),
(272, 159, '3\'6\" (W) × 7\'0\" (H)', 1.00, 24.0000, 1000, 2100, 75.00, 0.00, 15),
(273, 163, '5\'0\" (W) × 7\'0\" (H)', 1.00, 35.0000, 1500, 2100, 75.00, 0.00, 14),
(274, 163, '5\'0\" (W) × 2\'6\" (H)', 1.00, 12.0000, 1500, 700, 75.00, 0.00, 14),
(275, 163, '5\'0\" (W) × 7\'0\" (H)', 1.00, 35.0000, 1500, 2100, 75.00, 0.00, 14),
(276, 163, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(277, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(278, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(279, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(280, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(281, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(282, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(283, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(284, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(285, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(286, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(287, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(288, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(289, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(290, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(291, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(292, 164, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(293, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(294, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(295, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(296, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(297, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(298, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(299, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(300, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(301, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(302, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(303, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(304, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(305, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(306, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(307, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(308, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(309, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(310, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(311, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(312, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(313, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(314, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(315, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(316, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(317, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(318, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(319, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(320, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(321, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(322, 165, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(323, 166, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(324, 167, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(325, 167, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(326, 168, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 15),
(327, 168, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 15),
(328, 169, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(329, 169, '3\'6\" (W) × 7\'0\" (H)', 1.00, 24.0000, 1000, 2100, 75.00, 0.00, 14),
(330, 169, '3\'0\" (W) × 6\'0\" (H)', 1.00, 18.0000, 900, 1888, 75.00, 0.00, 14),
(331, 169, '3\'0\" (W) × 10\'6\" (H)', 1.00, 31.0000, 900, 3213, 75.00, 0.00, 18),
(332, 169, '2\'0\" (W) × 7\'0\" (H)', 1.00, 14.0000, 600, 2100, 75.00, 0.00, 18),
(333, 170, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 15),
(334, 170, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 15),
(335, 170, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 15),
(336, 170, '2\'6\" (W) × 16\'6\" (H)', 1.00, 41.0000, 700, 5000, 75.00, 0.00, 7),
(337, 170, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(338, 171, '3\'6\" (W) × 7\'0\" (H)', 1.00, 24.0000, 1000, 2100, 75.00, 0.00, 7),
(339, 171, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(340, 171, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(341, 171, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(342, 172, '18\'0\" (W) × 2\'0\" (H)', 1.00, 36.0000, 5462, 543, 75.00, 0.00, 18),
(343, 172, '1\'6\" (W) × 0\'0\" (H)', 1.00, 0.0000, 423, 34, 75.00, 0.00, 18),
(344, 172, '76\'0\" (W) × 2\'0\" (H)', 1.00, 152.0000, 23123, 543, 75.00, 0.00, 18),
(345, 172, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(346, 172, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 10),
(350, 173, '3\'0\" (W) × 11\'6\" (H)', 1.00, 34.0000, 950, 3500, 75.00, 0.00, 18),
(351, 173, '3\'6\" (W) × 7\'0\" (H)', 1.00, 24.0000, 1050, 2100, 75.00, 0.00, 18),
(352, 173, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(353, 174, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 950, 1050, 75.00, 0.00, 7),
(354, 174, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(355, 175, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 950, 1050, 75.00, 0.00, 18),
(356, 175, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 950, 1050, 75.00, 0.00, 18),
(357, 175, '3\'6\" (W) × 10\'0\" (H)', 1.00, 35.0000, 1050, 3050, 75.00, 0.00, 18),
(358, 176, '3\'0\" (W) × 705\'6\" (H)', 1.00, 2116.0000, 950, 215000, 75.00, 0.00, 14),
(359, 177, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 950, 1050, 75.00, 0.00, 14),
(360, 178, '3\'0\" (W) × 6\'6\" (H)', 1.00, 19.0000, 950, 2050, 75.00, 0.00, 12),
(361, 178, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 12),
(362, 178, '6\'6\" (W) × 7\'0\" (H)', 1.00, 45.0000, 2000, 2100, 75.00, 0.00, 12),
(363, 178, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 12),
(370, 180, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(371, 179, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 950, 1050, 75.00, 0.00, 7),
(372, 179, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 7),
(373, 179, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 13),
(374, 181, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 7),
(375, 181, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 7),
(376, 181, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 7),
(377, 182, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 950, 1050, 75.00, 0.00, 14),
(378, 182, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(380, 183, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 18),
(381, 183, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 18),
(382, 183, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 18),
(383, 184, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 18),
(384, 185, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 7),
(385, 185, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2060, 75.00, 0.00, 7),
(386, 186, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.0000, 900, 1050, 75.00, 0.00, 18),
(391, 187, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 18),
(392, 187, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 18),
(393, 187, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 18),
(394, 187, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 10),
(395, 188, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(396, 188, '1\'6\" (W) × 3\'6\" (H)', 1.00, 5.2500, 460, 1050, 75.00, 0.00, 14),
(397, 188, '1\'0\" (W) × 3\'6\" (H)', 1.00, 3.5000, 345, 1050, 75.00, 0.00, 14),
(398, 189, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 14),
(399, 189, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 14),
(400, 189, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 14),
(401, 190, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 18),
(402, 190, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 18),
(403, 190, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 18),
(404, 191, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 15),
(405, 191, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 15),
(406, 191, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 15),
(407, 192, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 657.00, 18),
(408, 192, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 657.00, 18),
(409, 192, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 657.00, 18),
(410, 192, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 657.00, 18),
(411, 192, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(412, 193, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 888.00, 14),
(413, 193, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(414, 193, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(415, 193, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(416, 194, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(417, 195, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 700.00, 14),
(418, 195, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 14),
(419, 195, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 14),
(424, 196, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 7),
(425, 196, '19\'6\" (W) × 7\'0\" (H)', 1.00, 136.5000, 6000, 2100, 75.00, 0.00, 7),
(426, 197, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(427, 197, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(428, 197, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 14),
(429, 198, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 500.00, 7),
(430, 198, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 7),
(431, 198, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 7),
(435, 199, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 609.00, 7),
(436, 199, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 7),
(437, 199, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1050, 75.00, 0.00, 7),
(438, 200, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 544.00, 18),
(439, 200, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 18),
(440, 200, '3\'0\" (W) × 3\'6\" (H)', 1.00, 10.5000, 900, 1052, 75.00, 0.00, 18),
(447, 201, '4\'0\" (W) × 6\'6\" (H)', 1.00, 26.0000, 1200, 2050, 75.00, 700.00, 7),
(448, 201, '6\'6\" (W) × 4\'6\" (H)', 1.00, 29.2500, 2000, 1423, 75.00, 0.00, 7),
(449, 201, '3\'0\" (W) × 6\'6\" (H)', 1.00, 19.5000, 900, 2050, 75.00, 0.00, 7),
(450, 201, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 80.00, 600.00, 11),
(451, 201, '3\'0\" (W) × 2\'6\" (H)', 1.00, 7.5000, 900, 700, 80.00, 0.00, 11),
(452, 201, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2132, 344.00, 0.00, 14),
(461, 204, '3\'0\" (W) × 7\'0\" (H)(In Kitchen)', 1.00, 21.0000, 900, 2100, 75.00, 500.00, 14),
(462, 204, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(463, 202, '3\'6\" (W) × 7\'0\" (H)', 1.00, 24.5000, 1050, 2100, 75.00, 600.00, 18),
(464, 202, '4\'0\" (W) × 7\'0\" (H) (In Kitchen)', 1.00, 28.0000, 1220, 2100, 75.00, 0.00, 18),
(465, 202, '3\'0\" (W) × 11\'0\" (H) (In Store Room)', 1.00, 33.0000, 900, 3300, 75.00, 0.00, 18),
(466, 202, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(469, 203, '3\'0\" (W) × 7\'0\" (H) (In Kitchen)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(470, 203, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 18),
(473, 205, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 71.00, 15),
(474, 205, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 15),
(475, 206, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 75.00, 0.00, 14),
(476, 207, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 324.00, 0.00, 18),
(477, 208, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 85.00, 0.00, 15),
(478, 209, '3\'6\" (W) × 7\'6\" (H)', 1.00, 26.2500, 1050, 2233, 60.00, 600.00, 14),
(487, 210, '7\'6\" (W) × 11\'6\" (H) (In Kichen)', 1.00, 86.2500, 2313, 3462, 76.00, 699.00, 10),
(488, 210, '15\'0\" (W) × 4\'6\" (H)', 1.00, 67.5000, 4522, 1323, 76.00, 0.00, 10),
(489, 210, '14\'0\" (W) × 3\'6\" (H)', 1.00, 49.0000, 4235, 1123, 85.00, 500.00, 15),
(490, 210, '4\'0\" (W) × 14\'0\" (H)', 1.00, 56.0000, 1233, 4234, 85.00, 0.00, 15),
(491, 211, '5\'0\" (W) × 10\'6\" (H)', 1.00, 52.5000, 1504, 3212, 85.00, 0.00, 15),
(492, 211, '13\'6\" (W) × 10\'6\" (H)', 1.00, 141.7500, 4124, 3125, 85.00, 0.00, 15),
(493, 211, '10\'6\" (W) × 4\'0\" (H)', 1.00, 42.0000, 3244, 1234, 76.00, 0.00, 10),
(494, 212, '4\'6\" (W) × 7\'6\" (H)', 1.00, 33.7500, 1324, 2353, 93.00, 0.00, 12),
(495, 213, '3\'0\" (W) × 7\'0\" (H)', 1.00, 21.0000, 900, 2100, 93.00, 0.00, 12);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `staff_name` varchar(100) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT '../images/default-avatar.png',
  `appointment_calendar` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `password`, `staff_name`, `status`, `email`, `phone`, `profile_image`, `appointment_calendar`, `updated_at`, `address`) VALUES
(6, '$2y$10$a3o1.TCG4RVXKVQPO.MeUefiKjHgLdoDiTbrXKvgLqq7hUnmqcCnu', 'Sim Heng Jing', 1, 'hengjingchen102@gmail.com', '0137787266', 'uploads/staff_avatars/staff_6_1782153416.webp', 'https://calendar.app.google/G6NyZrMYhSszPmeE6', '2026-06-22 18:36:56', '22，Jalan Eco Cascadia 9/8, Taman Eco Cascadia\r\n'),
(15, '$2y$10$UWqx.pUXV3cILdhWJQufgOfcLIpy6I/4PKDfdpLHSr4hHKPlDzkGy', 'Sim Heng Ping', 1, 'simhengping@gmail.com', '0137519660', 'uploads/staff_avatars/default_avatar.png', 'https://calendar.app.google/Cn9fpqqfKBCHEyGr9', '2026-06-22 19:07:47', '19，Jalan Eco Cascadia 9/8, Taman Eco Cascadia'),
(18, '$2y$10$JJMcDYaeiG57MiGCfrVj4efZ7IoKFLLwDD3V/1zHr1tf9Xux05C2q', 'Ng Yong Lok', 1, 'jmslok1234@gmail.com', '0104331663', 'uploads/staff_avatars/default_avatar.png', 'https://calendar.app.google/sfiWtgpYopYd7Mm47', '2026-06-22 19:07:50', '34，Jalan Eco Cascadia 9/8, Taman Eco Cascadia');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`inv_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `qtn_id` (`qtn_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `idx_paid_date` (`paid_date`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `qtn_id` (`qtn_id`,`stage`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `project_progress`
--
ALTER TABLE `project_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `idx_qtn_id` (`qtn_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`qtn_id`),
  ADD KEY `fk_cust_qtn` (`customer_id`),
  ADD KEY `fk_staff_qtn` (`staff_id`);

--
-- Indexes for table `quotation_history`
--
ALTER TABLE `quotation_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `qtn_id` (`qtn_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `qtn_id` (`qtn_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `inv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `project_progress`
--
ALTER TABLE `project_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `qtn_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `quotation_history`
--
ALTER TABLE `quotation_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=496;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_qtn` FOREIGN KEY (`qtn_id`) REFERENCES `quotations` (`qtn_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD CONSTRAINT `fk_payment_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_records_qtn` FOREIGN KEY (`qtn_id`) REFERENCES `quotations` (`qtn_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_records_ibfk_1` FOREIGN KEY (`qtn_id`) REFERENCES `quotations` (`qtn_id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `fk_cust_qtn` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_staff_qtn` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_history`
--
ALTER TABLE `quotation_history`
  ADD CONSTRAINT `fk_history_qtn` FOREIGN KEY (`qtn_id`) REFERENCES `quotations` (`qtn_id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
