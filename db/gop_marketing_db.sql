-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 01:23 AM
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
-- Database: `gop_marketing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `charges`
--

CREATE TABLE `charges` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `charge_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charges`
--

INSERT INTO `charges` (`id`, `customer_id`, `total_price`, `po_number`, `charge_date`) VALUES
(1, 1, 6718.00, NULL, '2025-04-26 03:16:32'),
(2, 2, 12586.00, NULL, '2025-03-04 03:53:22'),
(3, 3, 2500.00, NULL, '2025-04-26 04:34:45'),
(4, 3, 2500.00, NULL, '2025-04-26 04:37:12'),
(5, 3, 500.00, NULL, '2025-01-01 06:07:12'),
(6, 1, 18129.00, NULL, '2025-04-26 11:15:41'),
(7, 3, 665.00, NULL, '2025-04-28 05:04:39'),
(8, 2, 665.00, NULL, '2025-04-28 05:04:57'),
(9, 2, 665.00, NULL, '2025-04-28 05:05:11'),
(10, 2, 20615.00, NULL, '2025-04-28 05:05:45'),
(11, 1, 8287.00, NULL, '2025-04-28 05:09:24'),
(12, 2, 1569.00, NULL, '2025-04-28 05:17:32'),
(13, 1, 1175.00, NULL, '2025-04-28 05:33:15'),
(14, 7, 3350.00, NULL, '2025-04-28 05:56:50'),
(15, 1, 5565.00, NULL, '2025-04-28 08:13:59'),
(16, 7, 6304.00, NULL, '2025-06-04 04:50:23'),
(17, 7, 1190.00, NULL, '2025-06-04 12:42:47'),
(18, 7, 1246.00, NULL, '2025-06-04 12:43:00'),
(19, 7, 683.00, NULL, '2025-06-04 12:44:48'),
(20, 7, 450.00, NULL, '2025-06-24 04:57:21'),
(21, 7, 150.00, NULL, '2025-06-24 05:30:30'),
(22, 7, 320.00, NULL, '2025-06-24 05:33:42'),
(23, 7, 30.00, NULL, '2025-06-24 05:34:06'),
(24, 4, 525.00, NULL, '2025-06-24 05:34:21'),
(25, 5, 6260.00, NULL, '2025-06-24 05:34:54'),
(26, 5, 28810.00, NULL, '2025-06-24 05:35:12'),
(27, 10, 2072.00, NULL, '2025-06-24 05:35:33'),
(28, 7, 45.00, NULL, '2025-06-24 07:21:00'),
(29, 10, 15.00, '905', '2025-06-24 07:27:20'),
(32, 6, 30.00, '67845', '2025-06-24 08:03:11'),
(34, 6, 4250.00, '2341', '2025-06-24 08:04:29'),
(35, 6, 4250.00, '2341', '2025-06-24 08:04:30'),
(36, 7, 607.50, '0005', '2025-06-24 08:11:32'),
(37, 7, 607.50, '0005', '2025-06-24 08:11:33'),
(38, 10, 1084.00, '7890', '2025-06-24 08:20:30'),
(39, 10, 1084.00, '7890', '2025-06-24 08:20:30'),
(40, 1, 2462.00, '67845', '2025-06-24 08:20:53'),
(42, 6, 1231.00, '2341', '2025-06-24 08:21:02'),
(44, 11, 16025.00, '00029', '2025-06-25 12:51:26'),
(45, 11, 16025.00, '00029', '2025-06-25 12:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `charge_items`
--

CREATE TABLE `charge_items` (
  `id` int(11) NOT NULL,
  `charge_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charge_items`
--

INSERT INTO `charge_items` (`id`, `charge_id`, `item_id`, `quantity`, `price`, `discount_percentage`) VALUES
(1, 1, 5, 1, 500.00, 0.00),
(2, 1, 4, 1, 675.00, 0.00),
(3, 1, 9, 1, 5543.00, 0.00),
(4, 2, 5, 3, 500.00, 0.00),
(5, 2, 9, 2, 5543.00, 0.00),
(6, 3, 5, 5, 500.00, 0.00),
(7, 4, 5, 5, 500.00, 0.00),
(8, 5, 5, 1, 500.00, 0.00),
(9, 6, 5, 3, 500.00, 0.00),
(10, 6, 9, 3, 5543.00, 0.00),
(11, 7, 8, 1, 665.00, 0.00),
(12, 8, 8, 1, 665.00, 0.00),
(13, 9, 8, 1, 665.00, 0.00),
(14, 10, 8, 31, 665.00, 0.00),
(15, 11, 5, 1, 500.00, 0.00),
(16, 11, 4, 1, 675.00, 0.00),
(17, 11, 9, 1, 5543.00, 0.00),
(18, 11, 1, 1, 70.00, 0.00),
(19, 11, 6, 1, 679.00, 0.00),
(20, 11, 3, 1, 150.00, 0.00),
(21, 11, 7, 1, 670.00, 0.00),
(22, 12, 7, 1, 670.00, 0.00),
(23, 12, 3, 1, 150.00, 0.00),
(24, 12, 6, 1, 679.00, 0.00),
(25, 12, 1, 1, 70.00, 0.00),
(26, 13, 5, 1, 500.00, 0.00),
(27, 13, 4, 1, 675.00, 0.00),
(28, 14, 5, 4, 500.00, 0.00),
(29, 14, 4, 2, 675.00, 0.00),
(30, 15, 5, 3, 500.00, 0.00),
(31, 15, 4, 6, 675.00, 0.00),
(32, 15, 11, 1, 15.00, 0.00),
(33, 16, 11, 2, 15.00, 0.00),
(34, 16, 4, 1, 675.00, 0.00),
(35, 16, 10, 1, 56.00, 0.00),
(36, 16, 9, 1, 5543.00, 0.00),
(37, 17, 4, 1, 675.00, 0.00),
(38, 17, 5, 1, 500.00, 0.00),
(39, 17, 11, 1, 15.00, 0.00),
(40, 18, 11, 1, 15.00, 0.00),
(41, 18, 5, 1, 500.00, 0.00),
(42, 18, 4, 1, 675.00, 0.00),
(43, 18, 10, 1, 56.00, 0.00),
(44, 19, 11, 1, 15.00, 0.00),
(45, 19, 5, 1, 500.00, 0.00),
(46, 19, 10, 3, 56.00, 0.00),
(47, 20, 17, 18, 25.00, 0.00),
(48, 21, 17, 6, 25.00, 0.00),
(49, 22, 16, 32, 10.00, 0.00),
(50, 23, 11, 2, 15.00, 0.00),
(51, 24, 11, 35, 15.00, 0.00),
(52, 25, 15, 16, 10.00, 0.00),
(53, 25, 3, 38, 150.00, 0.00),
(54, 25, 14, 20, 20.00, 0.00),
(55, 26, 7, 43, 670.00, 0.00),
(56, 27, 10, 37, 56.00, 0.00),
(57, 28, 11, 3, 15.00, 0.00),
(58, 29, 11, 1, 15.00, 0.00),
(59, 32, 11, 4, 15.00, 50.00),
(60, 34, 5, 10, 500.00, 15.00),
(61, 35, 5, 10, 500.00, 15.00),
(62, 36, 4, 1, 675.00, 10.00),
(63, 37, 4, 1, 675.00, 10.00),
(64, 38, 5, 2, 500.00, 5.00),
(65, 38, 12, 2, 67.00, 0.00),
(66, 39, 5, 2, 500.00, 5.00),
(67, 39, 12, 2, 67.00, 0.00),
(68, 40, 10, 2, 56.00, 0.00),
(69, 40, 4, 2, 675.00, 0.00),
(70, 40, 5, 2, 500.00, 0.00),
(71, 42, 5, 1, 500.00, 0.00),
(72, 42, 4, 1, 675.00, 0.00),
(73, 42, 10, 1, 56.00, 0.00),
(76, 44, 4, 23, 675.00, 0.00),
(77, 44, 5, 1, 500.00, 0.00),
(78, 45, 4, 23, 675.00, 0.00),
(79, 45, 5, 1, 500.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `terms` varchar(255) NOT NULL,
  `salesman` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone_number`, `address`, `terms`, `salesman`, `created_at`) VALUES
