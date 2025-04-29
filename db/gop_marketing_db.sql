-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 04:13 PM
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
  `charge_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charges`
--

INSERT INTO `charges` (`id`, `customer_id`, `total_price`, `charge_date`) VALUES
(1, 1, 6718.00, '2025-04-26 03:16:32'),
(2, 2, 12586.00, '2025-03-04 03:53:22'),
(3, 3, 2500.00, '2025-04-26 04:34:45'),
(4, 3, 2500.00, '2025-04-26 04:37:12'),
(5, 3, 500.00, '2025-01-01 06:07:12'),
(6, 1, 18129.00, '2025-04-26 11:15:41'),
(7, 3, 665.00, '2025-04-28 05:04:39'),
(8, 2, 665.00, '2025-04-28 05:04:57'),
(9, 2, 665.00, '2025-04-28 05:05:11'),
(10, 2, 20615.00, '2025-04-28 05:05:45'),
(11, 1, 8287.00, '2025-04-28 05:09:24'),
(12, 2, 1569.00, '2025-04-28 05:17:32'),
(13, 1, 1175.00, '2025-04-28 05:33:15'),
(14, 7, 3350.00, '2025-04-28 05:56:50'),
(15, 1, 5565.00, '2025-04-28 08:13:59');

-- --------------------------------------------------------

--
-- Table structure for table `charge_items`
--

CREATE TABLE `charge_items` (
  `id` int(11) NOT NULL,
  `charge_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charge_items`
--

INSERT INTO `charge_items` (`id`, `charge_id`, `item_id`, `quantity`, `price`) VALUES
(1, 1, 5, 1, 500.00),
(2, 1, 4, 1, 675.00),
(3, 1, 9, 1, 5543.00),
(4, 2, 5, 3, 500.00),
(5, 2, 9, 2, 5543.00),
(6, 3, 5, 5, 500.00),
(7, 4, 5, 5, 500.00),
(8, 5, 5, 1, 500.00),
(9, 6, 5, 3, 500.00),
(10, 6, 9, 3, 5543.00),
(11, 7, 8, 1, 665.00),
(12, 8, 8, 1, 665.00),
(13, 9, 8, 1, 665.00),
(14, 10, 8, 31, 665.00),
(15, 11, 5, 1, 500.00),
(16, 11, 4, 1, 675.00),
(17, 11, 9, 1, 5543.00),
(18, 11, 1, 1, 70.00),
(19, 11, 6, 1, 679.00),
(20, 11, 3, 1, 150.00),
(21, 11, 7, 1, 670.00),
(22, 12, 7, 1, 670.00),
(23, 12, 3, 1, 150.00),
(24, 12, 6, 1, 679.00),
(25, 12, 1, 1, 70.00),
(26, 13, 5, 1, 500.00),
(27, 13, 4, 1, 675.00),
(28, 14, 5, 4, 500.00),
(29, 14, 4, 2, 675.00),
(30, 15, 5, 3, 500.00),
(31, 15, 4, 6, 675.00),
(32, 15, 11, 1, 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone_number`, `address`) VALUES
(1, 'Kent', '09876543212', 'Wangag, Leyte'),
(2, 'Kevin Heart', '098765434', 'balugo 2 '),
(3, 'Michael Pangilinan', '098767232', 'Kapinhan, likods buki'),
(4, 'Harvey Dela Cruz Casane', '343434', 'Sitio Wangag'),
(5, 'Harvey Dela Cruz Casane', '34344', 'Sitio Wangag'),
(6, 'hayup', '3434', 'Sitio Wangag'),
(7, 'Doms', '0987', 'Cebu'),
(8, 'Mark', '09123456', 'wangag'),
(9, 'test', '05656', 'test');

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
(3, 44, 'PCS', 'test item 2', 'testing', 100.00, 150.00),
(4, 534, 'piece', 'Helmet', 'Gear', 450.00, 675.00),
(5, 322, 'PCK', 'Gloves', 'Gear', 345.00, 500.00),
(6, 4543, 'PCK', 'test item ', 'testing', 456.00, 679.00),
(7, 43, 'PCS', 'testing', 'test', 434.00, 670.00),
(8, 0, 'PCS', 'wowowo hahah', 'wowowo', 34.00, 665.00),
(9, 446, 'BOX', 'nails wow', 'nails', 3434.00, 5543.00),
(10, 45, 'PCS', 'ice Candy', 'malameg', 34.00, 56.00),
(11, 50, 'PCS', 'charger', 'japan', 50.00, 15.00),
(12, 460, 'PCS', 'test', 'test', 45.00, 67.00);

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
(2, 11, 1, '2025-04-29 14:01:56');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `charge_items`
--
ALTER TABLE `charge_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `item_stock_history`
--
ALTER TABLE `item_stock_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
