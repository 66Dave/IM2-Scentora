-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 02:48 PM
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
-- Database: `scentoradb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `Cart_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Date_Added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consumerdetails`
--

CREATE TABLE `consumerdetails` (
  `User_ID` int(11) NOT NULL,
  `Consumer_Name` varchar(100) NOT NULL,
  `Consumer_Email` varchar(100) NOT NULL,
  `Consumer_Address` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consumerdetails`
--

INSERT INTO `consumerdetails` (`User_ID`, `Consumer_Name`, `Consumer_Email`, `Consumer_Address`) VALUES
(1, 'Taylor Swift', 'taylor@celebrity.com', 'Beverly Hills, CA'),
(2, 'Brad Pitt', 'brad@hollywood.com', 'Los Angeles, CA'),
(3, 'Lady Gaga', 'gaga@music.com', 'New York, NY'),
(4, 'Leonardo DiCaprio', 'leo@oscar.com', 'Manhattan, NY'),
(5, 'Beyoncé', 'queen@bey.com', 'Houston, TX'),
(6, 'Tom Cruise', 'tom@mission.com', 'Pacific Palisades, CA'),
(7, 'Rihanna', 'riri@fenty.com', 'Barbados'),
(8, 'Robert Downey Jr.', 'tony@stark.com', 'Malibu, CA'),
(9, 'Ariana Grande', 'ari@grande.com', 'Boca Raton, FL'),
(10, 'Chris Hemsworth', 'thor@asgard.com', 'Byron Bay, Australia'),
(11, 'Jennifer Lawrence', 'jen@lawrence.com', 'Louisville, KY'),
(12, 'Drake', 'drake@ovo.com', 'Toronto, Canada'),
(13, 'Scarlett Johansson', 'black@widow.com', 'New York, NY'),
(14, 'Justin Bieber', 'justin@bieber.com', 'Ontario, Canada'),
(15, 'Emma Watson', 'hermione@hogwarts.com', 'London, UK'),
(16, 'The Weeknd', 'abel@xo.com', 'Toronto, Canada'),
(17, 'Margot Robbie', 'harley@quinn.com', 'Gold Coast, Australia'),
(18, 'Ed Sheeran', 'ed@sheeran.com', 'Suffolk, UK'),
(19, 'Zendaya', 'zen@daya.com', 'Oakland, CA'),
(20, 'Chris Evans', 'captain@america.com', 'Boston, MA'),
(21, 'Billie Eilish', 'billie@eilish.com', 'Los Angeles, CA'),
(22, 'Tom Holland', 'spider@man.com', 'Kingston, UK'),
(23, 'Dua Lipa', 'dua@lipa.com', 'London, UK'),
(24, 'Ryan Reynolds', 'dead@pool.com', 'Vancouver, Canada'),
(25, 'Adele', 'adele@hello.com', 'London, UK'),
(26, 'Keanu Reeves', 'john@wick.com', 'Beirut, Lebanon'),
(27, 'Olivia Rodrigo', 'olivia@rodrigo.com', 'Temecula, CA'),
(28, 'Henry Cavill', 'superman@dc.com', 'Jersey, UK'),
(29, 'Harry Styles', 'harry@styles.com', 'Holmes Chapel, UK'),
(30, 'Gal Gadot', 'wonder@woman.com', 'Tel Aviv, Israel'),
(33, 'Amazon Corporate', 'amazon@gmail.com', 'Seattle, WA');

-- --------------------------------------------------------

--
-- Table structure for table `employeedetails`
--