(1, 'Kent', '09876543212', 'Wangag, Leyte', '0', '', '2025-06-04 04:37:07'),
(2, 'Kevin Heart', '098765434', 'balugo 2 ', '0', '', '2025-06-04 04:37:07'),
(3, 'Michael Pangilinan', '098767232', 'Kapinhan, likods buki', '0', '', '2025-06-04 04:37:07'),
(4, 'Harvey Dela Cruz Casane', '343434', 'Sitio Wangag', '0', '', '2025-06-04 04:37:07'),
(5, 'Harvey Dela Cruz Casane', '34344', 'Sitio Wangag', '0', '', '2025-06-04 04:37:07'),
(6, 'hayup', '3434', 'Sitio Wangag', 'Monthly payment', 'puyah', '2025-06-04 04:37:07'),
(7, 'Doms', '0987', 'Cebu', 'COD', 'Unoboy', '2025-06-04 04:37:07'),
(8, 'Mark', '09123456', 'Brgy. Liloan Ormoc City', 'COD', 'Carlos Sode', '2025-06-04 04:37:07'),
(9, 'test', '05656', 'test', 'COD', 'Agregate', '2025-06-04 04:37:07'),
(10, 'Harvey Casane', '0912345690', 'Wangag', 'Monthly payment', 'Rosal uyab ni harv', '2025-06-04 04:38:53'),
(11, 'Nicole Shayne Noval', '0912395812', 'Borok', 'COD', 'Printet', '2025-06-25 12:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `stock` int(255) NOT NULL,
  `sold_by` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `cost` double(50,2) NOT NULL,
  `price` double(50,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `stock`, `sold_by`, `name`, `category`, `cost`, `price`) VALUES
(1, 48, 'PCS', 'test item', 'testing', 50.00, 70.00),
(3, 6, 'PCS', 'test item 2', 'testing', 100.00, 150.00),
(4, 480, 'piece', 'Helmet', 'Gear', 450.00, 675.00),
(5, 290, 'PCK', 'Gloves', 'Gear', 345.00, 500.00),
(6, 4543, 'PCK', 'test item ', 'PLUMBING', 456.00, 679.00),
(7, 86, 'PCS', 'testing', 'ELECTRICAL', 434.00, 670.00),
(8, 200, 'PCS', 'wowowo hahah', 'HARDWARE', 34.00, 665.00),
(9, 445, 'BOX', 'nails wow', 'P.E FITTING', 3434.00, 5543.00),
(10, 0, 'PCS', 'ICE CANDY', 'PLUMBING', 34.00, 56.00),
(11, 230, 'PCS', 'charger', 'ELECTRICAL', 50.00, 15.00),
(12, 456, 'PCS', 'Mousepad', 'ELECTRICAL', 45.00, 67.00),
(13, 50, 'PCS', 'PVC', 'HARDWARE', 25.00, 50.00),
(14, 5, 'PCS', 'wawa', 'HARDWARE', 25.00, 20.00),
(15, 258, 'PCS', 'test', 'P.E FITTING', 25.00, 10.00),
(16, 203, 'PCS', 'test1', 'ELECTRICAL', 10.00, 10.00),
(17, 6, 'PCS', 'test', 'ELECTRICAL', 50.00, 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `item_stock_history`
--

CREATE TABLE `item_stock_history` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_added` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_stock_history`
--

INSERT INTO `item_stock_history` (`id`, `item_id`, `quantity_added`, `date_added`) VALUES
(1, 12, 4, '2025-04-29 13:57:21'),
(2, 11, 1, '2025-04-29 14:01:56'),
(3, 16, 10, '2025-04-30 04:21:11'),
(4, 17, 10, '2025-06-04 05:18:04'),
(5, 8, 200, '2025-06-24 04:56:37'),
(6, 7, 86, '2025-06-24 05:53:25'),
(7, 16, 200, '2025-06-24 06:13:24'),
(8, 11, 230, '2025-06-24 08:35:26'),
(9, 15, 250, '2025-06-25 12:34:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `charge_items`
--
ALTER TABLE `charge_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `charge_id` (`charge_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_stock_history`
--
ALTER TABLE `item_stock_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `charge_items`
--
ALTER TABLE `charge_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `item_stock_history`
--
ALTER TABLE `item_stock_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `charges`
--
ALTER TABLE `charges`
  ADD CONSTRAINT `charges_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `charge_items`
--
ALTER TABLE `charge_items`
  ADD CONSTRAINT `charge_items_ibfk_1` FOREIGN KEY (`charge_id`) REFERENCES `charges` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `charge_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `item_stock_history`
--
ALTER TABLE `item_stock_history`
  ADD CONSTRAINT `item_stock_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
