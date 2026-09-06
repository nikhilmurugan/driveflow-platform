-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 02:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `carproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ADMIN_ID` varchar(255) NOT NULL,
  `ADMIN_PASSWORD` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ADMIN_ID`, `ADMIN_PASSWORD`) VALUES
('ADMIN', 'ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BOOK_ID` int(11) NOT NULL,
  `CAR_ID` int(11) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `BOOK_PLACE` varchar(255) NOT NULL,
  `BOOK_DATE` date NOT NULL,
  `DURATION` int(11) NOT NULL,
  `PHONE_NUMBER` bigint(20) NOT NULL,
  `DESTINATION` varchar(255) NOT NULL,
  `RETURN_DATE` date NOT NULL,
  `PRICE` int(11) NOT NULL,
  `BOOK_STATUS` varchar(255) NOT NULL DEFAULT 'UNDER PROCESSING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BOOK_ID`, `CAR_ID`, `EMAIL`, `BOOK_PLACE`, `BOOK_DATE`, `DURATION`, `PHONE_NUMBER`, `DESTINATION`, `RETURN_DATE`, `PRICE`, `BOOK_STATUS`) VALUES
(73, 20, 'nikhil@gmail.com', 'vellore', '2025-01-30', 1, 0, 'chennai', '2025-01-31', 1000, 'APPROVED'),
(74, 20, 'nikhil@gmail.com', 'vellore', '2025-01-30', 1, 0, 'chennai', '2025-01-31', 1000, 'REJECTED'),
(75, 20, 'nikhil@gmail.com', 'vellore', '2025-01-30', 1, 0, 'hy', '2025-01-31', 1000, 'APPROVED'),
(78, 20, 'pathaan@gmail.com', 'vellore', '2025-02-28', 1, 0, 'bangalore', '2025-03-01', 1000, 'CONFIRMED'),
(79, 22, 'pathaan@gmail.com', 'vellore', '2025-02-28', 2, 0, 'kerala', '2025-03-02', 3000, 'CONFIRMED'),
(81, 30, 'nikhil1232@gmail.com', 'vellore', '2025-03-03', 1, 8798546005, 'chennai', '2025-03-04', 4200, 'CONFIRMED'),
(82, 53, 'nikhil1232@gmail.com', 'vellore', '2025-03-05', 1, 8798546005, 'chennai', '2025-03-06', 4199, 'APPROVED'),
(84, 22, 'nikhil1232@gmail.com', 'vellore', '2025-03-17', 2, 8798546005, 'mysore', '2025-03-19', 8400, 'CONFIRMED'),
(85, 31, 'nikhil1232@gmail.com', 'vellore', '2025-03-17', 8, 8798546005, 'bangalore', '2025-03-25', 39992, 'CONFIRMED'),
(86, 35, 'nikhil1232@gmail.com', 'vellore', '2025-03-20', 3, 8798546005, 'chennai', '2025-03-23', 10197, 'CONFIRMED'),
(87, 32, 'nikhil1232@gmail.com', 'vellore', '2025-03-20', 1, 8798546005, 'bangalore', '2025-03-21', 6999, 'CANCELLED');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `CAR_ID` int(11) NOT NULL,
  `CAR_NAME` varchar(255) NOT NULL,
  `FUEL_TYPE` varchar(255) NOT NULL,
  `CAPACITY` int(11) NOT NULL,
  `PRICE` int(11) NOT NULL,
  `CAR_IMG` varchar(255) NOT NULL,
  `AVAILABLE` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`CAR_ID`, `CAR_NAME`, `FUEL_TYPE`, `CAPACITY`, `PRICE`, `CAR_IMG`, `AVAILABLE`) VALUES