CREATE TABLE `employeedetails` (
  `User_ID` int(11) NOT NULL,
  `Employee_Name` varchar(100) DEFAULT NULL,
  `Employee_Email` varchar(100) DEFAULT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `Assigned_Section` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `Order_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Order_Date` datetime DEFAULT current_timestamp(),
  `Total_Amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Shipping_Address` text NOT NULL,
  `Payment_Method` varchar(50) NOT NULL,
  `Payment_Proof` varchar(255) NOT NULL,
  `Status` varchar(50) DEFAULT 'Pending',
  `Courier` varchar(10) DEFAULT NULL,
  `Tracking_Number` varchar(10) DEFAULT NULL,
  `Arrival_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`Order_ID`, `User_ID`, `Order_Date`, `Total_Amount`, `Shipping_Address`, `Payment_Method`, `Payment_Proof`, `Status`, `Courier`, `Tracking_Number`, `Arrival_Date`) VALUES
(1, 1, '2025-05-01 10:00:00', 38595.00, 'Beverly Hills, CA', 'card', 'proof_001.jpg', 'Completed', 'grab', 'TRK100001', '2025-05-04'),
(2, 1, '2025-05-15 14:30:00', 19097.00, 'Beverly Hills, CA', 'gcash', 'proof_002.jpg', 'Completed', 'lalamove', 'TRK100002', '2025-05-18'),
(3, 2, '2025-05-20 09:15:00', 31397.00, 'Los Angeles, CA', 'card', 'proof_003.jpg', 'Completed', 'grab', 'TRK100003', '2025-05-23'),
(4, 2, '2025-06-01 11:45:00', 7899.00, 'Los Angeles, CA', 'gcash', 'proof_004.jpg', 'Accepted', 'lalamove', 'TRK100004', NULL),
(5, 3, '2025-06-10 16:20:00', 41396.00, 'New York, NY', 'card', 'proof_005.jpg', 'Accepted', NULL, NULL, NULL),
(6, 3, '2025-06-15 13:10:00', 18999.00, 'New York, NY', 'gcash', 'proof_006.jpg', 'Declined', NULL, NULL, NULL),
(9, 17, '2025-02-07 11:20:00', 20797.00, 'Gold Coast, Australia', 'card', 'proof_203.jpg', 'Completed', 'grab', 'TRK203', '2025-02-10'),
(12, 22, '2025-03-01 13:15:00', 19397.00, 'Kingston, UK', 'gcash', 'proof_301.jpg', 'Completed', 'lalamove', 'TRK301', '2025-03-04'),
(13, 19, '2025-03-05 09:45:00', 11998.00, 'Oakland, CA', 'card', 'proof_302.jpg', 'Completed', 'grab', 'TRK302', '2025-03-08'),
(14, 13, '2025-03-10 15:20:00', 6299.00, 'New York, NY', 'gcash', 'proof_303.jpg', 'Completed', 'lalamove', 'TRK303', '2025-03-13'),
(15, 26, '2025-03-15 11:30:00', 39998.00, 'Beirut, Lebanon', 'card', 'proof_304.jpg', 'Completed', 'grab', 'TRK304', '2025-03-18'),
(16, 4, '2025-03-20 14:45:00', 8599.00, 'Manhattan, NY', 'gcash', 'proof_305.jpg', 'Completed', 'lalamove', 'TRK305', '2025-03-23'),
(18, 16, '2025-04-07 16:30:00', 15798.00, 'Toronto, Canada', 'gcash', 'proof_402.jpg', 'Completed', 'lalamove', 'TRK402', '2025-04-10'),
(20, 29, '2025-04-17 09:20:00', 18497.00, 'Holmes Chapel, UK', 'gcash', 'proof_404.jpg', 'Completed', 'lalamove', 'TRK404', '2025-04-20'),
(21, 3, '2025-04-22 15:40:00', 24996.00, 'New York, NY', 'card', 'proof_405.jpg', 'Completed', 'grab', 'TRK405', '2025-04-25'),
(23, 25, '2025-05-08 14:30:00', 14998.00, 'London, UK', 'card', 'proof_502.jpg', 'Completed', 'grab', 'TRK502', '2025-05-11'),
(25, 18, '2025-05-18 16:20:00', 5599.00, 'Suffolk, UK', 'card', 'proof_504.jpg', 'Completed', 'grab', 'TRK504', '2025-05-21'),
(26, 5, '2025-05-23 12:40:00', 10598.00, 'Houston, TX', 'gcash', 'proof_505.jpg', 'Completed', 'lalamove', 'TRK505', '2025-05-26'),
(31, 23, '2025-06-21 10:40:00', 25396.00, 'London, UK', 'card', 'proof_605.jpg', 'Accepted', NULL, NULL, NULL),
(34, 2, '2025-07-05 12:45:00', 6299.00, 'Los Angeles, CA', 'gcash', 'proof_703.jpg', 'Declined', NULL, NULL, NULL),
(36, 1, '2025-07-10 15:40:00', 6399.00, 'Beverly Hills, CA', 'gcash', 'proof_705.jpg', 'Accepted', 'lalamove', 'TRK705', NULL),
(37, 33, '2025-07-01 09:00:00', 2500000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz1.jpg', 'Completed', 'grab', 'AMZ100001', '2025-07-04'),
(38, 33, '2025-07-02 10:30:00', 1800000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz2.jpg', 'Completed', 'lalamove', 'AMZ100002', '2025-07-05'),
(39, 33, '2025-07-03 11:15:00', 3200000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz3.jpg', 'Completed', 'grab', 'AMZ100003', '2025-07-06'),
(40, 33, '2025-03-05 14:20:00', 4500000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz4.jpg', 'Completed', 'lalamove', 'AMZ100004', '2025-07-08'),
(41, 33, '2025-07-08 09:45:00', 1950000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz5.jpg', 'Completed', 'grab', 'AMZ100005', '2025-07-11'),
(42, 33, '2025-07-10 13:30:00', 2800000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz6.jpg', 'Accepted', 'lalamove', 'AMZ100006', NULL),
(43, 33, '2025-05-12 15:20:00', 3750000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz7.jpg', 'Accepted', 'grab', 'AMZ100007', NULL),
(44, 33, '2025-02-13 16:45:00', 5200000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz8.jpg', 'Pending', NULL, NULL, NULL),
(45, 33, '2025-04-14 10:15:00', 4100000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz9.jpg', 'Pending', NULL, NULL, NULL),
(46, 33, '2025-07-15 11:30:00', 6500000.00, 'Amazon HQ, Seattle', 'card', 'proof_amz10.jpg', 'Pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `Order_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Product_Price` decimal(10,2) DEFAULT NULL,
  `Product_Qty` int(11) DEFAULT NULL,
  `Subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`Order_ID`, `Product_ID`, `Product_Price`, `Product_Qty`, `Subtotal`) VALUES
