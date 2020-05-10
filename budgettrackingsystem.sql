-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 18, 2020 at 08:59 AM
-- Server version: 5.7.26
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `budgettrackingsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_type` varchar(10) NOT NULL COMMENT 'expenses/income',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `category_type`) VALUES
(1, 'food', 'expenses'),
(2, 'bills', 'expenses'),
(3, 'transportation', 'expenses'),
(4, 'home', 'expenses'),
(5, 'car', 'expenses'),
(6, 'entertainment', 'expenses'),
(7, 'shopping', 'expenses'),
(8, 'clothing', 'expenses'),
(9, 'insurance', 'expenses'),
(10, 'tax', 'expenses'),
(11, 'telephone', 'expenses'),
(12, 'cigarette', 'expenses'),
(13, 'health', 'expenses'),
(14, 'sport', 'expenses'),
(15, 'baby', 'expenses'),
(16, 'pet', 'expenses'),
(17, 'beauty', 'expenses'),
(18, 'electronics', 'expenses'),
(19, 'snacks', 'expenses'),
(20, 'gifts', 'expenses'),
(21, 'social', 'expenses'),
(22, 'travel', 'expenses'),
(23, 'education', 'expenses'),
(24, 'fruits and vegetables', 'expenses'),
(25, 'book', 'expenses'),
(26, 'office', 'expenses'),
(27, 'stationery', 'expenses'),
(28, 'medical', 'expenses'),
(29, 'family', 'expenses'),
(30, 'investments', 'expenses'),
(31, 'business', 'expenses'),
(32, 'salary', 'income'),
(33, 'investments', 'income'),
(34, 'awards', 'income'),
(35, 'grants', 'income'),
(36, 'sale', 'income'),
(37, 'rental', 'expenses'),
(38, 'refunds', 'income'),
(39, 'coupons', 'income'),
(40, 'lottery', 'income'),
(41, 'dividends', 'income'),
(42, 'interest', 'income'),
(43, 'gifts', 'income'),
(44, 'rental', 'income'),
(45, 'top up', 'income');

-- --------------------------------------------------------

--
-- Table structure for table `family`
--

