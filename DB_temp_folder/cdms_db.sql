-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 07, 2025 at 02:38 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cdms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address_line1` varchar(120) DEFAULT NULL,
  `address_line2` varchar(120) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state_province` varchar(80) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `phone`, `address_line1`, `address_line2`, `city`, `state_province`, `postal_code`, `created_at`, `updated_at`) VALUES
(1, 'Jamie', 'Ng', 'jamie.ng@example.com', '555-3001', NULL, NULL, 'St Paul', 'MN', '55105', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(2, 'Taylor', 'Kim', 'taylor.kim@example.com', '555-3002', NULL, NULL, 'Minneapolis', 'MN', '55401', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(3, 'Riley', 'Ortiz', 'riley.ortiz@example.com', '555-3003', NULL, NULL, 'Maplewood', 'MN', '55109', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(4, 'Avery', 'Johnson', 'avery.johnson@example.com', '555-3101', '124 Oak St', NULL, 'St Paul', 'MN', '55105', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(5, 'Jordan', 'Smith', 'jordan.smith@example.com', '555-3102', '451 Maple Ave', NULL, 'Minneapolis', 'MN', '55403', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(6, 'Cameron', 'Davis', 'cameron.davis@example.com', '555-3103', '67 Elm Rd', NULL, 'Woodbury', 'MN', '55125', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(7, 'Taylor', 'Nguyen', 'taylor.nguyen@example.com', '555-3104', '98 Lakeview Dr', NULL, 'Eagan', 'MN', '55122', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(8, 'Morgan', 'Lopez', 'morgan.lopez@example.com', '555-3105', '220 Ridge Ln', NULL, 'Roseville', 'MN', '55113', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(9, 'Riley', 'Baker', 'riley.baker@example.com', '555-3106', '18 Pine Ct', NULL, 'Maplewood', 'MN', '55109', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(10, 'Sydney', 'Martinez', 'sydney.martinez@example.com', '555-3107', '712 Birch Blvd', NULL, 'Bloomington', 'MN', '55420', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(11, 'Drew', 'Gonzalez', 'drew.gonzalez@example.com', '555-3108', '501 Highland Rd', NULL, 'Inver Grove Heights', 'MN', '55076', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(12, 'Payton', 'Lee', 'payton.lee@example.com', '555-3109', '330 Main St', NULL, 'Stillwater', 'MN', '55082', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(13, 'Jamie', 'Rivera', 'jamie.rivera@example.com', '555-3110', '27 Sunset Dr', NULL, 'Cottage Grove', 'MN', '55016', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(14, 'Alex', 'Foster', 'alex.foster@example.com', '555-3111', '8 Meadow Ln', NULL, 'Oakdale', 'MN', '55128', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(15, 'Quinn', 'Miller', 'quinn.miller@example.com', '555-3112', '46 Cedar St', NULL, 'Hudson', 'WI', '54016', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(16, 'Casey', 'Hernandez', 'casey.hernandez@example.com', '555-3113', '93 Walnut Ave', NULL, 'Mendota Heights', 'MN', '55120', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(17, 'Skyler', 'Brown', 'skyler.brown@example.com', '555-3114', '21 Hillcrest Rd', NULL, 'Burnsville', 'MN', '55337', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(18, 'Logan', 'Nelson', 'logan.nelson@example.com', '555-3115', '74 Elmwood Ave', NULL, 'Lakeville', 'MN', '55044', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(19, 'Kendall', 'Moore', 'kendall.moore@example.com', '555-3116', '632 Oak St', NULL, 'Richfield', 'MN', '55423', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(20, 'Jesse', 'Perez', 'jesse.perez@example.com', '555-3117', '129 Willow Ln', NULL, 'Apple Valley', 'MN', '55124', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(21, 'Dakota', 'Clark', 'dakota.clark@example.com', '555-3118', '235 Linden Blvd', NULL, 'Plymouth', 'MN', '55446', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(22, 'Bailey', 'Johnson', 'bailey.johnson@example.com', '555-3119', '18 Birchwood Ct', NULL, 'Edina', 'MN', '55435', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(23, 'Reese', 'King', 'reese.king@example.com', '555-3120', '405 Walnut Dr', NULL, 'Maple Grove', 'MN', '55369', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(24, 'Aidan', 'Scott', 'aidan.scott@example.com', '555-3121', '9 Greenway Blvd', NULL, 'Woodbury', 'MN', '55125', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(25, 'Harper', 'Adams', 'harper.adams@example.com', '555-3122', '53 Lake Rd', NULL, 'Oakdale', 'MN', '55128', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(26, 'Emerson', 'Brooks', 'emerson.brooks@example.com', '555-3123', '102 River St', NULL, 'Stillwater', 'MN', '55082', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(27, 'Finley', 'Wright', 'finley.wright@example.com', '555-3124', '840 Summit Ave', NULL, 'St Paul', 'MN', '55105', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(28, 'Charlie', 'Thompson', 'charlie.thompson@example.com', '555-3125', '11 Birch Dr', NULL, 'Eagan', 'MN', '55122', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(29, 'Micah', 'Allen', 'micah.allen@example.com', '555-3126', '17 Forest Ave', NULL, 'Maplewood', 'MN', '55109', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(30, 'Jordan', 'Young', 'jordan.young@example.com', '555-3127', '555 Prairie Ln', NULL, 'Woodbury', 'MN', '55125', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(31, 'River', 'Torres', 'river.torres@example.com', '555-3128', '212 Cedar Ct', NULL, 'Cottage Grove', 'MN', '55016', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(32, 'Dakota', 'Ward', 'dakota.ward@example.com', '555-3129', '99 Highland Ave', NULL, 'Hudson', 'WI', '54016', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(33, 'Elliot', 'Peterson', 'elliot.peterson@example.com', '555-3130', '78 Maple Ct', NULL, 'Lake Elmo', 'MN', '55042', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(34, 'Rowan', 'Mitchell', 'rowan.mitchell@example.com', '555-3131', '24 Brookside Ln', NULL, 'St Paul', 'MN', '55105', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(35, 'Sawyer', 'Cruz', 'sawyer.cruz@example.com', '555-3132', '60 Lincoln Ave', NULL, 'Minneapolis', 'MN', '55408', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(36, 'Parker', 'Evans', 'parker.evans@example.com', '555-3133', '200 5th St', NULL, 'Bloomington', 'MN', '55420', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(37, 'Reagan', 'Collins', 'reagan.collins@example.com', '555-3134', '73 Meadow Dr', NULL, 'Richfield', 'MN', '55423', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(38, 'Carter', 'Bell', 'carter.bell@example.com', '555-3135', '12 Oak Cir', NULL, 'Roseville', 'MN', '55113', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(39, 'Phoenix', 'Garcia', 'phoenix.garcia@example.com', '555-3136', '310 Sunrise Ct', NULL, 'Burnsville', 'MN', '55337', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(40, 'Sasha', 'Hughes', 'sasha.hughes@example.com', '555-3137', '420 Ridge Ln', NULL, 'Lakeville', 'MN', '55044', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(41, 'Avery', 'Turner', 'avery.turner@example.com', '555-3138', '29 Crest Dr', NULL, 'Apple Valley', 'MN', '55124', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(42, 'Dylan', 'Flores', 'dylan.flores@example.com', '555-3139', '15 Lakeview Ct', NULL, 'Eagan', 'MN', '55122', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(43, 'Hayden', 'Price', 'hayden.price@example.com', '555-3140', '88 Pine Blvd', NULL, 'Maplewood', 'MN', '55109', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(44, 'Morgan', 'Bennett', 'morgan.bennett@example.com', '555-3141', '22 Birch Ln', NULL, 'Woodbury', 'MN', '55125', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(45, 'Quinn', 'Howard', 'quinn.howard@example.com', '555-3142', '135 Summit Blvd', NULL, 'St Paul', 'MN', '55105', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(46, 'Taylor', 'Reed', 'taylor.reed@example.com', '555-3143', '59 Elmwood Ln', NULL, 'Minneapolis', 'MN', '55403', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(47, 'Jordan', 'Murphy', 'jordan.murphy@example.com', '555-3144', '48 Willow St', NULL, 'Roseville', 'MN', '55113', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(48, 'Riley', 'Bailey', 'riley.bailey@example.com', '555-3145', '25 Valley Rd', NULL, 'Maple Grove', 'MN', '55369', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(49, 'Cameron', 'Gray', 'cameron.gray@example.com', '555-3146', '70 Walnut Ave', NULL, 'Edina', 'MN', '55435', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(50, 'Dakota', 'Ross', 'dakota.ross@example.com', '555-3147', '41 Oakwood Ct', NULL, 'Eagan', 'MN', '55122', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(51, 'Avery', 'Watson', 'avery.watson@example.com', '555-3148', '87 Park Blvd', NULL, 'Lake Elmo', 'MN', '55042', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(52, 'Jamie', 'Parker', 'jamie.parker@example.com', '555-3149', '66 Hill St', NULL, 'Hudson', 'WI', '54016', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(53, 'Skyler', 'Cook', 'skyler.cook@example.com', '555-3150', '33 Elm Cir', NULL, 'Woodbury', 'MN', '55125', '2025-10-16 20:42:04', '2025-10-16 20:42:04'),
(54, 'John', 'Doe', 'john.doe@gmail.com', '555-3001', NULL, NULL, 'St Paul', 'MN', '55105', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(55, 'Jane', 'Doe', 'jane.doe@example.com', '555-3001', NULL, NULL, 'St Paul', 'MN', '55105', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(56, 'Bryan', 'Anderson', 'bryan.anderson@example.com', '555-3001', NULL, NULL, 'St Paul', 'MN', '55105', '2025-09-30 23:59:44', '2025-09-30 23:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('sales','manager','admin') NOT NULL DEFAULT 'sales',
  `hire_date` date NOT NULL DEFAULT curdate(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `role`, `hire_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Administrator', 'admin@cdms.com', 'test123', '555-1234', 'admin', '2023-06-01', 1, '2025-09-30 23:59:44', '2025-10-16 00:51:40'),
(2, 'Morgan', 'Lee', 'morgan.lee@cdms.com', 'test123', '555-2002', 'manager', '2021-03-15', 1, '2025-09-30 23:59:44', '2025-10-16 20:11:16'),
(3, 'Sam', 'Patel', 'sam.patel@cdms.com', 'test123', '555-2003', 'sales', '2020-10-05', 1, '2025-09-30 23:59:44', '2025-10-16 20:11:34'),
(15, 'Andrew', 'Hawkinson', 'hawk@cdms.com', 'password123', '555-1234', 'admin', '2021-03-01', 1, '2025-09-30 23:59:44', '2025-10-16 00:51:40'),
(17, 'John', 'Smith', 'john.smith@cdms.com', '$2y$10$hToGCoqdRQys2eLQ7FmXb.dlZ/2UuikK2DL6D7/xRUEn.WEE00mIa', NULL, 'sales', '2025-10-16', 1, '2025-10-17 00:37:41', '2025-10-17 00:37:41'),
(18, 'Xamong', 'Thao', 'x.thao@cdms.com', '$2y$10$yup9lLwDqsbKWEFfsP6qJOrE59bdJWfVGdTe4E3gsxALMPDAFSMcm', NULL, 'admin', '2025-10-16', 1, '2025-10-17 00:48:24', '2025-10-17 00:48:24'),
(19, 'Jimmy', 'Taiwo', 'j.taiwo@cdms.com', '$2y$10$NREyLarAAeBUFgmbj97DweFKPrBDaxRP2Th84hXrBJM6yk8ZuK/1C', NULL, 'admin', '2025-10-16', 1, '2025-10-17 00:48:55', '2025-10-17 00:48:55'),
(20, 'Bryan', 'Timmers', 'b.timmers@cdms.com', '$2y$10$BDSPLIzMju.hSEcIqzXDN.7tDE7ciA9fiLPpJdWP/TyCURPQ/rFNy', NULL, 'manager', '2025-10-16', 1, '2025-10-17 00:49:22', '2025-10-17 00:49:22'),
(21, 'Mitch', 'Rapp', 'm.rapp@cdms.com', '$2y$10$7YJN9RoK9TckrkgOtf1G9OvPhx2EZWI1LnpjAiqisMlS8NWyJ1KGy', NULL, 'sales', '2025-10-16', 1, '2025-10-17 00:50:39', '2025-10-17 00:50:39'),
(22, 'Cassian', 'Andor', 'c.andor@cdms.com', '$2y$10$ZYSWnbxf9VLTKmryl2BdYuxo/rZXbSRBtt0Ra1uqG1UvCfA7rzemK', NULL, 'sales', '2025-10-16', 1, '2025-10-17 00:51:07', '2025-10-17 00:51:07');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `created_by_employee_id` int(11) DEFAULT NULL,
  `type` enum('test_drive','hold') NOT NULL DEFAULT 'test_drive',
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','canceled','expired') NOT NULL DEFAULT 'pending',
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `vehicle_id`, `customer_id`, `created_by_employee_id`, `type`, `start_datetime`, `end_datetime`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(20250929, 1, 1, 22, 'test_drive', '2025-07-01 10:00:00', '2025-07-01 10:45:00', 'pending', 'Customer prefers morning', '2025-09-30 23:59:44', '2025-10-17 01:24:31'),
(20251021, 17, 29, 22, 'test_drive', '2025-06-16 09:30:00', '2025-06-16 10:45:00', 'expired', 'Customer prefers morning', '2025-09-30 23:59:44', '2025-10-17 01:24:20'),
(20251022, 75, 11, 17, 'hold', '2025-09-30 11:00:00', '2025-09-23 10:45:00', 'confirmed', 'Customer will be in around lunchtime', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(20251023, 43, 32, 17, 'test_drive', '2025-09-30 17:30:00', '2025-09-23 18:00:00', 'confirmed', 'Customer will be in around dinner', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(20251024, 45, 35, 17, 'hold', '2025-10-16 09:30:00', '2025-10-16 10:45:00', 'completed', '', '2025-09-30 23:59:44', '2025-10-17 01:25:42'),
(20251025, 58, 55, 21, 'hold', '2025-10-01 17:30:00', '2025-10-01 18:00:00', 'confirmed', 'Customer will be in around dinner', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(20251026, 37, 56, 1, 'test_drive', '2025-11-07 10:30:00', '2025-11-07 11:15:00', 'pending', NULL, '2025-11-06 15:42:10', '2025-11-06 15:42:10'),
(20251027, 61, 25, 22, 'test_drive', '2025-11-08 09:42:00', '2025-11-08 10:27:00', 'pending', 'This is a test', '2025-11-06 15:42:54', '2025-11-06 15:42:54'),
(20251028, 61, 29, 22, 'test_drive', '2025-11-06 10:29:00', '2025-11-06 11:14:00', 'confirmed', 'test 3', '2025-11-06 16:29:21', '2025-11-07 00:22:12'),
(20251029, 32, 44, 15, 'test_drive', '2025-11-06 19:16:00', '2025-11-06 20:01:00', 'pending', 'Customer for test drive', '2025-11-07 01:17:15', '2025-11-07 01:17:15');

--
-- Triggers `reservations`
--
DELIMITER $$
CREATE TRIGGER `trg_reservation_confirmed_after_ins` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
  IF NEW.status = 'confirmed' THEN
    UPDATE vehicles
      SET status = 'reserved', updated_at = CURRENT_TIMESTAMP
      WHERE vehicle_id = NEW.vehicle_id AND status = 'available';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_reservation_status_update` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
  IF OLD.status = 'confirmed' AND NEW.status IN ('canceled','expired','completed') THEN
    UPDATE vehicles v
      LEFT JOIN sales s ON s.vehicle_id = v.vehicle_id
    SET v.status = IF(s.sale_id IS NULL, 'available', v.status),
        v.updated_at = CURRENT_TIMESTAMP
    WHERE v.vehicle_id = NEW.vehicle_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_date` date NOT NULL DEFAULT curdate(),
  `payment_method` enum('cash','finance','lease','other') NOT NULL DEFAULT 'finance',
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `vehicle_id`, `customer_id`, `employee_id`, `sale_price`, `sale_date`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 31, 17, 23250.00, '2025-07-12', 'cash', 'Approved at 2.9% APR, 72 month', '2025-09-30 23:59:44', '2025-10-17 01:23:31'),
(2, 10, 23, 17, 14000.00, '2025-09-18', 'finance', 'Approved at 4.9% APR', '2025-09-30 23:59:44', '2025-10-17 01:28:42'),
(3, 62, 25, 22, 50000.00, '2025-11-05', 'finance', NULL, '2025-11-06 02:12:50', '2025-11-06 02:12:50'),
(6, 51, 38, 22, 34990.00, '2025-11-06', 'finance', 'test sale', '2025-11-07 01:16:05', '2025-11-07 01:16:05');

--
-- Triggers `sales`
--
DELIMITER $$
CREATE TRIGGER `trg_sale_after_ins` AFTER INSERT ON `sales` FOR EACH ROW BEGIN
  UPDATE vehicles
    SET status = 'sold', updated_at = CURRENT_TIMESTAMP
    WHERE vehicle_id = NEW.vehicle_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `vin` char(17) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `trim` varchar(50) DEFAULT NULL,
  `model_year` year(4) NOT NULL,
  `color` varchar(40) DEFAULT NULL,
  `body_style` varchar(40) DEFAULT NULL,
  `transmission` varchar(40) DEFAULT NULL,
  `fuel_type` varchar(40) DEFAULT NULL,
  `mileage` int(11) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('available','reserved','sold') NOT NULL DEFAULT 'available',
  `image_filename` varchar(255) DEFAULT NULL,
  `location` varchar(80) DEFAULT NULL,
  `listed_at` date NOT NULL DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `vin`, `make`, `model`, `trim`, `model_year`, `color`, `body_style`, `transmission`, `fuel_type`, `mileage`, `price`, `image_url`, `status`, `image_filename`, `location`, `listed_at`, `created_at`, `updated_at`) VALUES
(1, '1HGCM82633A004352', 'Honda', 'Civic', 'EX', '2021', 'Blue', 'Sedan', 'Automatic', 'Gasoline', 16500, 20500.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-09-30', '2025-09-30 23:59:44', '2025-10-16 20:07:34'),
(2, '2T3RFREV4JW123456', 'Toyota', 'RAV4', 'XLE', '2019', 'White', 'SUV', 'Automatic', 'Gasoline', 42000, 23850.00, 'https://source.unsplash.com/600x400/?suv,car', 'sold', 'SUV.png', 'Main Lot B', '2025-09-30', '2025-09-30 23:59:44', '2025-10-16 20:07:34'),
(3, '1FTFW1E55MFB12345', 'Ford', 'F-150', 'Lariat', '2021', 'Black', 'Truck', 'Automatic', 'Gasoline', 22000, 44990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Overflow Lot', '2025-09-30', '2025-09-30 23:59:44', '2025-10-16 20:07:34'),
(4, '5YJ3E1EA7KF123456', 'Tesla', 'Model 3', 'Long Range', '2020', 'Red', 'Sedan', 'Automatic', 'Electric', 31000, 28990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Showroom', '2025-09-30', '2025-09-30 23:59:44', '2025-10-16 20:07:34'),
(5, '3FAHP0HA9AR000101', 'Ford', 'Fusion', 'SE', '2018', 'Silver', 'Sedan', 'Automatic', 'Gasoline', 58500, 13990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(6, '3FA6P0K98LR000102', 'Ford', 'Fusion', 'Titanium', '2020', 'Blue', 'Sedan', 'Automatic', 'Hybrid', 31200, 19950.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(7, '1G1ZD5ST5MF000103', 'Chevrolet', 'Malibu', 'LT', '2021', 'Black', 'Sedan', 'Automatic', 'Gasoline', 22800, 18990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(8, '1GNEVGKW7MJ000104', 'Chevrolet', 'Traverse', 'LT', '2021', 'White', 'SUV', 'Automatic', 'Gasoline', 34100, 28990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(9, '1GYS4BKJ6PR000105', 'Cadillac', 'Escalade', 'Premium Luxury', '2023', 'Crystal White', 'SUV', 'Automatic', 'Gasoline', 9800, 87990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(10, '5NPEJ4J26JH000106', 'Hyundai', 'Sonata', 'Limited', '2018', 'Gray', 'Sedan', 'Automatic', 'Gasoline', 64200, 14950.00, 'https://source.unsplash.com/600x400/?sedan,car', 'sold', 'sedan.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-17 00:44:46'),
(11, '5NMS3CAD0MH000107', 'Hyundai', 'Santa Fe', 'SEL', '2021', 'Stormy Sea', 'SUV', 'Automatic', 'Gasoline', 28750, 25990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(12, 'KM8J3CA40NU000108', 'Hyundai', 'Tucson', 'SEL', '2022', 'Red', 'SUV', 'Automatic', 'Hybrid', 18900, 27990.00, 'https://source.unsplash.com/600x400/?suv,car', 'reserved', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(13, 'KNDPMAAC0M7000109', 'Kia', 'Sportage', 'LX', '2021', 'Snow White', 'SUV', 'Automatic', 'Gasoline', 25500, 21990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(14, '5XYRK4LF0PG000110', 'Kia', 'Telluride', 'EX', '2023', 'Dark Moss', 'SUV', 'Automatic', 'Gasoline', 12250, 41990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(15, '1N4BL4BV2MN000111', 'Nissan', 'Altima', 'S', '2021', 'Gun Metallic', 'Sedan', 'CVT', 'Gasoline', 30100, 17990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(16, 'JN8AT3BB3MW000112', 'Nissan', 'Rogue', 'SV', '2021', 'Pearl White', 'SUV', 'CVT', 'Gasoline', 27800, 22990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(17, '3N1AB8CV0NY000113', 'Nissan', 'Sentra', 'SV', '2022', 'Blue', 'Sedan', 'CVT', 'Gasoline', 16200, 18950.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(18, '1HGCV1F37MA000114', 'Honda', 'Accord', 'Sport', '2021', 'Sonic Gray', 'Sedan', 'Automatic', 'Gasoline', 24800, 25990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(19, '7FARS2H85PE000115', 'Honda', 'CR-V', 'EX-L', '2023', 'Platinum White', 'SUV', 'CVT', 'Hybrid', 9800, 35990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(20, 'JHMFL5G46PX000116', 'Honda', 'Accord', 'EX', '2023', 'Black', 'Sedan', 'Automatic', 'Hybrid', 7200, 32990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'reserved', 'sedan.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(21, 'WBA5R1C59LF000117', 'BMW', '530i', 'xDrive', '2020', 'Black Sapphire', 'Sedan', 'Automatic', 'Gasoline', 33400, 33990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(22, 'WBA13BJ04P7A00118', 'BMW', 'i4', 'eDrive40', '2023', 'Portimao Blue', 'Sedan', 'Automatic', 'Electric', 5200, 47990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(23, 'WAUEAAF40MN000119', 'Audi', 'A4', 'Premium', '2021', 'Glacier White', 'Sedan', 'Automatic', 'Gasoline', 21100, 29990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(24, 'WA1BNAFY8M2000120', 'Audi', 'Q5', 'Premium Plus', '2021', 'Navarra Blue', 'SUV', 'Automatic', 'Gasoline', 24500, 33950.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(25, 'WDDZF4JB2KA000121', 'Mercedes-Benz', 'E 300', 'Luxury', '2019', 'Polar White', 'Sedan', 'Automatic', 'Gasoline', 41200, 32990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(26, 'W1N0G8EB0MF000122', 'Mercedes-Benz', 'GLC 300', '4MATIC', '2021', 'Selenite Gray', 'SUV', 'Automatic', 'Gasoline', 23300, 37990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(27, '5YJYGDEE7LF000123', 'Tesla', 'Model Y', 'Long Range', '2020', 'Midnight Silver', 'SUV', 'Automatic', 'Electric', 28500, 36990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(28, '5YJSA1E25KF000124', 'Tesla', 'Model S', 'Long Range', '2019', 'Red Multi-Coat', 'Hatchback', 'Automatic', 'Electric', 34800, 44990.00, 'https://source.unsplash.com/600x400/?hatchback,car', 'available', 'hatchback.jpg', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(29, '4T1G11AK1NU000125', 'Toyota', 'Camry', 'SE', '2022', 'Celestial Silver', 'Sedan', 'Automatic', 'Gasoline', 17100, 25990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'sold', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-11-06 16:29:47'),
(30, 'JTMB6RFV7MD000126', 'Toyota', 'RAV4', 'XLE', '2021', 'Magnetic Gray', 'SUV', 'Automatic', 'Hybrid', 23300, 30990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(31, 'JTDKAMFU8N3000127', 'Toyota', 'Prius', 'LE', '2022', 'Sea Glass', 'Hatchback', 'Automatic', 'Hybrid', 14500, 24950.00, 'https://source.unsplash.com/600x400/?hatchback,car', 'available', 'hatchback.jpg', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(32, '1FT8W3BT5NE000128', 'Ford', 'F-250', 'Lariat', '2022', 'Oxford White', 'Truck', 'Automatic', 'Diesel', 21800, 58990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(33, '1FTEW1EP6PK000129', 'Ford', 'F-150', 'XLT', '2023', 'Carbonized Gray', 'Truck', 'Automatic', 'Gasoline', 8800, 42990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'reserved', 'truck.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(34, '1C4RJFBG8MC000130', 'Jeep', 'Grand Cherokee', 'Limited', '2021', 'Velvet Red', 'SUV', 'Automatic', 'Gasoline', 29400, 32990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(35, '1C6SRFJT1NN000131', 'Ram', '1500', 'Laramie', '2022', 'Patriot Blue', 'Truck', 'Automatic', 'Gasoline', 22100, 46990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(36, '1GTP9EEL6NZ000132', 'GMC', 'Canyon', 'AT4', '2022', 'Onyx Black', 'Truck', 'Automatic', 'Gasoline', 19800, 37990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(37, '1GKS2DKL5PR000133', 'GMC', 'Yukon', 'Denali', '2023', 'White Frost', 'SUV', 'Automatic', 'Gasoline', 11500, 76990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(38, '3VW5T7AU6MM000134', 'Volkswagen', 'Jetta', 'SE', '2021', 'Platinum Gray', 'Sedan', 'Automatic', 'Gasoline', 26400, 17990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(39, '3VV2B7AX1MM000135', 'Volkswagen', 'Tiguan', 'SEL', '2021', 'Pure White', 'SUV', 'Automatic', 'Gasoline', 25100, 25950.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(40, 'JM3KFBCM5M1000136', 'Mazda', 'CX-5', 'Touring', '2021', 'Machine Gray', 'SUV', 'Automatic', 'Gasoline', 23700, 24990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(41, 'JM1BPBMM5N1000137', 'Mazda', 'Mazda3', 'Premium', '2022', 'Soul Red', 'Hatchback', 'Automatic', 'Gasoline', 16800, 22990.00, 'https://source.unsplash.com/600x400/?hatchback,car', 'available', 'hatchback.jpg', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(42, 'JF2GTHNC3MH000138', 'Subaru', 'Crosstrek', 'Limited', '2021', 'Cool Gray Khaki', 'SUV', 'CVT', 'Gasoline', 22400, 25990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(43, 'JF2SKAJC6LH000139', 'Subaru', 'Forester', 'Premium', '2020', 'Jasper Green', 'SUV', 'CVT', 'Gasoline', 33100, 21990.00, 'https://source.unsplash.com/600x400/?suv,car', 'reserved', 'SUV.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-17 01:19:06'),
(44, 'YV4L12RL5P1000140', 'Volvo', 'XC60', 'B5 Plus', '2023', 'Denim Blue', 'SUV', 'Automatic', 'Hybrid', 9300, 45990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(45, 'JTHGZ1B26L5000141', 'Lexus', 'RC 300', 'F Sport', '2020', 'Ultra White', 'Coupe', 'Automatic', 'Gasoline', 18800, 35990.00, 'https://source.unsplash.com/600x400/?coupe,car', 'available', 'coupe.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(46, 'JTJGARDZ6M2000142', 'Lexus', 'NX 300', 'Base', '2021', 'Caviar', 'SUV', 'Automatic', 'Gasoline', 24600, 30990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(47, '19UUB5F46MA000143', 'Acura', 'TLX', 'A-Spec', '2021', 'Performance Red', 'Sedan', 'Automatic', 'Gasoline', 21700, 31990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(48, '5J8TC2H60NL000144', 'Acura', 'RDX', 'Technology', '2022', 'Apex Blue', 'SUV', 'Automatic', 'Gasoline', 16700, 36990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(49, '1FA6P8CF5N5000145', 'Ford', 'Mustang', 'GT', '2022', 'Race Red', 'Coupe', 'Manual', 'Gasoline', 9800, 36950.00, 'https://source.unsplash.com/600x400/?coupe,car', 'available', 'coupe.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(50, 'ZARFAMAN0M7000146', 'Alfa Romeo', 'Giulia', 'Ti', '2021', 'Vesuvio Gray', 'Sedan', 'Automatic', 'Gasoline', 23800, 30990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(51, '2C3CDXHG2NH000147', 'Dodge', 'Charger', 'R/T', '2022', 'Granite', 'Sedan', 'Automatic', 'Gasoline', 14200, 34990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'sold', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-11-07 01:16:05'),
(52, '1C4HJXDG2MW000148', 'Jeep', 'Wrangler', 'Sport S', '2021', 'Sarge Green', 'SUV', 'Manual', 'Gasoline', 20500, 32990.00, 'https://source.unsplash.com/600x400/?suv,car', 'sold', 'SUV.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(53, '3C4NJDEB2MT000149', 'Jeep', 'Compass', 'Limited', '2021', 'Laser Blue', 'SUV', 'Automatic', 'Gasoline', 22900, 23990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(54, 'WMWXU1C57M2A00150', 'MINI', 'Cooper', 'S', '2021', 'Chili Red', 'Hatchback', 'Automatic', 'Gasoline', 18300, 23950.00, 'https://source.unsplash.com/600x400/?hatchback,car', 'available', 'hatchback.jpg', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(55, 'KL79MPSL6P3000151', 'Chevrolet', 'Trailblazer', 'RS', '2023', 'Mosaic Black', 'SUV', 'Automatic', 'Gasoline', 7600, 27990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(56, '1GCUYDED9PZ000152', 'Chevrolet', 'Silverado 1500', 'LT', '2023', 'Northsky Blue', 'Truck', 'Automatic', 'Gasoline', 11200, 45990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(57, 'SALYK2EX9PA000153', 'Land Rover', 'Discovery Sport', 'S', '2023', 'Firenze Red', 'SUV', 'Automatic', 'Gasoline', 8800, 43990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(58, 'ZASPAKBN0P7000154', 'Alfa Romeo', 'Stelvio', 'Sprint', '2023', 'Alfa White', 'SUV', 'Automatic', 'Gasoline', 7200, 38990.00, 'https://source.unsplash.com/600x400/?suv,car', 'reserved', 'SUV.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-17 01:26:36'),
(59, '3VVFB7AX3RM000155', 'Volkswagen', 'Tiguan', 'SE R-Line', '2024', 'Kings Red', 'SUV', 'Automatic', 'Gasoline', 5400, 29990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(60, 'JTEAAAAH0RA000156', 'Toyota', 'Grand Highlander', 'XLE', '2024', 'Blueprint', 'SUV', 'Automatic', 'Hybrid', 4100, 44990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(61, '1N6ED1EK8RN000157', 'Nissan', 'Frontier', 'SV', '2024', 'Gun Metallic', 'Truck', 'Automatic', 'Gasoline', 3900, 34990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(62, '5XYK5CDF5RG000158', 'Kia', 'EV9', 'Wind', '2024', 'Snow White', 'SUV', 'Automatic', 'Electric', 2200, 56990.00, 'https://source.unsplash.com/600x400/?suv,car', 'sold', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-11-06 02:12:50'),
(63, 'YS3FD59Y781000159', 'Saab', '9-3', '2.0T', '2008', 'Nocturne Blue', 'Sedan', 'Manual', 'Gasoline', 128500, 5990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(64, 'JHMGE8H55BC000160', 'Honda', 'Fit', 'Sport', '2011', 'Orange', 'Hatchback', 'Automatic', 'Gasoline', 102300, 6990.00, 'https://source.unsplash.com/600x400/?hatchback,car', 'available', 'hatchback.jpg', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(65, '1G1BE5SM1H7000161', 'Chevrolet', 'Cruze', 'LT', '2017', 'Silver Ice', 'Sedan', 'Automatic', 'Gasoline', 78600, 9990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(66, 'WDDWJ4KB6LF000162', 'Mercedes-Benz', 'C 300', 'Coupe', '2020', 'Mojave Silver', 'Coupe', 'Automatic', 'Gasoline', 29800, 31990.00, 'https://source.unsplash.com/600x400/?coupe,car', 'available', 'coupe.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(67, '3KPF24AD8ME000163', 'Kia', 'Forte', 'LXS', '2021', 'Gravity Gray', 'Sedan', 'Automatic', 'Gasoline', 26100, 16990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Main Lot A', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(68, '5NMS5DAJ1RH000164', 'Hyundai', 'Santa Fe', 'Calligraphy', '2024', 'Hampton Gray', 'SUV', 'Automatic', 'Gasoline', 3500, 41990.00, 'https://source.unsplash.com/600x400/?suv,car', 'reserved', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(69, 'JTMGBRFV2RD000165', 'Toyota', 'RAV4 Prime', 'XSE', '2024', 'Supersonic Red', 'SUV', 'Automatic', 'Plug-in Hybrid', 2100, 51990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(70, 'YV1H12DK1R2000166', 'Volvo', 'V60', 'Plus', '2024', 'Crystal White', 'Wagon', 'Automatic', 'Plug-in Hybrid', 2600, 52990.00, 'https://source.unsplash.com/600x400/?station-wagon,car', 'available', 'wagon.jpeg', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(71, '3GCPADED1NG000167', 'Chevrolet', 'Silverado 1500', 'Custom', '2022', 'Summit White', 'Truck', 'Automatic', 'Gasoline', 24100, 35990.00, 'https://source.unsplash.com/600x400/?pickup,truck', 'available', 'truck.png', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(72, '1FAHP2F88HG000168', 'Ford', 'Taurus', 'SEL', '2017', 'White Gold', 'Sedan', 'Automatic', 'Gasoline', 88400, 11990.00, 'https://source.unsplash.com/600x400/?sedan,car', 'available', 'sedan.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(73, 'JTDKARFP1J3000169', 'Toyota', 'Prius Prime', 'Plus', '2018', 'Magnetic Gray', 'Hatchback', 'Automatic', 'Plug-in Hybrid', 61200, 16990.00, 'https://source.unsplash.com/600x400/?hatchback,car', 'available', 'hatchback.jpg', 'Main Lot C', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(74, 'WVGZZZ5NZJM000170', 'Volkswagen', 'Atlas', 'SEL', '2018', 'Deep Black', 'SUV', 'Automatic', 'Gasoline', 72300, 21990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(75, 'ZFF79ALA2E0200171', 'Ferrari', 'California T', NULL, '2014', 'Rosso Corsa', 'Convertible', 'Automatic', 'Gasoline', 17800, 129990.00, 'https://source.unsplash.com/600x400/?convertible,car', 'reserved', 'convertible.jpeg', 'Showroom', '2025-10-15', '2025-10-16 04:17:20', '2025-10-17 01:12:57'),
(76, 'SALLAAAN8GA000172', 'Land Rover', 'Range Rover', 'HSE', '2016', 'Santorini Black', 'SUV', 'Automatic', 'Diesel', 64200, 44990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Overflow Lot', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(77, '1GYS3CKJ2KR000173', 'Cadillac', 'Escalade', 'Luxury', '2019', 'Black Raven', 'SUV', 'Automatic', 'Gasoline', 48500, 52990.00, 'https://source.unsplash.com/600x400/?suv,car', 'available', 'SUV.png', 'Main Lot B', '2025-10-15', '2025-10-16 04:17:20', '2025-10-16 20:07:34'),
(80, 'ZARFANBN5N7000174', 'Alfa Romeo', 'Giulia', NULL, '2020', 'White', NULL, NULL, NULL, 0, 89000.00, NULL, 'reserved', '1762478305_6103464c934c.jpg', NULL, '2025-11-06', '2025-11-07 01:18:25', '2025-11-07 01:18:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_res_customer` (`customer_id`),
  ADD KEY `fk_res_employee` (`created_by_employee_id`),
  ADD KEY `idx_res_vehicle_status` (`vehicle_id`,`status`),
  ADD KEY `idx_res_time` (`start_datetime`,`end_datetime`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD UNIQUE KEY `uq_sale_vehicle` (`vehicle_id`),
  ADD KEY `fk_sale_customer` (`customer_id`),
  ADD KEY `fk_sale_employee` (`employee_id`),
  ADD KEY `idx_sale_date` (`sale_date`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `vin` (`vin`),
  ADD KEY `idx_vehicle_search` (`make`,`model`,`model_year`,`price`),
  ADD KEY `idx_vehicle_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20251030;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_res_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_res_employee` FOREIGN KEY (`created_by_employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_res_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sale_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