(1, 1, 8999.00, 1, 8999.00),
(1, 6, 6799.00, 1, 6799.00),
(1, 29, 6799.00, 1, 6799.00),
(1, 30, 7999.00, 2, 15998.00),
(2, 6, 6799.00, 1, 6799.00),
(2, 11, 6999.00, 1, 6999.00),
(2, 38, 5299.00, 1, 5299.00),
(3, 1, 5699.00, 2, 11398.00),
(3, 34, 19999.00, 1, 19999.00),
(4, 5, 7899.00, 1, 7899.00),
(5, 16, 7899.00, 2, 15798.00),
(5, 38, 18999.00, 1, 18999.00),
(5, 39, 6599.00, 1, 6599.00),
(6, 38, 18999.00, 1, 18999.00),
(9, 23, 7199.00, 1, 7199.00),
(9, 32, 6799.00, 1, 13598.00),
(12, 22, 6799.00, 1, 13598.00),
(12, 29, 5799.00, 1, 5799.00),
(13, 10, 5999.00, 1, 11998.00),
(14, 3, 6299.00, 1, 6299.00),
(15, 38, 19999.00, 1, 39998.00),
(16, 33, 8599.00, 1, 8599.00),
(18, 15, 7899.00, 1, 15798.00),
(20, 3, 5499.00, 2, 10998.00),
(20, 30, 7499.00, 1, 7499.00),
(21, 27, 8599.00, 1, 8599.00),
(21, 35, 5399.00, 2, 5399.00),
(21, 37, 5499.00, 2, 10998.00),
(23, 22, 7499.00, 1, 14998.00),
(25, 24, 5599.00, 1, 5599.00),
(26, 13, 5299.00, 1, 10598.00),
(31, 28, 5999.00, 2, 11998.00),
(31, 35, 6699.00, 1, 13398.00),
(34, 18, 6299.00, 2, 6299.00),
(36, 9, 6399.00, 1, 6399.00),
(37, 34, 19999.00, 125, 2499875.00),
(37, 38, 18999.00, 131, 2488869.00),
(37, 40, 17999.00, 138, 2483862.00),
(38, 34, 19999.00, 90, 1799910.00),
(38, 38, 18999.00, 94, 1785906.00),
(38, 40, 17999.00, 100, 1799900.00),
(39, 34, 19999.00, 160, 3199840.00),
(39, 38, 18999.00, 168, 3191832.00),
(39, 40, 17999.00, 177, 3185823.00),
(40, 34, 19999.00, 225, 4499775.00),
(40, 38, 18999.00, 236, 4483764.00),
(40, 40, 17999.00, 250, 4499750.00),
(41, 34, 19999.00, 97, 1939903.00),
(41, 38, 18999.00, 102, 1937898.00),
(41, 40, 17999.00, 108, 1943892.00),
(42, 34, 19999.00, 140, 2799860.00),
(42, 38, 18999.00, 147, 2792853.00),
(42, 40, 17999.00, 155, 2789845.00),
(43, 34, 19999.00, 187, 3739813.00),
(43, 38, 18999.00, 197, 3742803.00),
(43, 40, 17999.00, 208, 3743792.00),
(44, 34, 19999.00, 260, 5199740.00),
(44, 38, 18999.00, 273, 5186727.00),
(44, 40, 17999.00, 288, 5183712.00),
(45, 34, 19999.00, 205, 4099795.00),
(45, 38, 18999.00, 215, 4084785.00),
(45, 40, 17999.00, 227, 4085773.00),
(46, 34, 19999.00, 325, 6499675.00),
(46, 38, 18999.00, 342, 6497658.00),
(46, 40, 17999.00, 361, 6497639.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `Product_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Product_Name` varchar(100) NOT NULL,
  `Product_Price` decimal(10,2) NOT NULL,
  `Stock_Level` int(1) DEFAULT NULL,
  `Product_Code` varchar(50) DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Image_URL` varchar(255) DEFAULT NULL,
  `Date_Added` date DEFAULT NULL,
  `Date_Updated` date DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT 1,
  `Brand` varchar(100) DEFAULT 'Scentora',
  `Description` text DEFAULT NULL,
  `Available_Stocks` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `User_ID`, `Product_Name`, `Product_Price`, `Stock_Level`, `Product_Code`, `Category`, `Image_URL`, `Date_Added`, `Date_Updated`, `Is_Active`, `Brand`, `Description`, `Available_Stocks`) VALUES
(1, 1, 'Chanel No. 5', 8999.00, 1, 'CH001', 'Floral', 'uploads/chanel_no5.jpg', '2025-07-16', '2025-07-16', 1, 'Chanel', 'Iconic aldehyde floral fragrance with notes of rose and jasmine', 50),
(2, 1, 'La Vie Est Belle', 6499.00, 1, 'LAN001', 'Sweet', 'uploads/la_vie.jpg', '2025-07-16', '2025-07-16', 1, 'Lancome', 'Sweet iris and patchouli fragrance with gourmand notes', 45),
(3, 1, 'Black Opium', 7299.00, 1, 'YSL001', 'Oriental', 'uploads/black_opium.jpg', '2025-07-16', '2025-07-16', 1, 'Yves Saint Laurent', 'Coffee and vanilla scent with white flowers', 40),
(4, 1, 'Light Blue', 5299.00, 1, 'DG001', 'Fresh', 'uploads/light_blue.jpg', '2025-07-16', '2025-07-16', 1, 'Dolce & Gabbana', 'Fresh citrus scent with apple and bamboo', 55),
(5, 1, 'J\'adore', 7899.00, 1, 'DIO001', 'Floral', 'uploads/jadore.jpg', '2025-07-16', '2025-07-16', 1, 'Dior', 'Floral bouquet with ylang-ylang and Damascus rose', 35),
(6, 1, 'Good Girl', 6799.00, 1, 'CH002', 'Oriental', 'uploads/good_girl.jpg', '2025-07-16', '2025-07-16', 1, 'Carolina Herrera', 'Tuberose and cocoa with tonka bean', 42),
(7, 1, 'Angel', 6299.00, 1, 'MUG001', 'Sweet', 'uploads/angel.jpg', '2025-07-16', '2025-07-16', 1, 'Mugler', 'Sweet chocolate and patchouli scent', 38),
(8, 1, 'Flowerbomb', 7499.00, 1, 'VR001', 'Floral', 'uploads/flowerbomb.jpg', '2025-07-16', '2025-07-16', 1, 'Viktor&Rolf', 'Explosive floral fragrance with orchid and rose', 48),
(9, 1, 'Miss Dior', 7899.00, 1, 'DIO002', 'Floral', 'uploads/miss_dior.jpg', '2025-07-16', '2025-07-16', 1, 'Dior', 'Rose and lily-of-the-valley blend', 40),
(10, 1, 'Si', 6599.00, 1, 'ARM001', 'Fruity', 'uploads/si.jpg', '2025-07-16', '2025-07-16', 1, 'Giorgio Armani', 'Blackcurrant nectar with vanilla and rose', 45),
(11, 1, 'Coco Mademoiselle', 8599.00, 1, 'CH003', 'Oriental', 'uploads/coco_mad.jpg', '2025-07-16', '2025-07-16', 1, 'Chanel', 'Fresh oriental with orange and patchouli', 35),
(12, 1, 'Acqua di Gio', 5999.00, 1, 'ARM002', 'Fresh', 'uploads/acqua.jpg', '2025-07-16', '2025-07-16', 1, 'Giorgio Armani', 'Marine notes with mediterranean citrus', 50),
(13, 1, 'Daisy', 5499.00, 1, 'MJ001', 'Floral', 'uploads/daisy.jpg', '2025-07-16', '2025-07-16', 1, 'Marc Jacobs', 'Fresh floral with wild berries and white daisy', 55),
(14, 1, '1 Million', 5999.00, 1, 'PR001', 'Spicy', 'uploads/1million.jpg', '2025-07-16', '2025-07-16', 1, 'Paco Rabanne', 'Leather and amber with spicy notes', 40),
(15, 1, 'Euphoria', 5699.00, 1, 'CK001', 'Oriental', 'uploads/euphoria.jpg', '2025-07-16', '2025-07-16', 1, 'Calvin Klein', 'Exotic fruits with dark orchid', 45),
(16, 1, 'Pure Poison', 6599.00, 1, 'DIO003', 'Floral', 'uploads/pure_poison.jpg', '2025-07-16', '2025-07-16', 1, 'Dior', 'White flowers with amber and sandalwood', 38),
(17, 1, 'The One', 6299.00, 1, 'DG002', 'Oriental', 'uploads/the_one.jpg', '2025-07-16', '2025-07-16', 1, 'Dolce & Gabbana', 'Vanilla and peach with amber', 42),
(18, 1, 'Bright Crystal', 5299.00, 1, 'VER001', 'Floral', 'uploads/bright_crystal.jpg', '2025-07-16', '2025-07-16', 1, 'Versace', 'Fresh floral with pomegranate', 48),
(19, 1, 'Alien', 7199.00, 1, 'MUG002', 'Oriental', 'uploads/alien.jpg', '2025-07-16', '2025-07-16', 1, 'Mugler', 'Jasmine sambac with woody notes', 40),
(20, 1, 'Lady Million', 6299.00, 1, 'PR002', 'Floral', 'uploads/lady_million.jpg', '2025-07-16', '2025-07-16', 1, 'Paco Rabanne', 'Fresh floral with honey and patchouli', 45),
(21, 1, 'Libre', 6799.00, 1, 'YSL002', 'Floral', 'uploads/libre.jpg', '2025-07-16', '2025-07-16', 1, 'Yves Saint Laurent', 'Lavender essence with orange blossom', 50),
(22, 1, 'My Way', 6399.00, 1, 'ARM003', 'Floral', 'uploads/my_way.jpg', '2025-07-16', '2025-07-16', 1, 'Giorgio Armani', 'White flowers with vanilla', 45),
(23, 1, 'Good Fortune', 5999.00, 1, 'VR002', 'Oriental', 'uploads/good_fortune.jpg', '2025-07-16', '2025-07-16', 1, 'Viktor&Rolf', 'Gentiana flower with vanilla bourbon', 40),
(24, 1, 'Olympea', 5999.00, 1, 'PR003', 'Oriental', 'uploads/olympea.jpg', '2025-07-16', '2025-07-16', 1, 'Paco Rabanne', 'Salted vanilla with ginger flower', 42),
(25, 1, 'Very Good Girl', 6699.00, 1, 'CH004', 'Floral', 'uploads/very_good_girl.jpg', '2025-07-16', '2025-07-16', 1, 'Carolina Herrera', 'Rose and vanilla with vetiver', 38),
(26, 1, 'Mon Paris', 6399.00, 1, 'YSL003', 'Fruity', 'uploads/mon_paris.jpg', '2025-07-16', '2025-07-16', 1, 'Yves Saint Laurent', 'Red berries with white musk', 45),
(27, 1, 'Crystal Noir', 5599.00, 1, 'VER002', 'Oriental', 'uploads/crystal_noir.jpg', '2025-07-16', '2025-07-16', 1, 'Versace', 'Gardenia with amber and musk', 50),
(28, 1, 'Idole', 5999.00, 1, 'LAN002', 'Floral', 'uploads/idole.jpg', '2025-07-16', '2025-07-16', 1, 'Lancome', 'Clean rose with vanilla', 48),
(29, 1, 'Perfect', 5699.00, 1, 'MJ002', 'Floral', 'uploads/perfect.jpg', '2025-07-16', '2025-07-16', 1, 'Marc Jacobs', 'Rhubarb with almond milk', 42),
(30, 1, 'Boss Bottled', 5299.00, 1, 'HB001', 'Woody', 'uploads/boss_bottled.jpg', '2025-07-16', '2025-07-16', 1, 'Hugo Boss', 'Apple and cinnamon with woody notes', 55),
(31, 1, 'Dylan Blue', 5799.00, 1, 'VER003', 'Fresh', 'uploads/dylan_blue.jpg', '2025-07-16', '2025-07-16', 1, 'Versace', 'Aquatic notes with bergamot', 45),
(32, 1, 'Le Male', 5399.00, 1, 'JPG001', 'Oriental', 'uploads/le_male.jpg', '2025-07-16', '2025-07-16', 1, 'Jean Paul Gaultier', 'Mint and vanilla with lavender', 40),
(33, 1, 'Eros', 5899.00, 1, 'VER004', 'Fresh', 'uploads/eros.jpg', '2025-07-16', '2025-07-16', 1, 'Versace', 'Mint leaves with vanilla', 48),
(34, 1, 'Aventus', 19999.00, 1, 'CR001', 'Fruity', 'uploads/aventus.jpg', '2025-07-16', '2025-07-16', 1, 'Creed', 'Blackcurrant and apple with birch', 30),
(35, 1, 'Sauvage', 7499.00, 1, 'DIO004', 'Fresh', 'uploads/sauvage.jpg', '2025-07-16', '2025-07-16', 1, 'Dior', 'Bergamot with ambroxan', 45),
(36, 1, 'Invictus', 5499.00, 1, 'PR004', 'Fresh', 'uploads/invictus.jpg', '2025-07-16', '2025-07-16', 1, 'Paco Rabanne', 'Marine notes with guaiac wood', 50),
(37, 1, 'Y', 6299.00, 1, 'YSL004', 'Fresh', 'uploads/y.jpg', '2025-07-16', '2025-07-16', 1, 'Yves Saint Laurent', 'White aldehydes with cedar', 42),
(38, 1, 'Oud Wood', 18999.00, 1, 'TF001', 'Woody', 'uploads/oud_wood.jpg', '2025-07-16', '2025-07-16', 1, 'Tom Ford', 'Rare oud wood with sandalwood', 35),
(39, 1, 'Bleu de Chanel', 7999.00, 1, 'CH005', 'Fresh', 'uploads/bleu.jpg', '2025-07-16', '2025-07-16', 1, 'Chanel', 'Citrus and vetiver blend', 40),
(40, 1, 'Tobacco Vanille', 17999.00, 1, 'TF002', 'Oriental', 'uploads/tobacco_vanille.jpg', '2025-07-16', '2025-07-16', 1, 'Tom Ford', 'Tobacco leaf with vanilla', 32);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `User_Type` enum('Admin','Consumer','Employee') NOT NULL,
  `Password` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `Profile_Image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `Name`, `Email`, `Address`, `User_Type`, `Password`, `reset_token`, `reset_expiry`, `Profile_Image`, `is_active`) VALUES