DROP TABLE IF EXISTS `family`;
CREATE TABLE IF NOT EXISTS `family` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(255) NOT NULL,
  `family_status` int(1) NOT NULL DEFAULT '1' COMMENT '0=inactive, 1=active',
  PRIMARY KEY (`family_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `family`
--

INSERT INTO `family` (`family_id`, `family_name`, `family_status`) VALUES
(1, 'Yong', 1);

-- --------------------------------------------------------

--
-- Table structure for table `plan`
--

DROP TABLE IF EXISTS `plan`;
CREATE TABLE IF NOT EXISTS `plan` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `plan_amount` decimal(12,2) NOT NULL,
  `plan_alert` decimal(5,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `creator_user_id` int(11) NOT NULL,
  PRIMARY KEY (`plan_id`),
  KEY `category_id` (`category_id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `creator_user_id` (`creator_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `plan`
--

INSERT INTO `plan` (`plan_id`, `plan_name`, `category_id`, `wallet_id`, `plan_amount`, `plan_alert`, `start_date`, `end_date`, `creator_user_id`) VALUES
(3, 'iPhone instalment', 18, 2, '200.00', '70.00', '2020-03-28', NULL, 4),
(8, 'Tuition fee', 23, 4, '200.00', NULL, '2020-04-01', NULL, 4),
(14, 'Baby de cost', 15, 4, '250.00', '80.00', '2020-03-23', NULL, 4),
(16, 'Bills', 2, 4, '125.00', NULL, '2020-03-01', '2020-03-31', 4),
(18, 'Food', 1, 1, '500.00', '80.00', '2020-04-01', NULL, 4),
(19, 'Cloth', 8, 1, '200.00', '80.00', '2020-04-01', NULL, 4),
(21, 'EatEatEat', 1, 2, '500.00', '75.00', '2020-03-30', NULL, 4),
(27, 'Food', 1, 23, '500.00', '80.00', '2020-04-01', NULL, 4),
(34, 'Makan', 1, 8, '400.00', '75.00', '2020-04-01', NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE IF NOT EXISTS `transaction` (
  `trans_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `trans_amount` decimal(12,2) NOT NULL,
  `trans_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trans_desc` text NOT NULL,
  PRIMARY KEY (`trans_id`),
  KEY `category_id` (`category_id`),
  KEY `wallet_id` (`wallet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`trans_id`, `category_id`, `wallet_id`, `trans_amount`, `trans_date`, `trans_desc`) VALUES
(1, 2, 2, '200.00', '2020-03-15 14:10:43', 'Electrical bill'),
(2, 32, 2, '3000.00', '2020-03-15 14:10:43', 'salary'),
(3, 37, 2, '800.00', '2020-03-15 14:33:55', 'rental pay to Mrs. Lee'),
(5, 1, 1, '20.00', '2020-03-15 14:35:10', 'foodpanda'),
(6, 28, 1, '68.00', '2020-02-18 14:36:14', 'Corona'),
(7, 33, 2, '200.00', '2020-03-17 15:08:12', 'Apple Inc.'),
(8, 6, 1, '144.00', '2020-03-17 15:08:12', 'Escape Park'),
(9, 2, 1, '16.00', '2020-03-17 20:38:34', 'water bill'),
(11, 42, 2, '20.00', '2020-03-17 00:00:00', 'bank interest'),
(12, 8, 1, '69.00', '2020-03-14 00:00:00', ''),
(14, 2, 2, '321.00', '2020-03-20 05:02:50', 'test'),
(19, 6, 1, '8.00', '2020-03-17 00:00:00', 'Movie'),
(20, 19, 1, '2.00', '2020-03-01 00:00:00', 'mr potatoes'),
(23, 36, 2, '200.00', '2020-03-19 00:00:00', 'sell item'),
(24, 3, 2, '8.00', '2020-02-28 00:00:00', 'grab to somewhere'),
(27, 25, 1, '59.90', '2020-03-20 00:00:00', 'novel'),
(35, 6, 1, '11.00', '2020-03-25 23:50:29', 'Cinema'),
(36, 5, 2, '100.00', '2020-03-23 00:00:00', 'random desc'),
(37, 1, 2, '20.00', '2020-03-30 20:31:06', ''),
(40, 18, 2, '51.20', '2020-03-30 21:09:10', ''),
(41, 18, 2, '10.00', '2020-03-30 21:10:32', ''),
(50, 34, 2, '45.00', '2020-03-30 22:48:31', ''),
(51, 1, 2, '80.00', '2020-03-30 22:48:47', ''),
(52, 18, 2, '200.00', '2020-03-30 22:49:23', ''),
(53, 1, 2, '250.00', '2020-03-30 22:53:50', ''),
(54, 32, 4, '5000.00', '2020-03-31 18:34:55', ''),
(55, 15, 4, '200.00', '2020-03-31 19:02:36', ''),
(56, 2, 2, '126.00', '2020-03-31 19:04:02', ''),
(61, 17, 4, '200.00', '2020-04-01 23:44:02', 'Buy for someone (âÂ´â—¡`â)'),
(64, 15, 7, '79.90', '2020-04-02 02:33:04', 'babyyyyyy'),
(67, 41, 7, '100.00', '2020-04-02 02:36:50', ''),
(68, 1, 2, '50.00', '2020-04-16 01:52:26', ''),
(69, 1, 8, '200.00', '2020-04-17 03:06:07', 'I just want to eatttttt'),
(70, 1, 23, '100.00', '2020-04-17 21:59:30', 'HaiDiLao Hotpot'),
(86, 1, 8, '170.00', '2020-04-18 16:13:03', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_pw` varchar(255) NOT NULL,
  `family_id` int(11) DEFAULT NULL,
  `access_level` int(11) NOT NULL DEFAULT '1' COMMENT '1=strong user, 2=weak user',
  `user_status` int(1) NOT NULL DEFAULT '1' COMMENT '0=inactive, 1=active',
  PRIMARY KEY (`user_id`),
  KEY `family_id` (`family_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_email`, `user_pw`, `family_id`, `access_level`, `user_status`) VALUES
(1, 'YongYikPhern', 'yikphern@gmail.com', 'e99a18c428cb38d5f260853678922e03', NULL, 1, 1),
(2, 'yap ', 'hogsnysp@gmail.com', '202cb962ac59075b964b07152d234b70', NULL, 1, 1),
(3, 'StanleyYong', 'stanley0063@gmail.com', 'ca17e0df20b0953056ec545bc0f5a40b', NULL, 1, 1),
(4, 'myParent', 'a@a.com', '0cc175b9c0f1b6a831c399e269772661', 1, 1, 1),
(5, 'myChild', 'child@mail.com', '202cb962ac59075b964b07152d234b70', 1, 2, 1),
(6, 'myChild2', 'child2@mail.com', '123', 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

DROP TABLE IF EXISTS `wallet`;
CREATE TABLE IF NOT EXISTS `wallet` (
  `wallet_id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wallet_bal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'wallet balance',
  `wallet_status` int(1) NOT NULL DEFAULT '1' COMMENT '0=inactive, 1=active',
  PRIMARY KEY (`wallet_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wallet`
--

INSERT INTO `wallet` (`wallet_id`, `wallet_name`, `user_id`, `wallet_bal`, `wallet_status`) VALUES
(1, 'Cash', 4, '677.00', 1),
(2, 'Bank', 4, '10256.80', 1),
(4, 'Wallet 2', 4, '4600.00', 1),
(7, 'New Wallet', 4, '20.10', 1),
(8, 'Child Wallet', 5, '130.00', 1),
(9, 'School Fee', 5, '200.00', 1),
(21, 'TestWallet', 5, '0.00', 1),
(23, 'Child2Wallet', 6, '900.00', 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `plan`
--
ALTER TABLE `plan`
  ADD CONSTRAINT `plan_categ` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `plan_user` FOREIGN KEY (`creator_user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `plan_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`wallet_id`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `trans_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `trans_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`wallet_id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_family` FOREIGN KEY (`family_id`) REFERENCES `family` (`family_id`);

--
-- Constraints for table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `wallet_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
