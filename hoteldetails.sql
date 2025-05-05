-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 03:36 PM
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
(9, 66, 'info@hotelranbirs.com', '1234', '2025-05-04 13:00:00', '2025-05-04 13:00:00');

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
  `plan_type` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `hotel_name`, `location`, `description`, `price_range`, `amenities`, `phone`, `whatsapp`, `google_review_link`, `facebook_link`, `instagram_link`, `dining_menu`, `image_url`, `logo_of_hotel`, `google_map_background`, `email`, `website`, `created_at`, `visit_count`, `plan_type`) VALUES
(66, 'Hotel test', 'Lucknow Durgapuri devi puji pad kamal tumhare', 'this is for test our logo', '7000 - 80000', 'pool,nul,wsiming', '9532829920', '7899654587', 'https://www.google.co.in/map/place/YASH+INFOSYSTEMS/@26.8489028,80.7776979,11z/data=!3m1!4b1!4m6!3m5!1s0x48db959e3537b4c7:0xa98fe84ae1f97761!8m2!3d26.848692!4d80.9425127!16s%2Fg%2F11vxjw3dn8?entry=ttu&g_ep=EgoyMDI1MDQwMi4xIKXMDSoASAFQAw%3D%3D', 'http://localhost/hotelranirsgomtinagar/dashboard.php/', 'https://www.instagrm.com/ranbirshotel/', 'https://www.yashinfosystem.com', 'uploads/6815aac7251a8.jpg', 'uploads/6815aac725736.png', 'uploads/6815aad7e13e4.png', 'info@hotelranbirs.com', 'https://www.hotel.com', '2025-05-02 10:59:04', 40, 3),
(67, 'Hotel luck', '279/48, Pan Dariba, Kanpur Rd, Mawaiyya, Lucknow, Uttar Pradesh 226004', '', NULL, NULL, '9026023171', '9026023171', 'https://yashinfosystem.com/', 'https://www.facebook.com/HotelRanbir', 'https://yashinfosystem.com/', '', '', '', '', 'info@hotelranbirs.com', 'https://www.hotel.com', '2025-05-02 12:34:05', 7, 1);

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `guest_name`, `hotel_id`, `overall_rating`, `rooms_rating`, `service_rating`, `location_rating`, `experience`, `trip_type`, `travel_with`, `hotel_description`, `topics`, `media`, `created_at`) VALUES
(57, 'Guest Name', 66, 2, 2, 1, 2, 'sdafgsadf dasf fasdasd geda dg', 'Business', 'Solo', 'Quiet, High-tech', '{\"Safety\":\"asdf sad f\",\"Noteworthy details\":\" asd fasddf \",\"Food & drinks\":\" sda fsadf \"}', '../../uploads/reviews/review_6816236db8caf1.17543587.png', '2025-05-03 14:08:45'),
(58, 'Guest Name', 66, 2, 3, 3, 3, 'asdf fw sad fsdaa fsaddf asddf ', 'Vacation', 'Couple', 'Quiet, Great Value', '{\"Walkability\":\"sdaf sdf \",\"Noteworthy details\":\"asd fsadf \"}', '../../uploads/reviews/review_68171d1ba27431.23890212.png', '2025-05-04 07:54:03');

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
(4, 66, 'Dining Menu', 2, '2025-05-02 12:32:37', '2025-05-03 05:31:06'),
(5, 66, 'WhatsApp', 1, '2025-05-02 12:32:43', '2025-05-02 12:32:43'),
(6, 66, 'Phone Call', 1, '2025-05-02 12:32:47', '2025-05-02 12:32:47'),
(7, 66, 'Find Us', 1, '2025-05-02 12:32:54', '2025-05-02 12:32:54'),
(8, 66, 'Amenities', 2, '2025-05-02 12:32:59', '2025-05-02 13:23:35'),
(9, 66, 'Local Attractions', 1, '2025-05-02 12:33:06', '2025-05-02 12:33:06'),
(10, 67, 'Google Review', 3, '2025-05-02 12:35:04', '2025-05-04 06:37:31'),
(11, 67, 'WhatsApp', 1, '2025-05-02 12:35:12', '2025-05-02 12:35:12'),
(12, 67, 'Local Attractions', 1, '2025-05-02 12:35:19', '2025-05-02 12:35:19'),
(13, 67, 'Phone Call', 1, '2025-05-02 12:35:23', '2025-05-02 12:35:23'),
(14, 66, 'TV Channels', 2, '2025-05-02 13:23:18', '2025-05-02 13:23:23');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `hotel_amenities`
--
ALTER TABLE `hotel_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `service_clicks`
--
ALTER TABLE `service_clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
