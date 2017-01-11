-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: studmysql01.fhict.local
-- Generation Time: Jan 11, 2017 at 07:33 AM
-- Server version: 5.7.13-log
-- PHP Version: 5.6.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbi358895`
--

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `ID` int(11) NOT NULL,
  `voornaam` varchar(255) NOT NULL,
  `achternaam` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `licence`
--

CREATE TABLE `licence` (
  `ID` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `licencekey` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `ID` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `licence_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `action` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parameter1` text,
  `parameter2` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`ID`, `owner_id`, `user_id`, `stock_id`, `employee_id`, `licence_id`, `product_id`, `action`, `time`, `parameter1`, `parameter2`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, 5, '2017-01-11 07:25:24', NULL, NULL),
(2, 1, 2, NULL, NULL, NULL, NULL, 1, '2017-01-11 07:25:56', NULL, NULL),
(3, 1, 3, NULL, NULL, NULL, NULL, 1, '2017-01-11 07:26:19', NULL, NULL),
(4, 1, 4, NULL, NULL, NULL, NULL, 1, '2017-01-11 07:27:01', NULL, NULL),
(5, 4, NULL, NULL, NULL, NULL, NULL, 5, '2017-01-11 07:27:18', NULL, NULL),
(6, 3, NULL, NULL, NULL, NULL, NULL, 5, '2017-01-11 07:27:23', NULL, NULL),
(7, 2, NULL, NULL, NULL, NULL, NULL, 5, '2017-01-11 07:27:26', NULL, NULL),
(8, 1, NULL, NULL, NULL, NULL, NULL, 5, '2017-01-11 07:29:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `software` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `ID` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `warranty` date DEFAULT NULL,
  `servicetag` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `ip` varchar(15) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `voornaam` varchar(255) NOT NULL,
  `achternaam` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `salt`, `level`, `voornaam`, `achternaam`, `email`) VALUES
(1, 'Raymond', '956363cb6b45f277605b0f54a0547e605f1e37b30f566fbe4a8e49b301311516', 'xhEUGZTWPD7ciHqUW7nN7o64EsarTPF9GqdTnKTUOLiUkVv6Mc9du5htkwwACZWL', 2, 'Raymond', 'Jetten', 'r.jetten@scholt.com'),
(2, 'Freek', '8feba0b9fcdb09320c53424d11d33e79c5f1c843601d777782e903b2a8c432b9', 'QOKSuaY1hKClPe0Uayf428bRB6ZiDtythjmLtlNL5q7VF7PPG5TJd4AObz7O2Fik', 2, 'Freek', 'de Man', 'f.de.man@scholt.com'),
(3, 'Burak', 'a0a9ee457def24b76646202a62a56331a874190f44736b91debf2652fb00655b', 'hZ75molENUMWFZJBu22iZciyO9LoYFCgEJl07HFVBsRhrASWDVeC7waVGWkFBWVg', 2, 'Burak', 'Agyel', 'b.agyel@scholt.com'),
(4, 'Tom', '74cab036ebcab6f63a5006f17a9fdf73eddabf2544e7d8be999e952d71ae5bd9', '8xiPWewNKyEkx1fWI1Rz5neILtabDDwLbOA737VNFz8dBn9kp1Tvp7eaAome2TZd', 2, 'Tom', 'van der Maaten', 't.v.d.maaten@scholt.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `licence`
--
ALTER TABLE `licence`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `licence`
--
ALTER TABLE `licence`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
