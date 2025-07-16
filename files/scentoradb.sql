-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 02:57 AM
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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`Cart_ID`, `User_ID`, `Product_ID`, `Quantity`, `Date_Added`) VALUES
(12, 7, 5, 1, '2025-07-15 05:55:51'),
(13, 1, 4, 1, '2025-07-15 07:04:41'),
(14, 1, 2, 6, '2025-07-16 00:42:53'),
(15, 1, 6, 1, '2025-07-16 00:43:25'),
(16, 1, 5, 1, '2025-07-16 00:44:26');

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
(2, 'ConsumerUser', 'consumer@gmail.com', 'Cebu City');

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

--
-- Dumping data for table `employeedetails`
--

INSERT INTO `employeedetails` (`User_ID`, `Employee_Name`, `Employee_Email`, `Position`, `Assigned_Section`) VALUES
(3, 'EmployeeUser', 'employee@gmail.com', 'Inventory Clerk', 'Orders');

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
(1, 5, '2025-07-15 10:14:51', 59.00, 'Maria Santos 1234 Rizal Street Cebu City Cebu 6000 Philippines', 'gcash', '../uploads/proofs/proof_1752545690_6875b99af17ff.jpg', 'Pending', NULL, NULL, NULL),
(2, 1, '2025-07-15 10:19:42', 1.00, 'Maria Santos 1234 Rizal Street Cebu City Cebu 6000 Philippines', 'gcash', '../uploads/proofs/proof_1752545982_6875babe7d632.jpg', 'Pending', NULL, NULL, NULL),
(3, 5, '2025-07-15 10:33:23', 1.00, 'Maria Santos 1234 Rizal Street Cebu City Cebu 6000 Philippines', 'gcash', '../uploads/proofs/proof_1752546803_6875bdf3867a0.jpg', 'Pending', NULL, NULL, NULL),
(4, 5, '2025-07-15 10:38:55', 2.00, '123', 'card', '../uploads/proofs/proof_1752547135_6875bf3f11532.jpg', 'Pending', NULL, NULL, NULL),
(5, 5, '2025-07-15 10:46:35', 1.00, 'Maria Santos 1234 Rizal Street Cebu City Cebu 6000 Philippines', 'gcash', '../uploads/proofs/proof_1752547595_6875c10b3f2b7.jpg', 'Pending', NULL, NULL, NULL),
(6, 6, '2025-07-15 11:46:14', 169.00, 'usc TC', 'gcash', '../uploads/proofs/proof_1752551174_6875cf0692360.png', 'Pending', NULL, NULL, NULL),
(7, 2, '2025-07-16 08:53:04', 4.00, '5637', 'gcash', 'proof_1752627184_8672.jpg', 'Declined', 'grab', NULL, NULL);

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
(1, 2, 1.00, 5, NULL),
(1, 3, 21.00, 2, NULL),
(1, 4, 12.00, 1, NULL),
(2, 2, 1.00, 1, NULL),
(3, 2, 1.00, 1, NULL),
(4, 2, 1.00, 2, NULL),
(5, 2, 1.00, 1, NULL),
(6, 5, 69.00, 1, NULL),
(6, 6, 100.00, 1, NULL),
(7, 2, 1.00, 4, 4.00);

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
(2, 1, 'Test1', 1.00, 1, '123', 'Floral', 'uploads/wo_mu.png', '2025-07-08', '2025-07-16', 1, 'Scentora', 'vf', 450),
(3, 1, 'lead dev for sale', 21.00, 2, '344', 'Floral', 'uploads/developer.jpg', '2025-07-08', '2025-07-16', 1, 'randomhuman2', '3ed', 1),
(4, 1, 'random guy', 12.00, 1, '12', 'Floral', 'uploads/developer.jpg', '2025-07-14', '2025-07-16', 1, 'Scentora', 'Mega', 54),
(5, 1, 'jabol monster', 69.00, 1, '69', 'Floral', 'uploads/developer.jpg', '2025-07-14', '2025-07-16', 1, 'khit', 'Niga', 435),
(6, 1, 'random ass perfume', 100.00, 1, 'random1', 'Fresh', 'uploads/flo_es.png', '2025-07-14', '2025-07-16', 1, 'Scentora', 'Checkkk', 65);

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
  `Profile_Image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `Name`, `Email`, `Address`, `User_Type`, `Password`, `reset_token`, `reset_expiry`, `Profile_Image`) VALUES
(1, 'AdminUser', 'admin@example.com', 'Cebu City', 'Admin', 'admin123', NULL, NULL, NULL),
(2, 'ConsumerUser', 'customer@example.com', 'Cebu City', 'Consumer', 'customer123', NULL, NULL, NULL),
(3, 'EmployeeUser', 'employee@gmail.com', 'Lapu-Lapu City', 'Employee', 'employee123', NULL, NULL, NULL),
(4, 'dave lang', 'dave@test.com', '', 'Consumer', '$2y$10$xKZ57il.hkE15hUT/Xx95uQ7H5cpNwe1zTWE3kO22rmCYBuyiinlu', NULL, NULL, NULL),
(5, 'Dave Lagunda', 'davelagunda@gmail.com', '', 'Consumer', '$2y$10$PQnRSc6.mvGfzS8lm3ra3ejk4MXjVtaxj/1212345678', 'b0a7dcd93d000baa20844288b58d46209d9fdaa76fa1a9817d65552389f55964', '2025-07-15 16:45:51', NULL),
(6, 'Kylle A. Buhia', 'kylle@gmail.com', '-USC - TC', 'Consumer', '$2y$10$cDGbO54mXkyT1KQCPpkw1uBRrLSPUty8Ut5iYuFUiXAdpZ5KUQjvS', NULL, NULL, '../uploads/profile/Halo (1).png'),
(7, 'Kylle Andrei', 'kyllebuhia@gmail.com', '', 'Consumer', '123456789', NULL, NULL, '../uploads/profile/card_qr.jpg');

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
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
