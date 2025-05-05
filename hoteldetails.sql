-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 03:59 PM
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
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `clients_email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `hotel_id`, `clients_email`, `password`, `created_at`, `updated_at`) VALUES
(10, 71, 'lko@gmail.com', '1234', '2025-05-05 14:10:28', '2025-05-05 14:10:28');

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
  `website` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `visit_count` int(11) DEFAULT 0,
  `plan_type` int(11) DEFAULT NULL,
  `landing_page_enabled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `hotel_name`, `location`, `description`, `price_range`, `amenities`, `phone`, `whatsapp`, `google_review_link`, `facebook_link`, `instagram_link`, `dining_menu`, `image_url`, `logo_of_hotel`, `google_map_background`, `email`, `website`, `created_at`, `visit_count`, `plan_type`, `landing_page_enabled`) VALUES
(69, 'LKO HOTEL', 'Lucknow Durgapuri', '', NULL, NULL, '9532829920', '7899654587', 'https://www.google.co.in/maps/place/YASH+INFOSYSTEMS/@26.8489028,80.7776979,11z/data=!3m1!4b1!4m6!3m5!1s0x48db959e3537b4c7:0xa98fe84ae1f97761!8m2!3d26.848692!4d80.9425127!16s%2Fg%2F11vxjw3dn8?entry=ttu&g_ep=EgoyMDI1MDQwMi4xIKXMDSoASAFQAw%3D%3D', 'https://www.facebook.com/HotelRanbir', 'https://www.instagram.com/ranbirshotel/', '', 'uploads/hotel_image_6818601df18754.72410286.jpg', 'uploads/logo_of_hotel_681860618cfae1.60839210.png', '', 'hotelLko@gmail.com', 'https://www.yashinfotec.in', '2025-05-05 06:52:13', 10, 1, 1),
(70, 'LKO HOTEL', 'Lucknow Durgapuri', '', '7000 - 8000', 'Pool, Restaurant,swimming', '9532829920', '9026023171', 'https://www.google.co.in/maps/place/YASH+INFOSYSTEMS/@26.8489028,80.7776979,11z/data=!3m1!4b1!4m6!3m5!1s0x48db959e3537b4c7:0xa98fe84ae1f97761!8m2!3d26.848692!4d80.9425127!16s%2Fg%2F11vxjw3dn8?entry=ttu&g_ep=EgoyMDI1MDQwMi4xIKXMDSoASAFQAw%3D%3D', 'https://www.facebook.com/HotelRanbir', 'https://www.instagrm.com/ranbirshotel/', '', 'uploads/hotel_image_6818615d16d423.09295473.jpg', 'uploads/logo_of_hotel_6818615d174db0.54632471.png', '', 'hotelLkeo@gmail.com', 'https://yashinfosystem.com/', '2025-05-05 06:57:33', 8, 2, 1),
(71, 'LKO HOTEL', 'Lucknow Durgapuri', '', '7000 - 8000', 'Pool, restaurant, Loundry', '9532829920', '9026023171', 'https://www.google.co.in/maps/place/YASH+INFOSYSTEMS/@26.8489028,80.7776979,11z/data=!3m1!4b1!4m6!3m5!1s0x48db959e3537b4c7:0xa98fe84ae1f97761!8m2!3d26.848692!4d80.9425127!16s%2Fg%2F11vxjw3dn8?entry=ttu&g_ep=EgoyMDI1MDQwMi4xIKXMDSoASAFQAw%3D%3D', 'https://www.facebook.com/HotelRanbir', 'https://www.instagrm.com/ranbirshotel/', 'https://www.yashinfosystem.com', 'uploads/hotel_image_6818a3aa1f75d1.26393846.jpg', 'uploads/logo_of_hotel_6818a3aa1fb330.98404733.jpg', '', 'lko@gmail.com', 'https://yashinfosystem.com/', '2025-05-05 07:00:46', 32, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `hotel_amenities`
--

CREATE TABLE `hotel_amenities` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `hotel_amenities` varchar(100) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_amenities`
--

INSERT INTO `hotel_amenities` (`id`, `hotel_id`, `hotel_amenities`, `is_available`, `remarks`) VALUES
(1, NULL, 'Free Wi-Fi', 1, 'High-speed internet throughout the property'),
(2, NULL, 'Parking', 1, 'Free private parking on site'),
(3, NULL, 'Swimming Pool', 1, 'Outdoor pool with lounge chairs'),
(4, NULL, 'Spa & Wellness', 1, 'Open 10 AM to 8 PM'),
(5, NULL, 'Gym / Fitness Center', 0, 'Currently under renovation'),
(6, NULL, 'Restaurant', 1, 'Multi-cuisine dining with vegetarian options'),
(7, NULL, 'Room Service', 1, '24/7 service'),
(8, NULL, 'Airport Shuttle', 1, 'On request (charges may apply)'),
(9, NULL, 'Pet Friendly', 0, 'Pets are not permitted'),
(10, NULL, 'Conference Hall', 1, 'Up to 100 people capacity');

-- --------------------------------------------------------

--
-- Table structure for table `item_visibility`
--

CREATE TABLE `item_visibility` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_visibility`
--

INSERT INTO `item_visibility` (`id`, `hotel_id`, `item_name`, `is_visible`) VALUES
(1, 69, 'google_review', 1),
(2, 69, 'facebook', 1),
(3, 69, 'instagram', 1),
(4, 69, 'whatsapp', 1),
(5, 69, 'phone', 1),
(6, 69, 'local_attractions', 1),
(7, 69, 'dining_menu', 1),
(8, 69, 'amenities', 1),
(9, 69, 'tv_channels', 1),
(10, 69, 'email', 0),
(11, 69, 'offers', 1),
(12, 69, 'check_in', 1),
(13, 69, 'wifi', 0),
(14, 69, 'pay_us', 0),
(15, 69, 'travel_destinations', 0),
(16, 69, 'compass', 0),
(17, 71, 'google_review', 1),
(18, 71, 'facebook', 1),
(19, 71, 'instagram', 1),
(20, 71, 'whatsapp', 1),
(21, 71, 'phone', 1),
(22, 71, 'local_attractions', 1),
(23, 71, 'dining_menu', 1),
(24, 71, 'amenities', 0),
(25, 71, 'tv_channels', 0),
(26, 71, 'email', 0),
(27, 71, 'offers', 0),
(28, 71, 'check_in', 0),
(29, 71, 'wifi', 1),
(30, 71, 'pay_us', 0),
(31, 71, 'travel_destinations', 0),
(32, 71, 'compass', 1);

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
  `guest_name` varchar(255) NOT NULL DEFAULT 'Dear Guest',
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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `guest_name`, `hotel_id`, `overall_rating`, `rooms_rating`, `service_rating`, `location_rating`, `experience`, `trip_type`, `travel_with`, `hotel_description`, `topics`, `media`, `created_at`) VALUES
(67, 'Dear Guest', 71, 2, 0, 0, 0, '', '', '', NULL, NULL, NULL, '2025-05-05 10:53:15'),
(68, 'Dear Guest', 71, 2, 0, 0, 0, '', '', '', NULL, NULL, NULL, '2025-05-05 10:54:28'),
(69, 'Dear Guest', 71, 2, 0, 0, 0, '', '', '', NULL, NULL, 'review_68189913546cf4.11427858.png', '2025-05-05 10:55:15'),
(70, 'Dear Guest', 71, 2, 0, 0, 0, '', '', '', NULL, NULL, 'review_681899ae4ff222.93547874.png', '2025-05-05 10:57:50'),
(71, 'Dear Guest', 71, 2, 0, 0, 0, '', '', '', NULL, NULL, 'uploads/reviews/review_681899fa4d2d71.60692615.png', '2025-05-05 10:59:06'),
(72, 'Dear Guest', 71, 2, 0, 0, 0, '', '', '', NULL, NULL, '../../uploads/reviews/review_6818a0f3bbc4b2.39992363.PNG', '2025-05-05 11:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `service_clicks`
--

CREATE TABLE `service_clicks` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `click_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_clicks`
--

INSERT INTO `service_clicks` (`id`, `hotel_id`, `service_name`, `click_count`, `created_at`, `updated_at`) VALUES
(1, 66, 'Google Review', 8, '2025-05-02 12:05:24', '2025-05-04 06:37:20'),
(2, 66, 'Facebook', 1, '2025-05-02 12:07:29', '2025-05-02 12:07:29'),
(3, 66, 'Instagram', 2, '2025-05-02 12:08:08', '2025-05-02 12:08:27'),
(4, 66, 'Dining Menu', 4, '2025-05-02 12:32:37', '2025-05-05 06:01:34'),
(5, 66, 'WhatsApp', 2, '2025-05-02 12:32:43', '2025-05-05 05:57:19'),
(6, 66, 'Phone Call', 2, '2025-05-02 12:32:47', '2025-05-05 05:57:34'),
(7, 66, 'Find Us', 1, '2025-05-02 12:32:54', '2025-05-02 12:32:54'),
(8, 66, 'Amenities', 2, '2025-05-02 12:32:59', '2025-05-02 13:23:35'),
(9, 66, 'Local Attractions', 2, '2025-05-02 12:33:06', '2025-05-05 05:46:09'),
(10, 67, 'Google Review', 3, '2025-05-02 12:35:04', '2025-05-04 06:37:31'),
(11, 67, 'WhatsApp', 1, '2025-05-02 12:35:12', '2025-05-02 12:35:12'),
(12, 67, 'Local Attractions', 1, '2025-05-02 12:35:19', '2025-05-02 12:35:19'),
(13, 67, 'Phone Call', 1, '2025-05-02 12:35:23', '2025-05-02 12:35:23'),
(14, 66, 'TV Channels', 2, '2025-05-02 13:23:18', '2025-05-02 13:23:23'),
(15, 68, 'Local Attractions', 1, '2025-05-05 05:48:22', '2025-05-05 05:48:22'),
(16, 68, 'Places', 1, '2025-05-05 05:48:34', '2025-05-05 05:48:34'),
(17, 66, 'Travel Destinations', 1, '2025-05-05 06:02:25', '2025-05-05 06:02:25'),
(18, 69, 'Google Review', 4, '2025-05-05 06:52:42', '2025-05-05 08:38:43'),
(19, 69, 'WhatsApp', 1, '2025-05-05 06:52:51', '2025-05-05 06:52:51'),
(20, 69, 'Local Attractions', 2, '2025-05-05 06:52:57', '2025-05-05 08:32:37'),
(21, 71, 'Google Review', 18, '2025-05-05 07:03:52', '2025-05-05 12:53:09'),
(22, 71, 'Dining Menu', 1, '2025-05-05 07:08:58', '2025-05-05 07:08:58'),
(23, 71, 'WhatsApp', 1, '2025-05-05 12:53:02', '2025-05-05 12:53:02');

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
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_email` (`clients_email`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotel_amenities`
--
ALTER TABLE `hotel_amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_visibility`
--
ALTER TABLE `item_visibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hotel_item` (`hotel_id`,`item_name`);

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
-- Indexes for table `service_clicks`
--
ALTER TABLE `service_clicks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tv_channels`
--
ALTER TABLE `tv_channels`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `hotel_amenities`
--
ALTER TABLE `hotel_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `item_visibility`
--
ALTER TABLE `item_visibility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `service_clicks`
--
ALTER TABLE `service_clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