(1, 'Taylor Swift', 'taylor@celebrity.com', 'Beverly Hills, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(2, 'Brad Pitt', 'brad@hollywood.com', 'Los Angeles, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(3, 'Lady Gaga', 'gaga@music.com', 'New York, NY', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(4, 'Leonardo DiCaprio', 'leo@oscar.com', 'Manhattan, NY', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(5, 'Beyoncé', 'queen@bey.com', 'Houston, TX', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(6, 'Tom Cruise', 'tom@mission.com', 'Pacific Palisades, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(7, 'Rihanna', 'riri@fenty.com', 'Barbados', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(8, 'Robert Downey Jr.', 'tony@stark.com', 'Malibu, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(9, 'Ariana Grande', 'ari@grande.com', 'Boca Raton, FL', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(10, 'Chris Hemsworth', 'thor@asgard.com', 'Byron Bay, Australia', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(11, 'Jennifer Lawrence', 'jen@lawrence.com', 'Louisville, KY', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(12, 'Drake', 'drake@ovo.com', 'Toronto, Canada', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(13, 'Scarlett Johansson', 'black@widow.com', 'New York, NY', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(14, 'Justin Bieber', 'justin@bieber.com', 'Ontario, Canada', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(15, 'Emma Watson', 'hermione@hogwarts.com', 'London, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(16, 'The Weeknd', 'abel@xo.com', 'Toronto, Canada', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(17, 'Margot Robbie', 'harley@quinn.com', 'Gold Coast, Australia', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(18, 'Ed Sheeran', 'ed@sheeran.com', 'Suffolk, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(19, 'Zendaya', 'zen@daya.com', 'Oakland, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(20, 'Chris Evans', 'captain@america.com', 'Boston, MA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(21, 'Billie Eilish', 'billie@eilish.com', 'Los Angeles, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(22, 'Tom Holland', 'spider@man.com', 'Kingston, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(23, 'Dua Lipa', 'dua@lipa.com', 'London, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(24, 'Ryan Reynolds', 'dead@pool.com', 'Vancouver, Canada', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(25, 'Adele', 'adele@hello.com', 'London, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(26, 'Keanu Reeves', 'john@wick.com', 'Beirut, Lebanon', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(27, 'Olivia Rodrigo', 'olivia@rodrigo.com', 'Temecula, CA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(28, 'Henry Cavill', 'superman@dc.com', 'Jersey, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(29, 'Harry Styles', 'harry@styles.com', 'Holmes Chapel, UK', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(30, 'Gal Gadot', 'wonder@woman.com', 'Tel Aviv, Israel', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1),
(31, 'Admin User', 'sscentora@gmail.com', 'Cebu City', 'Admin', 'admin123', NULL, NULL, NULL, 1),
(32, 'Dave Lagunda', 'davelagunda@gmail.com', '', 'Consumer', '$2y$10$sclXu/Ro0ZQUi.YSa3Cau.h4QU.0BBiPlaf2I7RJiTmuheAYnjT.W', NULL, NULL, NULL, 1),
(33, 'Amazon Corporate', 'amazon@gmail.com', 'Seattle, WA', 'Consumer', '$2y$10$v.LBbOqs9NAqq2rcKbCLaeV6kgn8Nbv1gAu16p.uw5Te1QoKcV0.C', NULL, NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `consumerdetails`
--
ALTER TABLE `consumerdetails`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `employeedetails`
--
ALTER TABLE `employeedetails`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`Order_ID`,`Product_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `consumerdetails`
--
ALTER TABLE `consumerdetails`
  ADD CONSTRAINT `consumerdetails_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `employeedetails`
--
ALTER TABLE `employeedetails`
  ADD CONSTRAINT `employeedetails_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `order` (`Order_ID`),
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
