-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2021 at 04:09 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `m307_timo`
--
CREATE DATABASE IF NOT EXISTS `m307_timo` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `m307_timo`;

-- --------------------------------------------------------

--
-- Table structure for table `timo_apps`
--

DROP TABLE IF EXISTS `timo_apps`;
CREATE TABLE `timo_apps` (
  `app_id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `app_kaufdatum` date NOT NULL,
  `app_kaufpreis` decimal(5,2) DEFAULT NULL,
  `app_kategorie` varchar(255) NOT NULL,
  `app_rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `timo_apps`
--

INSERT INTO `timo_apps` (`app_id`, `app_name`, `app_kaufdatum`, `app_kaufpreis`, `app_kategorie`, `app_rating`) VALUES
(1, 'Todos!', '2016-12-01', '15.50', 'Work', 5),
(2, 'Slash\'n Go', '2017-02-01', '12.00', 'Games', 4),
(3, 'Uzmosy', '2017-03-01', '10.50', 'Social', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `timo_apps`
--
ALTER TABLE `timo_apps`
  ADD PRIMARY KEY (`app_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `timo_apps`
--
ALTER TABLE `timo_apps`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
