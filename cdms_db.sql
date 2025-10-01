-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 01, 2025 at 02:09 AM
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
(3, 'Riley', 'Ortiz', 'riley.ortiz@example.com', '555-3003', NULL, NULL, 'Maplewood', 'MN', '55109', '2025-09-30 23:59:44', '2025-09-30 23:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('sales','manager','admin') NOT NULL DEFAULT 'sales',
  `hire_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `role`, `hire_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Alex', 'Rivera', 'alex.rivera@dealer.example', '555-2001', 'sales', '2023-06-01', 1, '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(2, 'Morgan', 'Lee', 'morgan.lee@dealer.example', '555-2002', 'manager', '2021-03-15', 1, '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(3, 'Sam', 'Patel', 'sam.patel@dealer.example', '555-2003', 'admin', '2020-10-05', 1, '2025-09-30 23:59:44', '2025-09-30 23:59:44');

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
(1, 1, 1, 1, 'test_drive', '2025-09-23 10:00:00', '2025-09-23 10:45:00', 'confirmed', 'Customer prefers morning', '2025-09-30 23:59:44', '2025-09-30 23:59:44');

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
(1, 2, 2, 1, 23250.00, '2025-09-18', 'finance', 'Approved at 4.9% APR', '2025-09-30 23:59:44', '2025-09-30 23:59:44');

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
  `status` enum('available','reserved','sold') NOT NULL DEFAULT 'available',
  `location` varchar(80) DEFAULT NULL,
  `listed_at` date NOT NULL DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `vin`, `make`, `model`, `trim`, `model_year`, `color`, `body_style`, `transmission`, `fuel_type`, `mileage`, `price`, `status`, `location`, `listed_at`, `created_at`, `updated_at`) VALUES
(1, '1HGCM82633A004352', 'Honda', 'Civic', 'EX', '2021', 'Blue', 'Sedan', 'Automatic', 'Gasoline', 16500, 20500.00, 'available', 'Main Lot A', '2025-09-30', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(2, '2T3RFREV4JW123456', 'Toyota', 'RAV4', 'XLE', '2019', 'White', 'SUV', 'Automatic', 'Gasoline', 42000, 23850.00, 'sold', 'Main Lot B', '2025-09-30', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(3, '1FTFW1E55MFB12345', 'Ford', 'F-150', 'Lariat', '2021', 'Black', 'Truck', 'Automatic', 'Gasoline', 22000, 44990.00, 'available', 'Overflow Lot', '2025-09-30', '2025-09-30 23:59:44', '2025-09-30 23:59:44'),
(4, '5YJ3E1EA7KF123456', 'Tesla', 'Model 3', 'Long Range', '2020', 'Red', 'Sedan', 'Automatic', 'Electric', 31000, 28990.00, 'available', 'Showroom', '2025-09-30', '2025-09-30 23:59:44', '2025-09-30 23:59:44');

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
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
