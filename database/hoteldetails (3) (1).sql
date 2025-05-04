-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 05:49 AM
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
-- Database: `hoteldetails`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$fHLSEv0pluiDoJwlLqVXZeyhBmsSwcOdYYw9rfWKwYdXgmYwvTgqq', 'Administrator', 'admin@example.com', '2025-04-02 08:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `hotel_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_range` varchar(100) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `google_review_link` text DEFAULT NULL,
  `facebook_link` text DEFAULT NULL,
  `instagram_link` text DEFAULT NULL,
  `dining_menu` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `logo_of_hotel` text DEFAULT NULL,
  `google_map_background` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `visit_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `hotel_name`, `location`, `description`, `price_range`, `amenities`, `phone`, `whatsapp`, `google_review_link`, `facebook_link`, `instagram_link`, `dining_menu`, `image_url`, `logo_of_hotel`, `google_map_background`, `email`, `website`, `created_at`, `visit_count`) VALUES
(8, 'Hotel 1 ', 'Phaphamau Prayagraj', 'A hotel is a commercial establishment that offers lodging and meals to travelers and sometimes to permanent residents, often with restaurants, meeting rooms, and stores available to the public. ', '9000', 'free WiFi, hair dryer, toiletries, free breakfast, free parking, bathroom accessories, room service', '9026023171', '9026023171', 'https://yashinfosystem.com/', 'https://yashinfosystem.com/', 'https://yashinfosystem.com/', 'Breakfast, Lunch, Dinner', 'uploads/hotel_67efd7cd363211.64571099.jpg', NULL, NULL, 'Dineshtiwari@gmail.com', 'https://yashinfosystem.com/', '2025-04-04 12:59:57', 5),
(16, 'kjkklklb', 'Lucknow Hazratganj ', 'ioiyutivi    uy gi go  ug ', '8000', 'g g ug ', '9067654534', '9878765654', 'https://yashinfosystem.com/', 'https://yashinfosystem.com/', 'https://yashinfosystem.com/', 'dinesh,tiwari', 'uploads/hotel_image_67fdc2cca248f4.01742069.jpg', 'uploads/logo_of_hotel_67fdc2cca33b72.59376754.jpg', 'uploads/map_background_67fdc2cca2c840.85485964.jpg', 'Dineshtiwari@gmail.com', 'https://yashinfosystem.com/', '2025-04-15 02:22:04', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `hotel_id`, `message`, `created_at`, `is_read`) VALUES
(298, 8, 'New visit to Hotel 1 ', '2025-04-09 12:12:29', 0),
(439, 8, 'New visit to Hotel 1 ', '2025-04-13 09:20:46', 0),
(443, 8, 'New visit to Hotel 1 ', '2025-04-14 13:44:13', 0),
(444, 8, 'New visit to Hotel 1 ', '2025-04-14 15:31:15', 0),
(445, 8, 'New visit to Hotel 1 ', '2025-04-15 02:22:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `sub_category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `category`, `sub_category`, `description`) VALUES
(1, 'Bara Imambara', 'Historical', 'Monument', 'Grand structure with Bhool Bhulaiya (labyrinth)'),
(2, 'Chota Imambara', 'Historical', 'Monument', 'Ornate interior, chandeliers, tombs'),
(3, 'Rumi Darwaza', 'Historical', 'Gateway', 'Iconic symbol of Lucknow, Awadhi architecture'),
(4, 'British Residency', 'Historical', 'Ruins', '1857 revolt historical site'),
(5, 'La Martiniere College', 'Historical', 'Institution', 'French-inspired historical building'),
(6, 'Clock Tower', 'Historical', 'Tower', 'Tallest clock tower in India'),
(7, 'Dilkusha Palace', 'Historical', 'Ruins', 'Garden palace in British-Nawabi style'),
(8, 'Satkhanda', 'Historical', 'Watchtower', 'Unfinished Mughal-style tower'),
(9, 'Chattar Manzil', 'Historical', 'Palace', 'Umbrella-shaped dome, historic palace'),
(10, 'Janeshwar Mishra Park', 'Nature', 'Park', 'Jogging, boating, cycling, Asia’s largest urban park'),
(11, 'Gomti Riverfront Park', 'Nature', 'Riverfront', 'Landscaped riverwalk, light shows'),
(12, 'Lohia Park', 'Nature', 'Park', 'Green spaces, walking tracks'),
(13, 'Ambedkar Memorial Park', 'Nature', 'Memorial Park', 'Statues, monuments, sandstone architecture'),
(14, 'Lucknow Zoo', 'Nature', 'Zoo', 'Toy train, animals, heritage train'),
(15, 'Botanical Garden (NBRI)', 'Nature', 'Garden', 'Medicinal and rare plants, peaceful walk area'),
(16, 'Hazratganj Market', 'Shopping', 'Market', 'British-era shops, cafes, showrooms'),
(17, 'Aminabad Market', 'Shopping', 'Market', 'Chikankari clothes, street food'),
(18, 'Chowk Bazaar', 'Shopping', 'Local Market', 'Traditional items, kebabs, handicrafts'),
(19, 'Phoenix Palassio', 'Shopping', 'Mall', 'Luxury brands, food court, multiplex'),
(20, 'Lulu Mall', 'Shopping', 'Mall', 'One of India’s largest malls, family-friendly'),
(21, 'Tunday Kababi', 'Food', 'Restaurant', 'World-famous galouti kebabs'),
(22, 'Dastarkhwan', 'Food', 'Restaurant', 'Mughlai and Awadhi cuisine'),
(23, 'Rahim\'s Nihari', 'Food', 'Eatery', 'Nihari and kulcha, local legend'),
(24, 'Royal Café', 'Food', 'Café', 'Basket chaat, fast food'),
(25, 'Idris Biryani', 'Food', 'Street Food', 'Authentic Lucknow biryani'),
(26, 'Prakash Kulfi', 'Food', 'Dessert', 'Legendary kulfi joint in Aminabad'),
(27, 'Jama Masjid', 'Religious', 'Mosque', 'Beautiful design, prayer site'),
(28, 'St. Joseph’s Cathedral', 'Religious', 'Church', 'Historic colonial architecture'),
(29, 'Hanuman Setu Temple', 'Religious', 'Temple', 'Near Gomti river, crowded on Tuesdays'),
(30, 'Aliganj Hanuman Temple', 'Religious', 'Temple', 'Devotees flock on Tuesdays'),
(31, 'Gurudwara Yahiyaganj', 'Religious', 'Gurudwara', 'Peaceful Sikh worship place'),
(32, 'State Museum', 'Culture', 'Museum', 'Ancient artifacts, paintings, sculptures'),
(33, 'Indira Gandhi Planetarium', 'Culture', 'Science', 'Astronomy shows, fun for kids'),
(34, 'Hussainabad Picture Gallery', 'Culture', 'Art', 'Nawabi-era portraits'),
(35, 'State Archives Museum', 'Culture', 'Archive', 'Ancient records, historical manuscripts');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL DEFAULT 'Guest Name',
  `hotel_id` int(11) NOT NULL,
  `overall_rating` int(11) NOT NULL,
  `rooms_rating` int(11) NOT NULL DEFAULT 0,
  `service_rating` int(11) NOT NULL DEFAULT 0,
  `location_rating` int(11) NOT NULL DEFAULT 0,
  `experience` text DEFAULT NULL,
  `trip_type` varchar(50) DEFAULT NULL,
  `travel_with` varchar(50) DEFAULT NULL,
  `hotel_description` text DEFAULT NULL,
  `topics` text DEFAULT NULL,
  `media` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tv_channels`
--

CREATE TABLE `tv_channels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tv_channels`
--

INSERT INTO `tv_channels` (`id`, `name`, `category`, `language`) VALUES
(1, 'StarPlus', 'General Entertainment', 'Hindi'),
(2, 'Sony Entertainment Television', 'General Entertainment', 'Hindi'),
(3, 'Zee TV', 'General Entertainment', 'Hindi'),
(4, 'Colors TV', 'General Entertainment', 'Hindi'),
(5, 'Sony SAB', 'General Entertainment', 'Hindi'),
(6, 'Star World', 'General Entertainment', 'English'),
(7, 'Zee Café', 'General Entertainment', 'English'),
(8, 'Sun TV', 'General Entertainment', 'Tamil'),
(9, 'Star Maa', 'General Entertainment', 'Telugu'),
(10, 'Asianet', 'General Entertainment', 'Malayalam'),
(11, 'Colors Kannada', 'General Entertainment', 'Kannada'),
(12, 'Sony Max', 'Movies', 'Hindi'),
(13, 'Zee Cinema', 'Movies', 'Hindi'),
(14, 'Star Gold', 'Movies', 'Hindi'),
(15, 'HBO', 'Movies', 'English'),
(16, 'Star Movies', 'Movies', 'English'),
(17, 'KTV', 'Movies', 'Tamil'),
(18, 'Udaya Movies', 'Movies', 'Kannada'),
(19, 'Zee Talkies', 'Movies', 'Marathi'),
(20, 'NDTV 24x7', 'News', 'English'),
(21, 'Times Now', 'News', 'English'),
(22, 'Republic TV', 'News', 'English'),
(23, 'Aaj Tak', 'News', 'Hindi'),
(24, 'Zee News', 'News', 'Hindi'),
(25, 'India TV', 'News', 'Hindi'),
(26, 'ABP News', 'News', 'Hindi'),
(27, 'ABP Ananda', 'News', 'Bengali'),
(28, 'Sun News', 'News', 'Tamil'),
(29, 'Asianet News', 'News', 'Malayalam'),
(30, 'Star Sports 1', 'Sports', 'English'),
(31, 'Star Sports 2', 'Sports', 'English'),
(32, 'Star Sports Hindi', 'Sports', 'Hindi'),
(33, 'Sony Sports Ten 1', 'Sports', 'English'),
(34, 'Sony Sports Ten 2', 'Sports', 'English'),
(35, 'DD Sports', 'Sports', 'Hindi'),
(36, 'MTV India', 'Music', 'Hindi'),
(37, '9XM', 'Music', 'Hindi'),
(38, 'B4U Music', 'Music', 'Hindi'),
(39, 'Mastiii', 'Music', 'Hindi'),
(40, 'VH1 India', 'Music', 'English'),
(41, 'Cartoon Network', 'Kids', 'English'),
(42, 'Nickelodeon India', 'Kids', 'English'),
(43, 'Pogo TV', 'Kids', 'English'),
(44, 'Disney Channel India', 'Kids', 'English'),
(45, 'Hungama TV', 'Kids', 'Hindi'),
(46, 'Sonic', 'Kids', 'English'),
(47, 'Discovery Channel', 'Infotainment', 'English'),
(48, 'National Geographic', 'Infotainment', 'English'),
(49, 'History TV18', 'Infotainment', 'English'),
(50, 'Animal Planet', 'Infotainment', 'English'),
(51, 'Sony BBC Earth', 'Infotainment', 'English'),
(52, 'Aastha TV', 'Religious', 'Hindi'),
(53, 'Sanskar TV', 'Religious', 'Hindi'),
(54, 'Zee Salaam', 'Religious', 'Urdu'),
(55, 'Subh TV', 'Religious', 'Hindi'),
(56, 'Peace TV', 'Religious', 'Urdu'),
(57, 'TLC India', 'Lifestyle', 'English'),
(58, 'Fox Life', 'Lifestyle', 'English'),
(59, 'NDTV Good Times', 'Lifestyle', 'English'),
(60, 'Discovery Turbo', 'Lifestyle', 'English'),
(61, 'Living Foodz', 'Lifestyle', 'Hindi'),
(62, 'DD Gyan Darshan', 'Educational', 'Hindi'),
(63, 'Swayam Prabha', 'Educational', 'Hindi'),
(64, 'PM eVidya', 'Educational', 'Hindi'),
(65, 'Vande Gujarat', 'Educational', 'Gujarati'),
(66, 'Kalvi TV', 'Educational', 'Tamil');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `website` (`image_url`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `tv_channels`
--
ALTER TABLE `tv_channels`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=446;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
