-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 05:24 PM
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
-- Table structure for table `admindetails`
--

CREATE TABLE `admindetails` (
  `User_ID` int(11) NOT NULL,
  `Admin_Name` varchar(100) NOT NULL,
  `Admin_Email` varchar(100) NOT NULL,
  `Consumer_Address` varchar(200) NOT NULL,
  `Inventory_Information` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admindetails`
--

INSERT INTO `admindetails` (`User_ID`, `Admin_Name`, `Admin_Email`, `Consumer_Address`, `Inventory_Information`) VALUES
(1, 'AdminUser', 'admin@gmail.com', 'Cebu City', 'Manages perfume inventory');

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
  `User_ID` int(11) DEFAULT NULL,
  `Order_Date` date DEFAULT NULL,
  `Amount_Paid` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `Product_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Product_Name` varchar(100) NOT NULL,
  `Product_Price` decimal(10,2) NOT NULL,
  `Stock_Status` varchar(50) NOT NULL,
  `Product_Code` varchar(50) DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Image_URL` varchar(255) DEFAULT NULL,
  `Date_Added` date DEFAULT NULL,
  `Date_Updated` date DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT 1,
  `Brand` varchar(100) DEFAULT 'Scentora'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `User_ID`, `Product_Name`, `Product_Price`, `Stock_Status`, `Product_Code`, `Category`, `Image_URL`, `Date_Added`, `Date_Updated`, `Is_Active`, `Brand`) VALUES
(2, 1, 'Test1', 1.00, 'In stock (32 pcs)', '123', 'Floral', 'uploads/wo_mu.png', '2025-07-08', '2025-07-14', 1, 'Scentora'),
(3, 1, 'lead dev for sale', 21.00, 'In stock (2 pcs)', '344', 'Floral', 'uploads/developer.jpg', '2025-07-08', '2025-07-14', 1, 'randomhuman'),
(4, 1, 'random guy', 12.00, 'In stock (12 pcs)', '12', 'Floral', 'uploads/developer.jpg', '2025-07-14', '2025-07-14', 1, 'Scentora'),
(5, 1, 'jabol monster', 69.00, 'In stock (69 pcs)', '69', 'Floral', 'uploads/developer.jpg', '2025-07-14', '2025-07-14', 1, 'khit'),
(6, 1, 'random ass perfume', 100.00, 'In stock (21 pcs)', 'random1', 'Fresh', 'uploads/flo_es.png', '2025-07-14', '2025-07-14', 1, 'Scentora');

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
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `Name`, `Email`, `Address`, `User_Type`, `Password`, `reset_token`, `reset_expiry`) VALUES
(1, 'AdminUser', 'admin@example.com', 'Cebu City', 'Admin', 'admin123', NULL, NULL),
(2, 'ConsumerUser', 'customer@example.com', 'Cebu City', 'Consumer', 'customer123', NULL, NULL),
(3, 'EmployeeUser', 'employee@gmail.com', 'Lapu-Lapu City', 'Employee', 'employee123', NULL, NULL),
(4, 'dave lang', 'dave@test.com', '', 'Consumer', '$2y$10$xKZ57il.hkE15hUT/Xx95uQ7H5cpNwe1zTWE3kO22rmCYBuyiinlu', NULL, NULL),
(5, 'Dave Lagunda', 'davelagunda@gmail.com', '', 'Consumer', '$2y$10$PQnRSc6.mvGfzS8lm3ra3ejk4MXjVtaxj/Z0ClKJmzVq3Pqfr87iq', 'b0a7dcd93d000baa20844288b58d46209d9fdaa76fa1a9817d65552389f55964', '2025-07-15 16:45:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admindetails`
--
ALTER TABLE `admindetails`
  ADD PRIMARY KEY (`User_ID`);

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
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admindetails`
--
ALTER TABLE `admindetails`
  ADD CONSTRAINT `admindetails_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);

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
