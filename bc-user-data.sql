-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2024 at 06:29 PM
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
-- Database: `bc-user-data`
--

CREATE DATABASE IF NOT EXISTS `bc-user-data`;
USE `bc-user-data`;

--
-- Table structure for table `earned`
--

CREATE TABLE `earned` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unique_id` VARCHAR(17) NOT NULL,
  `earnings` DECIMAL(10,2) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `unique_id` (`unique_id`)
);

--
-- Table structure for table `promocodes`
--

CREATE TABLE `promocodes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `points` DECIMAL(10,2) NOT NULL,
  `expiry_date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);

--
-- Table structure for table `refearn`
--

CREATE TABLE `refearn` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` VARCHAR(17) NOT NULL,
  `referred_id` VARCHAR(17) NOT NULL,
  `earnings` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `referrer_id` (`referrer_id`),
  INDEX `referred_id` (`referred_id`)
);

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` VARCHAR(17) NOT NULL,
  `referred_id` VARCHAR(17) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `referrer_id` (`referrer_id`),
  INDEX `referred_id` (`referred_id`)
);

--
-- Table structure for table `userpoints`
--

CREATE TABLE `userpoints` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unique_id` VARCHAR(17) NOT NULL,
  `earned` DECIMAL(10,2) NOT NULL,
  `spent` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `unique_id` (`unique_id`)
);

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `unique_id` VARCHAR(17) NOT NULL,
  `picture` VARCHAR(500) NOT NULL,
  `roblox_avatar_url` VARCHAR(500) NOT NULL,
  `balance` DECIMAL(10,2) NOT NULL,
  `user_id` VARCHAR(17) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_id` (`unique_id`),
  INDEX `user_id` (`user_id`)
);

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`unique_id`),
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`unique_id`);

--
-- Constraints for table `refearn`
--
ALTER TABLE `refearn`
  ADD CONSTRAINT `refearn_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`unique_id`),
  ADD CONSTRAINT `refearn_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`unique_id`);

--
-- Constraints for table `userpoints`
--
ALTER TABLE `userpoints`
  ADD CONSTRAINT `userpoints_ibfk_1` FOREIGN KEY (`unique_id`) REFERENCES `users` (`unique_id`);

--
-- Constraints for table `earned`
--
ALTER TABLE `earned`
  ADD CONSTRAINT `earned_ibfk_1` FOREIGN KEY (`unique_id`) REFERENCES `users` (`unique_id`);

--
-- Triggers `earned_after_insert`
--
DELIMITER $$
CREATE TRIGGER `earned_after_insert` AFTER INSERT ON `earned`
FOR EACH ROW BEGIN
  IF NEW.type = 'promocode' THEN
    UPDATE `userpoints` SET `earned` = `earned` + NEW.earnings WHERE `unique_id` = NEW.unique_id;
  END IF;
END $$
DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;