(20, 'SWIFT', 'Diesel', 4, 2200, 'IMG-6239c94ea8a4a0.51789849.jpg', 'YES'),
(22, 'chevrolet Tavera', 'Diesel', 9, 4200, 'IMG-67c15a0fad99e0.24429126.webp', 'YES'),
(23, 'Ertiga', 'petrol', 7, 3500, 'IMG-67c2c2e0505904.07748844.webp', 'YES'),
(24, 'wagon r', 'petrol', 4, 2300, 'IMG-67c2c38293e938.25031704.webp', 'YES'),
(25, 'jimny', 'petrol', 5, 4600, 'IMG-67c2c4a47bc5c7.79927169.webp', 'YES'),
(26, 'ciaz', 'petrol', 5, 4400, 'IMG-67c2c4fd07b179.70845872.webp', 'YES'),
(27, 'eeco', 'CNG', 7, 2800, 'IMG-67c2c55f68f782.13457542.webp', 'YES'),
(28, 'Scorpio', 'Diesel', 8, 5200, 'IMG-67c2c602a5a7d4.68241319.webp', 'YES'),
(29, 'Thar', 'Diesel', 5, 5400, 'IMG-67c2c67a2d1052.11495314.webp', 'YES'),
(30, 'Bolero', 'Diesel', 6, 4200, 'IMG-67c2c6d7eff2a3.90555004.webp', 'YES'),
(31, 'Marazzo', 'petrol', 8, 4999, 'IMG-67c2c791e87ed4.42157920.webp', 'Y'),
(32, 'Fortuner', 'Diesel', 7, 6999, 'IMG-67c2c850de2889.20327466.webp', 'YES'),
(33, 'Innova Crysta', 'petrol', 6, 5499, 'IMG-67c2c8b3aec634.98540439.webp', 'YES'),
(34, 'Rumion', 'petrol', 7, 3799, 'IMG-67c2c9297d1106.71370652.webp', 'YES'),
(35, 'Glanza', 'petrol', 5, 3399, 'IMG-67c2c974c03d48.59456127.webp', 'YES'),
(36, 'Carens', 'petrol', 8, 5399, 'IMG-67c2c9de3d5ee7.11304274.webp', 'YES'),
(37, 'Seltos', 'petrol', 5, 5199, 'IMG-67c2ca53dd3e21.56433967.webp', 'YES'),
(38, 'i20 N Line', 'petrol', 4, 3999, 'IMG-67c2caae8b1739.97093891.webp', 'YES'),
(39, 'verna', 'petrol', 5, 5499, 'IMG-67c2caf8e059b2.00292983.webp', 'YES'),
(40, 'Exter', 'petrol', 5, 4999, 'IMG-67c2cb45a621f9.79040169.webp', 'YES'),
(41, 'Creta', 'petrol', 5, 5799, 'IMG-67c2cb85178245.06930293.webp', 'YES'),
(42, 'Tiago EV', 'Electric', 4, 3199, 'IMG-67c2cbf06c7991.46629957.webp', 'YES'),
(43, 'Safari', 'petrol', 8, 5699, 'IMG-67c2cc4c938aa0.94221480.webp', 'YES'),
(44, 'Altroz', 'petrol', 4, 3649, 'IMG-67c2cca4283dc2.71157259.webp', 'YES'),
(45, 'Tigor EV', 'Electric', 4, 3899, 'IMG-67c2cd05858f52.87743768.webp', 'YES'),
(46, 'Nexon ', 'Diesel', 4, 3299, 'IMG-67c2cd7d170892.19064734.webp', 'Y'),
(47, 'Hector', 'Diesel', 7, 5619, 'IMG-67c2cdf0ea3dc1.27953679.webp', 'YES'),
(48, 'Amaze', 'petrol', 4, 4849, 'IMG-67c2ce3ce77f53.10235512.webp', 'YES'),
(49, 'City', 'petrol', 5, 5249, 'IMG-67c2ce7db1d490.24234587.webp', 'YES'),
(50, 'Slavia', 'petrol', 5, 3999, 'IMG-67c2cee5bd1603.79685920.webp', 'YES'),
(51, 'Kushaq', 'Diesel', 5, 5799, 'IMG-67c2cf588b65a2.40142118.webp', 'Y'),
(52, 'Virtus', 'petrol', 5, 4399, 'IMG-67c2d00a6c24c7.38000729.webp', 'Y'),
(53, 'Tiguan', 'petrol', 5, 4199, 'IMG-67c2d05ec2ba83.10965160.webp', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `submission_date` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `submission_date`, `status`) VALUES
(1, 'nikhil', 'nikhil@gmail.com', '1472583692', 'DFGDFDFRER', 'GFGRTYFR', '2025-01-27 08:22:37', 'read'),
(2, 'iugiuf', 'kjabvf@faf.cos', '9633585164', 'giug', 'fasdfdsfdsfdf', '2025-01-27 08:25:43', 'read'),
(3, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'read'),
(5, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(6, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(7, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(8, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(9, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(10, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(11, 'tiger ', 'nikhil1232@gmail.com', NULL, 'travelled places', 'fesdfdsf', '0000-00-00 00:00:00', 'unread'),
(12, 'pathaan', 'pathaan@gmail.com', NULL, 'travelled places', 'it is good ', '0000-00-00 00:00:00', 'unread'),
(13, 'nikhil', 'nikhil1232@gmail.com', NULL, 'travelled places', 'hiiiiiii', '0000-00-00 00:00:00', 'unread'),
(14, 'nikhil', 'nikhil@gmail.com', NULL, 'travelled places', 'good', '0000-00-00 00:00:00', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FED_ID` int(11) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `COMMENT` text NOT NULL,
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FED_ID`, `EMAIL`, `rating`, `COMMENT`, `feedback_date`) VALUES
(11, 'nikhil@gmail.com', 0, 'i was amazing', '2025-03-01 07:12:12'),
(12, 'pathaan@gmail.com', 3, 'good', '2025-03-01 07:13:39'),
(13, 'pathaan@gmail.com', 3, 'good\r\n', '2025-03-01 07:13:51'),
(14, 'pathaan@gmail.com', 3, 'mhbv', '2025-03-01 09:37:05'),
(15, 'nikhil1232@gmail.com', 5, 'GOOD EXPERIENCE', '2025-03-12 05:53:37'),
(16, 'nikhil1232@gmail.com', 4, 'good', '2025-03-19 05:27:32');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAY_ID` int(11) NOT NULL,
  `BOOK_ID` int(11) NOT NULL,
  `CARD_NO` varchar(255) NOT NULL,
  `EXP_DATE` varchar(255) NOT NULL,
  `CVV` int(11) NOT NULL,
  `PRICE` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `FNAME` varchar(255) NOT NULL,
  `LNAME` varchar(255) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `LIC_NUM` varchar(255) NOT NULL,
  `PHONE_NUMBER` bigint(11) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `GENDER` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`FNAME`, `LNAME`, `EMAIL`, `LIC_NUM`, `PHONE_NUMBER`, `PASSWORD`, `GENDER`) VALUES
('bucks', 'karti', 'bucks12@gmail.com', '654987', 1234567890, '176db0bcc0f0d7c10f8f0d7051249203', 'male'),
('R.K', 'bharat', 'bharat@gmail.com', '1A2B3C', 0123456789, 'ee2059f5585eb6419b60b42d847eef28', 'male'),
('susa ', 'sudharsan', 'susa@gmail.com', '123456dsd', 0123456788, 'ee2059f5585eb6419b60b42d847eef28', 'male'),
('nikhil', 'murugan', 'nikhil@gmail.com', '123456', 123456777, 'eb0ae219c6f46f2ef748738067caa7d7', 'male'),
('surya', 'rai', 'rai@gmail.com', '65498712', 1234567899, 'ee2059f5585eb6419b60b42d847eef28', 'male'),
('suren', 'choudary', 'surenchoudary@gmail.com', 'B2343', 1234567555, 'c788b480e4a3c807a14b6f3f4b1a1ae6', 'male'),
('lokesh', 'kumar', 'lokeshkumar@gmail.com', '654987', 123456111, 'cfcc63102f655706b34e4e71f7592505', 'male');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ADMIN_ID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BOOK_ID`),
  ADD KEY `CAR_ID` (`CAR_ID`),
  ADD KEY `EMAIL` (`EMAIL`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`CAR_ID`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FED_ID`),
  ADD KEY `TEST` (`EMAIL`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PAY_ID`),
  ADD UNIQUE KEY `BOOK_ID` (`BOOK_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`EMAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BOOK_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `CAR_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FED_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`CAR_ID`) REFERENCES `cars` (`CAR_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`EMAIL`) REFERENCES `users` (`EMAIL`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `TEST` FOREIGN KEY (`EMAIL`) REFERENCES `users` (`EMAIL`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`BOOK_ID`) REFERENCES `booking` (`BOOK_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
