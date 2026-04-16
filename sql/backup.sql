-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 12, 2026 at 01:01 PM
-- Server version: 10.11.5-MariaDB-1:10.11.5+maria~ubu2004-log
-- PHP Version: 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbKk9odIzJeB`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('user','artist','admin') NOT NULL DEFAULT 'user',
  `username` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `role`, `username`, `first_name`, `last_name`, `email`, `password`, `profile_image_path`, `created_at`, `updated_at`) VALUES
(1, 'artist', 'SilverSpire_Ink', 'Naomi', 'Sinclair', 'naomi.sinclair@gmail.com', 'naomi', '', '2026-03-05 05:05:09', '2026-03-11 20:56:41'),
(3, 'admin', 'test', 'test', 'test', 'test@gmail.coom', 'test', '', '2026-03-05 05:23:30', '2026-03-05 05:23:30'),
(6, 'user', 'alex_client', 'Alex', 'Carter', 'alex_client@inkseek.test', 'Alex', '', '2026-03-11 20:12:52', '2026-03-11 20:12:52'),
(7, 'artist', 'marco_black', 'Marco', 'Alvarez', 'marco_black@inkseek.test', 'Marco', '', '2026-03-11 20:13:59', '2026-03-11 20:13:59'),
(8, 'artist', 'emily_lines', 'Emily', 'Shaw', 'emily_lines@inkseek.test', 'Emily', '', '2026-03-11 20:53:52', '2026-03-11 20:53:52'),
(9, 'artist', 'jordan_inkj', 'Jordan', 'Patel', 'jordan_inkj@inkseek.test', 'Jordan', '', '2026-03-11 20:57:13', '2026-03-11 20:57:13'),
(10, 'artist', 'luis_realism', 'Luis', 'Ramirez', 'luis_realism@inkseek.test', 'Luis', '', '2026-03-11 20:58:34', '2026-03-11 20:58:34'),
(11, 'user', 'nina_client', 'Nina', 'Lopez', 'nina_client@inkseek.test', 'Nina', '', '2026-03-11 20:59:17', '2026-03-11 20:59:17'),
(12, 'user', 'david_client', 'David', 'Brooks', 'david_client@inkseek.test', 'David', '', '2026-03-11 20:59:51', '2026-03-11 20:59:51'),
(13, 'user', 'sophia_client', 'Sophia', 'Martinez', 'sophia_client@inkseek.test', 'Sophia', '', '2026-03-11 21:00:28', '2026-03-11 21:00:28'),
(14, 'user', 'thewanderingquill', 'Mayson', 'Quill', 'mayquill225@inkseektest.com', 'Mayson', '', '2026-03-11 21:09:00', '2026-03-11 21:09:00');

-- --------------------------------------------------------

--
-- Table structure for table `account_follows`
--

CREATE TABLE `account_follows` (
  `follower_account_id` bigint(20) UNSIGNED NOT NULL,
  `following_account_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_follows`
--

INSERT INTO `account_follows` (`follower_account_id`, `following_account_id`, `created_at`) VALUES
(11, 1, '2026-03-11 21:06:27'),
(12, 1, '2026-03-11 21:06:38');

-- --------------------------------------------------------

--
-- Table structure for table `artist_profiles`
--

CREATE TABLE `artist_profiles` (
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `about` text DEFAULT NULL,
  `min_rate` decimal(10,2) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `day_rate` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `availability_status` enum('available','limited','booked','unavailable') DEFAULT 'available',
  `mon_start` time DEFAULT NULL,
  `mon_end` time DEFAULT NULL,
  `tue_start` time DEFAULT NULL,
  `tue_end` time DEFAULT NULL,
  `wed_start` time DEFAULT NULL,
  `wed_end` time DEFAULT NULL,
  `thu_start` time DEFAULT NULL,
  `thu_end` time DEFAULT NULL,
  `fri_start` time DEFAULT NULL,
  `fri_end` time DEFAULT NULL,
  `sat_start` time DEFAULT NULL,
  `sat_end` time DEFAULT NULL,
  `sun_start` time DEFAULT NULL,
  `sun_end` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artist_profiles`
--

INSERT INTO `artist_profiles` (`profile_id`, `account_id`, `about`, `min_rate`, `hourly_rate`, `day_rate`, `created_at`, `updated_at`, `availability_status`, `mon_start`, `mon_end`, `tue_start`, `tue_end`, `wed_start`, `wed_end`, `thu_start`, `thu_end`, `fri_start`, `fri_end`, `sat_start`, `sat_end`, `sun_start`, `sun_end`) VALUES
(1, 1, 'Hi my name is Naomi! I specialize in realistic and bold illustrative tattoos. I love collaborating with clients to bring their ideas to life, creating pieces that feel personal and timeless. With a background in both digital and traditional art, I focus on Japanese-inspired designs and anime aesthetics, blending classic Irezumi elements with contemporary illustration techniques. Each piece I create honors the rich traditions of Japanese tattooing while incorporating modern storytelling.', 100.00, 100.00, 1000.00, '2026-03-05 05:41:23', '2026-03-05 05:41:23', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 7, 'Blackwork and geometric tattoo artist specializing in mandalas and bold symmetrical designs.', 150.00, 200.00, 900.00, '2026-03-11 20:15:47', '2026-03-11 20:15:47', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 8, 'Fine line artist creating minimalist floral and delicate line tattoos.', 120.00, 180.00, 750.00, '2026-03-11 20:55:58', '2026-03-11 20:55:58', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 9, 'Neo-traditional tattoo artist focused on bold color pieces and classic imagery.', 150.00, 210.00, 850.00, '2026-03-11 21:02:31', '2026-03-11 21:02:31', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 10, 'Black and grey realism artist specializing in portraits and wildlife tattoos.', 200.00, 250.00, 1000.00, '2026-03-11 21:03:26', '2026-03-11 21:03:26', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `artist_style_tag`
--

CREATE TABLE `artist_style_tag` (
  `artist_id` bigint(20) UNSIGNED NOT NULL,
  `style_tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artist_style_tag`
--

INSERT INTO `artist_style_tag` (`artist_id`, `style_tag_id`, `created_at`) VALUES
(1, 2, '2026-03-05 05:42:34'),
(1, 6, '2026-03-11 21:05:14'),
(1, 11, '2026-03-11 21:05:33'),
(7, 7, '2026-03-11 20:32:44'),
(7, 10, '2026-03-11 20:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `tattoo_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `map_data`
--

CREATE TABLE `map_data` (
  `map_id` bigint(20) UNSIGNED NOT NULL,
  `artist_profile_id` bigint(20) UNSIGNED NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL,
  `postal_code` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `map_data`
--

INSERT INTO `map_data` (`map_id`, `artist_profile_id`, `address`, `city`, `state`, `postal_code`, `created_at`, `updated_at`) VALUES
(1, 1, '3784 Howell Branch Rd', 'winter park', 'florida', '32792', '2026-03-05 05:41:23', '2026-03-05 05:41:23'),
(2, 2, '317 Harbor Lane', 'Miami', 'FL', '33101', '2026-03-11 20:15:47', '2026-03-11 20:15:47'),
(3, 3, '842 Maple Street', 'Orlando', 'FL', '32801', '2026-03-11 20:55:58', '2026-03-11 20:55:58'),
(4, 4, '1294 Cypress Drive', 'Tampa', 'FL', '33602', '2026-03-11 21:02:31', '2026-03-11 21:02:31'),
(5, 5, '56 Bayside Terrace', 'Miami', 'FL', '33131', '2026-03-11 21:03:26', '2026-03-11 21:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `artist_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `style_tags`
--

CREATE TABLE `style_tags` (
  `tag_id` bigint(20) UNSIGNED NOT NULL,
  `tag_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `style_tags`
--

INSERT INTO `style_tags` (`tag_id`, `tag_name`) VALUES
(4, '3D'),
(5, 'Abstract'),
(6, 'Anime'),
(7, 'Blackwork'),
(3, 'Cartoon'),
(8, 'Chicano'),
(9, 'Fine Line'),
(10, 'Geometric'),
(11, 'Illustrative'),
(2, 'Japanese'),
(12, 'Minimalist'),
(13, 'Neo-Traditional'),
(14, 'Pointalism'),
(15, 'Portraiture'),
(1, 'Realism'),
(17, 'Script'),
(18, 'Sketch'),
(19, 'Traditional'),
(20, 'Tribal'),
(21, 'Watercolor');

-- --------------------------------------------------------

--
-- Table structure for table `tattoos`
--

CREATE TABLE `tattoos` (
  `tattoo_id` bigint(20) UNSIGNED NOT NULL,
  `artist_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `aspect_ratio` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tattoos`
--

INSERT INTO `tattoos` (`tattoo_id`, `artist_id`, `image_path`, `title`, `description`, `aspect_ratio`, `created_at`, `updated_at`) VALUES
(1, 7, 'images/tattoos/styles/Geometric/geo_1.jpg', 'Hexagon Pattern', 'Full sleeve tat with hexagon pattern and other patterns down the forearm', '2:3', '2026-03-11 20:29:48', '2026-03-12 02:01:12'),
(2, 7, 'images/tattoos/styles/Geometric/geo_2.jpg', 'Floral Geometric Abstract', 'Torso-covering tattoo of an abstract floral pattern that also covers part of one arm', '2:3', '2026-03-11 20:45:26', '2026-03-12 02:01:12'),
(3, 7, 'images/tattoos/styles/Blackwork/Blackwork_2.jpg', 'Abstract Forearm Blackwork Pattern', 'Forearm tattoo of circular abstract pattern ', '2:3', '2026-03-11 20:51:39', '2026-03-12 02:01:12'),
(4, 8, 'images/tattoos/styles/3D/3d_1.jpg', 'Blue Monarch Butterfly', 'Realistic blue monarch butterfly tattoo with soft shadowing to create a subtle 3D effect on the skin.', '2:3', '2026-03-11 21:01:29', '2026-03-12 02:01:12'),
(5, 8, 'images/tattoos/styles/3D/3d_2.jpg', 'Stylized Cartoon Face', 'Playful cartoon-style face tattoo with layered red and blue outlines creating a bold 3D illusion.', '2:3', '2026-03-11 21:01:56', '2026-03-12 02:01:12'),
(6, 8, 'images/tattoos/styles/3D/3d_3.jpg', 'Biomechanical Sleeve', 'Detailed biomechanical arm sleeve blending organic forms with metallic textures and glowing blue accents.', '2:3', '2026-03-11 21:02:16', '2026-03-12 02:01:12'),
(7, 8, 'images/tattoos/styles/abstract/abstract_1.jpg', 'Abstract Color Orbit', 'Vibrant circular abstract tattoo with layered colors, ink splashes, and expressive line work.', '2:3', '2026-03-11 21:03:25', '2026-03-12 02:01:12'),
(8, 8, 'images/tattoos/styles/abstract/abstract_2.jpg', 'Floral Linework Sleeve', 'Flowing floral sleeve created with delicate abstract linework and organic shapes.', '2:3', '2026-03-11 21:03:39', '2026-03-12 02:01:12'),
(9, 8, 'images/tattoos/styles/abstract/abstract_3.jpg', 'Minimal Flow Lines', 'Minimal abstract tattoo of flowing black lines stretching across the upper back.', '2:3', '2026-03-11 21:03:57', '2026-03-12 02:01:12'),
(10, 8, 'images/tattoos/styles/abstract/abstract_4.jpg', 'Ink Brush Stroke', 'Expressive abstract tattoo using bold brush strokes and splattered ink textures.', '2:3', '2026-03-11 21:04:07', '2026-03-12 02:01:12'),
(11, 10, 'images/tattoos/styles/abstract/abstract_5.jpg', 'Contour Butterfly', 'Abstract butterfly design formed from layered contour-style line patterns.', '2:3', '2026-03-11 21:04:18', '2026-03-12 02:01:12'),
(12, 10, 'images/tattoos/styles/Anime/Anime_eyes.jpg', 'Anime Eyes', 'Two manga-style eye panels with sharp linework and contrasting red and blue highlights.', '2:3', '2026-03-11 21:04:41', '2026-03-12 02:01:12'),
(13, 10, 'images/tattoos/styles/Anime/Anime_fineline.jpg', 'Anime Fineline', 'Fine line anime portrait with a surreal double-face illusion and delicate shading.', '2:3', '2026-03-11 21:07:16', '2026-03-12 02:01:12'),
(14, 10, 'images/tattoos/styles/Anime/Anime_girl.jpg', 'Anime Girl', 'Bright anime character tattoo with vibrant colors and expressive manga-style features.', '2:3', '2026-03-11 21:07:26', '2026-03-12 02:01:12'),
(15, 10, 'images/tattoos/styles/Anime/Anime_sword.jpg', 'Anime Sword', 'Stylized anime design featuring a snake forming a heart around a magical, wand-like sword.', '2:3', '2026-03-11 21:07:35', '2026-03-12 02:01:12'),
(16, 10, 'images/tattoos/styles/Anime/anime_wolves.jpg', 'Anime Wolves', 'Dark anime composition of a cursed sorcerer accompanied by two snarling wolves in detailed blackwork.', '2:3', '2026-03-11 21:07:47', '2026-03-12 02:01:12'),
(17, 7, 'images/tattoos/styles/Blackwork/Blackwork_1.jpg', 'Blackwork Mandala Back', 'Large blackwork mandala composition covering the back with layered geometric patterns and dot shading.', '2:3', '2026-03-11 21:17:39', '2026-03-12 02:01:12'),
(18, 7, 'images/tattoos/styles/Blackwork/Blackwork_2.jpg', 'Blackwork Forearm Mandala', 'Dense geometric mandala pattern filling the forearm with sharp contrast and symmetry.', '2:3', '2026-03-11 21:17:56', '2026-03-12 02:01:12'),
(19, 7, 'images/tattoos/styles/Blackwork/blackwork_3.jpg', 'Blackwork Geometric Sleeve', 'Full forearm design combining mandalas, sacred geometry, and bold black bands.', '2:3', '2026-03-11 21:18:07', '2026-03-12 02:11:35'),
(20, 7, 'images/tattoos/styles/Blackwork/blackwork_4.jpg', 'Blackwork Roses', 'Bold blackwork rose design with heavy fill and clean traditional-style shapes.', '2:3', '2026-03-11 21:18:22', '2026-03-12 02:26:56'),
(21, 10, 'images/tattoos/styles/Blackwork/blackwork_5.jpg', 'Blackwork Abstract Sleeve', 'High contrast blackwork sleeve with flowing abstract shapes and negative space patterns.', '2:3', '2026-03-11 21:18:33', '2026-03-12 02:11:35'),
(22, 8, 'images/tattoos/styles/Cartoon/Cartoon_Bunny.jpg', 'Cartoon Bunny', 'Playful cartoon bunny tattoo with bold outlines, bright colors, and floating hearts.', '2:3', '2026-03-11 21:23:30', '2026-03-12 02:01:12'),
(23, 8, 'images/tattoos/styles/Cartoon/Cartoon_pineapple.jpg', 'Cartoon Pineapple', 'Colorful cartoon pineapple house tattoo inspired by animated underwater worlds.', '2:3', '2026-03-11 21:23:44', '2026-03-12 02:01:12'),
(24, 10, 'images/tattoos/styles/Cartoon/Cartoon_powerpuff.jpg', 'Cartoon Powerpuff', 'Bright cartoon tattoo of the Powerpuff Girls stacked together with stars, hearts, and vibrant colors.', '2:3', '2026-03-11 21:35:40', '2026-03-12 02:01:12'),
(25, 10, 'images/tattoos/styles/Cartoon/Cartoon_spiderman.jpg', 'Cartoon Spiderman', 'Stylized Spider-Man tattoo with exaggerated cartoon proportions and comic-style color.', '2:3', '2026-03-11 21:36:04', '2026-03-12 02:01:12'),
(26, 10, 'images/tattoos/styles/Cartoon/Cartoon_Stitch.jpg', 'Cartoon Stitch', 'Cartoon tattoo of Stitch wearing a Jack Skellington costume while holding Oogie Boogie.', '2:3', '2026-03-11 21:36:15', '2026-03-12 02:01:12'),
(27, 10, 'images/tattoos/styles/Chicano/Chicano_cursive.jpg', 'Chicano Cursive', 'Large Chicano-style cursive lettering tattoo with bold shading and ornamental flourishes.', '2:3', '2026-03-11 21:55:12', '2026-03-12 02:01:12'),
(28, 10, 'images/tattoos/styles/Chicano/Chicano_lettering.jpg', 'Chicano Lettering', 'Black and grey Chicano lettering tattoo wrapping across both knees in ornate script.', '2:3', '2026-03-11 21:55:25', '2026-03-12 02:01:12'),
(29, 10, 'images/tattoos/styles/Chicano/Chicano_Memorial.jpg', 'Chicano Memorial', 'Memorial tattoo featuring praying hands, script, and meaningful dates in black and grey.', '2:3', '2026-03-11 21:55:41', '2026-03-12 02:01:12'),
(30, 10, 'images/tattoos/styles/Chicano/Chicano_Roses.jpg', 'Chicano Roses', 'Black and grey rose tattoo with smooth shading and a classic Chicano look.', '2:3', '2026-03-11 21:55:55', '2026-03-12 02:01:12'),
(31, 10, 'images/tattoos/styles/Chicano/Chicano_Sleeve.jpg', 'Chicano Sleeve', 'Full Chicano-style sleeve with layered portraits, eyes, and dramatic black and grey shading.', '2:3', '2026-03-11 21:56:09', '2026-03-12 02:01:12'),
(32, 10, 'images/tattoos/styles/Fine Line/fineline_1.jpg', 'Fine Line Crescent', 'Minimal fine line crescent moon and arrow tattoo with delicate dotwork.', '2:3', '2026-03-11 21:56:19', '2026-03-12 02:01:12'),
(33, 10, 'images/tattoos/styles/Fine Line/fineline_2.jpg', 'Fine Line Face', 'Fine line portrait tattoo of a face framed with soft floral linework.', '2:3', '2026-03-11 21:56:30', '2026-03-12 02:01:12'),
(34, 10, 'images/tattoos/styles/Fine Line/fineline_3.jpg', 'Fine Line Botanical', 'Delicate fine line botanical tattoo with light leaves and radiating details.', '2:3', '2026-03-11 21:57:48', '2026-03-12 02:01:12'),
(35, 10, 'images/tattoos/styles/Fine Line/fineline_4.jpg', 'Fine Line Dandelion', 'Minimal dandelion tattoo with tiny birds and soft fine line detail.', '2:3', '2026-03-11 21:57:58', '2026-03-12 02:01:12'),
(36, 10, 'images/tattoos/styles/Fine Line/fineline_5.jpg', 'Fine Line Compass', 'Intricate fine line compass and ornamental design with geometric accents.', '2:3', '2026-03-11 21:58:10', '2026-03-12 02:01:12'),
(37, 10, 'images/tattoos/styles/Geometric/geo_1.jpg', 'Geometric Sleeve Pattern', 'Full geometric sleeve with dense repeating patterns and strong symmetry.', '2:3', '2026-03-11 21:58:53', '2026-03-12 02:01:12'),
(38, 10, 'images/tattoos/styles/Geometric/geo_2.jpg', 'Geometric Chest Mandala', 'Large geometric chest tattoo centered around a layered mandala design.', '2:3', '2026-03-11 21:59:06', '2026-03-12 02:01:12'),
(39, 10, 'images/tattoos/styles/Geometric/geo_3.jpg', 'Geometric Honeycomb Sleeve', 'Geometric forearm sleeve blending honeycomb shapes with bold black patternwork.', '2:3', '2026-03-11 21:59:16', '2026-03-12 02:01:12'),
(40, 10, 'images/tattoos/styles/Geometric/geo_4.jpg', 'Geometric Crystal Design', 'Abstract geometric tattoo with crystal forms and layered linework.', '2:3', '2026-03-11 21:59:36', '2026-03-12 02:01:12'),
(41, 10, 'images/tattoos/styles/Geometric/geo_5.jpg', 'Geometric Floral Sleeve', 'Ornamental geometric sleeve with floral mandalas and repeating sacred geometry.', '2:3', '2026-03-11 21:59:48', '2026-03-12 02:01:12'),
(42, 10, 'images/tattoos/styles/Illustrative/illustrative_1.jpg', 'Illustrative Landscape', 'Illustrative blackwork tattoo showing a floating landscape scene with detailed line shading.', '2:3', '2026-03-11 21:59:59', '2026-03-12 02:01:12'),
(43, 10, 'images/tattoos/styles/Illustrative/illustrative_2.jpg', 'Illustrative Figure', 'Illustrative tattoo of a cloaked figure with detailed textures and surreal styling.', '2:3', '2026-03-11 22:00:10', '2026-03-12 02:01:12'),
(44, 10, 'images/tattoos/styles/Illustrative/illustrative_3.jpg', 'Illustrative Bird and Fruit', 'High-contrast illustrative tattoo of a bird perched on fruit with sharp line detail.', '2:3', '2026-03-11 22:00:19', '2026-03-12 02:01:12'),
(45, 10, 'images/tattoos/styles/Japanese/japanese_1.jpg', 'Japanese Mask Sleeve', 'Large Japanese-style sleeve featuring a mask, koi, and bold traditional colorwork.', '2:3', '2026-03-11 22:00:31', '2026-03-12 02:01:12'),
(46, 10, 'images/tattoos/styles/Japanese/japanese_2.jpg', 'Japanese Floral Suit', 'Japanese body tattoo with peonies, scales, and flowing traditional background shading.', '2:3', '2026-03-11 22:00:41', '2026-03-12 02:01:12'),
(47, 10, 'images/tattoos/styles/Japanese/japanese_3.jpg', 'Japanese Oni Sleeve', 'Japanese sleeve featuring an oni mask, flowers, and dark wave-like background.', '2:3', '2026-03-11 22:00:50', '2026-03-12 02:01:12'),
(48, 10, 'images/tattoos/styles/Japanese/japanese_4.jpg', 'Japanese Floral Sleeve', 'Full Japanese sleeve with black background, bright flowers, and wave elements.', '2:3', '2026-03-11 22:00:59', '2026-03-12 02:01:12'),
(49, 10, 'images/tattoos/styles/Japanese/japanese_5.jpg', 'Japanese Chest Panels', 'Japanese chest tattoo with dragon and mask imagery in a lighter traditional style.', '2:3', '2026-03-11 22:01:08', '2026-03-12 02:01:12'),
(50, 10, 'images/tattoos/styles/minimalist/minimalism_2.jpg', 'Minimalism Red Sun', 'Minimalist branch tattoo accented with a small red sun.', '2:3', '2026-03-11 22:01:21', '2026-03-12 02:01:12'),
(51, 10, 'images/tattoos/styles/minimalist/minimalism_2.jpg', 'Minimalism Floral Script', 'Minimal floral linework tattoo with subtle script and decorative strokes.', '2:3', '2026-03-11 22:02:34', '2026-03-12 02:01:12'),
(52, 10, 'images/tattoos/styles/minimalist/minimalism_3.jpg', 'Minimalism Heart Hands', 'Tiny minimalist tattoo combining a heart with hand-like linework.', '2:3', '2026-03-11 22:02:47', '2026-03-12 02:01:12'),
(53, 10, 'images/tattoos/styles/Neo-traditional/neo-traditional_1.jpg', 'Neo-Traditional Queen', 'Neo-traditional portrait tattoo with rich color, bold outlines, and ornate framing.', '2:3', '2026-03-11 22:02:56', '2026-03-12 02:06:47'),
(54, 10, 'images/tattoos/styles/Neo-traditional/neo-traditional_2.jpg', 'Neo-Traditional Owl', 'Neo-traditional owl tattoo with warm tones and bold decorative leaves.', '2:3', '2026-03-11 22:03:05', '2026-03-12 02:06:47'),
(55, 10, 'images/tattoos/styles/Neo-traditional/neo-traditional_3.jpg', 'Neo-Traditional Floral Portrait', 'Neo-traditional female portrait with flowers and heavy stylized shading.', '2:3', '2026-03-11 22:03:19', '2026-03-12 02:06:47'),
(56, 10, 'images/tattoos/styles/Neo-traditional/neo-traditional_4.jpg', 'Neo-Traditional Medusa', 'Neo-traditional portrait with snakes, pearls, and dramatic bold colorwork.', '2:3', '2026-03-11 22:03:28', '2026-03-12 02:06:47'),
(57, 10, 'images/tattoos/styles/Neo-traditional/neo-traditional_5.jpg', 'Neo-Traditional Dog Portrait', 'Neo-traditional dog portrait tattoo with flowers and vibrant shading.', '2:3', '2026-03-11 22:03:37', '2026-03-12 02:06:47'),
(58, 10, 'images/tattoos/styles/Pointilism/pointillism_1.jpg', 'Pointillism Snowflake', 'Dotwork snowflake tattoo with layered geometric detail and soft stippled shading.', '2:3', '2026-03-11 22:03:47', '2026-03-12 02:01:12'),
(59, 10, 'images/tattoos/styles/Pointilism/pointillism_2.jpg', 'Pointillism Eye Skull', 'Dotwork tattoo combining a skull, ornamental mandala, and a realistic eye.', '2:3', '2026-03-11 22:03:58', '2026-03-12 02:01:12'),
(60, 10, 'images/tattoos/styles/Pointilism/point_3.jpg', 'Pointillism Lion', 'Dotwork lion portrait with geometric framing and soft stippled texture.', '2:3', '2026-03-11 22:04:11', '2026-03-12 02:01:12'),
(61, 10, 'images/tattoos/styles/Pointilism/point_4.jpg', 'Pointillism Diamonds', 'Minimal dotwork tattoo of overlapping diamonds with soft gradient shading.', '2:3', '2026-03-11 22:04:25', '2026-03-12 02:01:12'),
(62, 10, 'images/tattoos/styles/Portraiture/Portrait_amy.jpg', 'Portrait Amy', 'Framed black and grey portrait tattoo of Amy Winehouse with a memorial style presentation.', '2:3', '2026-03-11 22:04:33', '2026-03-12 02:01:12'),
(63, 10, 'images/tattoos/styles/Portraiture/Portrait_dark.jpg', 'Portrait Dark', 'Dark portrait tattoo of a gothic woman with dramatic shading and stitched crown details.', '2:3', '2026-03-11 22:04:42', '2026-03-12 02:01:12'),
(64, 10, 'images/tattoos/styles/Portraiture/portrait_edward.jpg', 'Portrait Edward', 'Small portrait tattoo of Edward Scissorhands in dark black and grey tones.', '2:3', '2026-03-11 22:04:54', '2026-03-12 02:01:12'),
(65, 10, 'images/tattoos/styles/Portraiture/portrait_hangover.jpg', 'Portrait Hangover', 'Realistic portrait tattoo of Alan from The Hangover with sunglasses and beard detail.', '2:3', '2026-03-11 22:05:04', '2026-03-12 02:01:12'),
(66, 10, 'images/tattoos/styles/Portraiture/Portrait_simplified.jpg', 'Portrait Simplified', 'Stylized black and grey portrait tattoo framed with elegant ornamental linework.', '2:3', '2026-03-11 22:05:14', '2026-03-12 02:01:12'),
(67, 10, 'images/tattoos/styles/realism/real_1.jpg', 'Realism Woman Portrait', 'Black and grey realism tattoo of a woman with layered textures and dramatic contrast.', '2:3', '2026-03-11 22:05:31', '2026-03-12 02:01:12'),
(68, 10, 'images/tattoos/styles/realism/real_2.jpg', 'Realism Astronaut Sleeve', 'Realism sleeve featuring an astronaut and space-themed imagery in black and grey.', '2:3', '2026-03-11 22:05:39', '2026-03-12 02:01:12'),
(69, 10, 'images/tattoos/styles/realism/real_3.jpg', 'Realism McGregor Portrait', 'Realistic color portrait tattoo of Conor McGregor with detailed facial shading.', '2:3', '2026-03-11 22:05:50', '2026-03-12 02:01:12'),
(70, 10, 'images/tattoos/styles/Script/Script_1.jpg', 'Script Nameplate', 'Large flowing script tattoo with bold ornamental lettering.', '2:3', '2026-03-11 22:06:15', '2026-03-12 02:01:12'),
(71, 10, 'images/tattoos/styles/Script/script_2.jpg', 'Script Gothic Quote', 'Large blackletter quote tattoo running vertically down the forearm.', '2:3', '2026-03-11 22:06:25', '2026-03-12 02:01:12'),
(72, 10, 'images/tattoos/styles/Script/Script_3.jpg', 'Script Flying', 'Small cursive quote tattoo reading “The little things.”', '2:3', '2026-03-11 22:30:52', '2026-03-12 02:01:12'),
(73, 10, 'images/tattoos/styles/Script/Script_4.jpg', 'Script Breathe Out', 'Script quote tattoo reading “Breathe out, so I can breathe you in.”', '2:3', '2026-03-11 22:31:06', '2026-03-12 02:01:12'),
(74, 10, 'images/tattoos/styles/Script/script_5.jpg', 'Script I Wanna Be Something', 'Fine cursive quote tattoo reading “I wanna be something.”', '2:3', '2026-03-11 22:31:18', '2026-03-12 02:01:12'),
(75, 10, 'images/tattoos/styles/Sketch/sketch_1.jpg', 'Sketch Mickey', 'Sketch-style Mickey Mouse tattoo with loose lines and playful hand-drawn detail.', '2:3', '2026-03-11 22:31:34', '2026-03-12 02:01:12'),
(76, 10, 'images/tattoos/styles/Sketch/sketch_2.jpg', 'Sketch Tiger', 'Sketch-style tiger head tattoo with angular strokes and abstract linework.', '2:3', '2026-03-11 22:31:43', '2026-03-12 02:01:12'),
(77, 10, 'images/tattoos/styles/Sketch/sketch_3.jpg', 'Sketch Sword Figure', 'Sketch-style tattoo of a robed figure framed with a sword and halo-like circle.', '2:3', '2026-03-11 22:33:19', '2026-03-12 02:01:12'),
(78, 10, 'images/tattoos/styles/Sketch/sketch_4.jpg', 'Sketch Crown Portrait', 'Expressive sketch portrait tattoo with a crown and elongated black and grey strokes.', '2:3', '2026-03-11 22:33:51', '2026-03-12 02:01:12'),
(79, 10, 'images/tattoos/styles/Sketch/sketch_5.jpg', 'Sketch Raven', 'Sketch-style raven tattoo built from sharp layered strokes across the upper arm.', '2:3', '2026-03-11 22:34:11', '2026-03-12 02:01:12'),
(80, 10, 'images/tattoos/styles/Traditional/trad_1.jpg', 'Traditional Dagger Heart', 'Traditional tattoo of a dagger through a heart with bold classic colorwork.', '2:3', '2026-03-11 22:34:21', '2026-03-12 02:01:12'),
(81, 10, 'images/tattoos/styles/Traditional/trad_2.jpg', 'Traditional Globe', 'Traditional tattoo featuring a globe, framed scene, and decorative nautical-style elements.', '2:3', '2026-03-11 22:34:30', '2026-03-12 02:01:12'),
(82, 10, 'images/tattoos/styles/Traditional/trad_3.jpg', 'Traditional Gypsy Rose', 'Traditional portrait tattoo of a woman with a rose and warm classic colors.', '2:3', '2026-03-11 22:34:40', '2026-03-12 02:01:12'),
(83, 10, 'images/tattoos/styles/Traditional/trad_4', 'Traditional Snake Dagger', 'Traditional tattoo of a snake wrapped around a dagger with bold vintage colorwork.', '2:3', '2026-03-11 22:34:50', '2026-03-12 02:11:35'),
(84, 10, 'images/tattoos/styles/Traditional/trad_5.jpg', 'Traditional Tiger Head', 'Traditional tiger head chest tattoo with strong color saturation and fierce expression.', '2:3', '2026-03-11 22:35:00', '2026-03-12 02:01:12'),
(85, 10, 'images/tattoos/styles/Tribal/Tribal_1.jpg', 'Tribal Shoulder Curve', 'Bold tribal shoulder tattoo with sweeping curved black shapes.', '2:3', '2026-03-11 22:35:12', '2026-03-12 02:01:12'),
(86, 10, 'images/tattoos/styles/Tribal/Tribal_2.jpg', 'Tribal Flame Shoulder', 'Tribal shoulder tattoo with flame-like lines and pointed blackwork forms.', '2:3', '2026-03-11 22:35:23', '2026-03-12 02:01:12'),
(87, 10, 'images/tattoos/styles/Tribal/Tribal_3.jpg', 'Tribal Back Symbol', 'Small tribal symbol tattoo centered on the upper back.', '2:3', '2026-03-11 22:35:40', '2026-03-12 02:01:12'),
(88, 10, 'images/tattoos/styles/Tribal/tribal_4.jpg', 'Tribal Circular Pattern', 'Circular tribal shoulder piece with intricate repeating blackwork patterns.', '2:3', '2026-03-11 22:35:56', '2026-03-12 02:11:35'),
(89, 10, 'images/tattoos/styles/Tribal/tribal_5.jpg', 'Tribal Neck Shoulder Piece', 'Flowing tribal tattoo extending from the neck across the shoulder.', '2:3', '2026-03-11 22:36:07', '2026-03-12 02:11:35'),
(90, 10, 'images/tattoos/styles/Watercolor/watercolor_1.jpg', 'Watercolor Planet', 'Watercolor tattoo of a ringed planet with flowing pink and purple paint effects.', '2:3', '2026-03-11 22:36:19', '2026-03-12 02:01:12'),
(91, 10, 'images/tattoos/styles/Watercolor/watercolor_2.jpg', 'Watercolor Skull', 'Watercolor skull tattoo with dripping multicolor paint and abstract splash effects.', '2:3', '2026-03-11 22:36:30', '2026-03-12 02:01:12'),
(92, 10, 'images/tattoos/styles/Watercolor/watercolor_3.jpg', 'Watercolor Phoenix Sleeve', 'Watercolor sleeve design featuring a fiery bird in bright blended tones.', '2:3', '2026-03-11 22:36:42', '2026-03-12 02:01:12'),
(93, 10, 'images/tattoos/styles/Watercolor/watercolor_4.jpg', 'Watercolor Jellyfish', 'Watercolor jellyfish tattoo with loose purple brushwork and flowing movement.', '2:3', '2026-03-11 22:36:51', '2026-03-12 02:01:12'),
(94, 10, 'images/tattoos/styles/Watercolor/watercolor_5.jpg', 'Watercolor Poppies', 'Fine watercolor back tattoo of red poppies with soft ornamental accents.', '2:3', '2026-03-11 22:36:59', '2026-03-12 02:01:12');

-- --------------------------------------------------------

--
-- Table structure for table `tattoo_style_tag`
--

CREATE TABLE `tattoo_style_tag` (
  `tattoo_id` bigint(20) UNSIGNED NOT NULL,
  `style_tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tattoo_style_tag`
--

INSERT INTO `tattoo_style_tag` (`tattoo_id`, `style_tag_id`, `created_at`) VALUES
(1, 10, '2026-03-11 20:32:15'),
(4, 4, '2026-03-11 22:40:05'),
(5, 4, '2026-03-11 22:41:30'),
(6, 4, '2026-03-11 22:41:40'),
(7, 5, '2026-03-12 01:13:10'),
(8, 5, '2026-03-12 01:13:01'),
(9, 5, '2026-03-12 01:12:53'),
(9, 12, '2026-03-12 01:17:42'),
(10, 5, '2026-03-12 01:12:46'),
(11, 5, '2026-03-12 01:12:38'),
(12, 6, '2026-03-12 01:12:19'),
(13, 6, '2026-03-12 01:12:09'),
(13, 9, '2026-03-12 01:17:50'),
(14, 6, '2026-03-12 01:12:03'),
(15, 6, '2026-03-12 01:11:57'),
(16, 6, '2026-03-12 01:11:51'),
(17, 7, '2026-03-12 01:11:32'),
(18, 7, '2026-03-12 01:11:20'),
(19, 7, '2026-03-12 01:11:12'),
(19, 10, '2026-03-12 01:18:07'),
(20, 7, '2026-03-12 01:11:06'),
(21, 5, '2026-03-12 01:18:17'),
(21, 7, '2026-03-12 01:10:56'),
(22, 3, '2026-03-12 01:10:44'),
(23, 3, '2026-03-12 01:10:38'),
(24, 3, '2026-03-12 01:10:30'),
(25, 3, '2026-03-12 01:10:21'),
(26, 3, '2026-03-12 01:10:11'),
(27, 8, '2026-03-12 01:09:55'),
(28, 8, '2026-03-12 01:09:49'),
(29, 8, '2026-03-12 01:09:41'),
(30, 8, '2026-03-12 01:09:33'),
(31, 8, '2026-03-12 01:09:22'),
(32, 9, '2026-03-12 01:09:15'),
(33, 9, '2026-03-12 01:09:08'),
(34, 9, '2026-03-12 01:09:00'),
(35, 9, '2026-03-12 01:08:54'),
(36, 9, '2026-03-12 01:08:47'),
(37, 10, '2026-03-12 01:08:37'),
(38, 10, '2026-03-12 01:08:28'),
(39, 10, '2026-03-12 01:07:53'),
(40, 10, '2026-03-12 01:07:40'),
(41, 10, '2026-03-12 01:07:28'),
(42, 11, '2026-03-12 01:07:19'),
(43, 11, '2026-03-12 01:07:13'),
(44, 11, '2026-03-12 01:07:05'),
(45, 2, '2026-03-12 01:06:53'),
(46, 2, '2026-03-12 01:06:42'),
(47, 2, '2026-03-12 01:06:33'),
(48, 2, '2026-03-12 01:06:24'),
(49, 2, '2026-03-12 01:06:16'),
(50, 12, '2026-03-12 01:06:06'),
(51, 12, '2026-03-12 01:05:58'),
(52, 12, '2026-03-12 01:05:50'),
(53, 13, '2026-03-12 01:05:33'),
(54, 13, '2026-03-12 01:05:26'),
(55, 13, '2026-03-12 01:05:19'),
(55, 15, '2026-03-12 01:19:16'),
(56, 13, '2026-03-12 01:05:08'),
(57, 13, '2026-03-12 01:04:59'),
(57, 15, '2026-03-12 01:19:03'),
(58, 14, '2026-03-12 01:04:47'),
(59, 14, '2026-03-12 01:04:39'),
(60, 14, '2026-03-12 01:04:30'),
(61, 14, '2026-03-12 01:04:22'),
(62, 15, '2026-03-12 01:04:03'),
(63, 15, '2026-03-12 01:03:52'),
(64, 15, '2026-03-12 01:03:44'),
(65, 15, '2026-03-12 01:03:38'),
(66, 15, '2026-03-12 01:03:29'),
(67, 1, '2026-03-12 01:02:58'),
(67, 15, '2026-03-12 01:31:02'),
(68, 1, '2026-03-12 01:02:52'),
(69, 1, '2026-03-12 01:02:46'),
(69, 4, '2026-03-12 01:02:21'),
(69, 15, '2026-03-12 01:30:51'),
(70, 17, '2026-03-12 01:00:24'),
(71, 17, '2026-03-12 01:01:50'),
(72, 17, '2026-03-12 01:00:46'),
(72, 18, '2026-03-12 01:00:14'),
(73, 17, '2026-03-12 01:00:06'),
(74, 17, '2026-03-12 00:59:58'),
(75, 18, '2026-03-12 00:59:50'),
(76, 18, '2026-03-12 00:59:14'),
(77, 18, '2026-03-12 00:59:04'),
(78, 18, '2026-03-12 00:58:57'),
(79, 18, '2026-03-12 00:58:52'),
(80, 19, '2026-03-12 00:58:37'),
(81, 19, '2026-03-12 00:57:54'),
(82, 19, '2026-03-12 00:57:45'),
(83, 19, '2026-03-12 00:57:40'),
(84, 19, '2026-03-12 00:57:34'),
(85, 20, '2026-03-11 22:43:53'),
(86, 20, '2026-03-11 22:43:44'),
(87, 20, '2026-03-11 22:43:32'),
(88, 20, '2026-03-11 22:43:23'),
(89, 20, '2026-03-11 22:43:17'),
(90, 21, '2026-03-11 22:43:02'),
(91, 21, '2026-03-11 22:42:56'),
(92, 21, '2026-03-11 22:42:07'),
(93, 21, '2026-03-11 22:41:59'),
(94, 21, '2026-03-11 22:41:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `uq_accounts_username` (`username`),
  ADD UNIQUE KEY `uq_accounts_email` (`email`);

--
-- Indexes for table `account_follows`
--
ALTER TABLE `account_follows`
  ADD PRIMARY KEY (`follower_account_id`,`following_account_id`),
  ADD KEY `idx_account_follows_following` (`following_account_id`);

--
-- Indexes for table `artist_profiles`
--
ALTER TABLE `artist_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `uq_artist_profiles_account_id` (`account_id`);

--
-- Indexes for table `artist_style_tag`
--
ALTER TABLE `artist_style_tag`
  ADD PRIMARY KEY (`artist_id`,`style_tag_id`),
  ADD KEY `idx_artist_style_tag_style_tag` (`style_tag_id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`account_id`,`tattoo_id`),
  ADD KEY `idx_bookmarks_tattoo_id` (`tattoo_id`);

--
-- Indexes for table `map_data`
--
ALTER TABLE `map_data`
  ADD PRIMARY KEY (`map_id`),
  ADD UNIQUE KEY `uq_map_data_artist_profile_id` (`artist_profile_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uq_reviews_reviewer_artist` (`reviewer_id`,`artist_id`),
  ADD KEY `idx_reviews_artist_id` (`artist_id`);

--
-- Indexes for table `style_tags`
--
ALTER TABLE `style_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `uq_style_tags_tag_name` (`tag_name`);

--
-- Indexes for table `tattoos`
--
ALTER TABLE `tattoos`
  ADD PRIMARY KEY (`tattoo_id`),
  ADD KEY `idx_tattoos_artist_id` (`artist_id`);

--
-- Indexes for table `tattoo_style_tag`
--
ALTER TABLE `tattoo_style_tag`
  ADD PRIMARY KEY (`tattoo_id`,`style_tag_id`),
  ADD KEY `idx_tattoo_style_tag_style_tag` (`style_tag_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `artist_profiles`
--
ALTER TABLE `artist_profiles`
  MODIFY `profile_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `map_data`
--
ALTER TABLE `map_data`
  MODIFY `map_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `style_tags`
--
ALTER TABLE `style_tags`
  MODIFY `tag_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tattoos`
--
ALTER TABLE `tattoos`
  MODIFY `tattoo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_follows`
--
ALTER TABLE `account_follows`
  ADD CONSTRAINT `fk_account_follows_follower` FOREIGN KEY (`follower_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_account_follows_following` FOREIGN KEY (`following_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `artist_profiles`
--
ALTER TABLE `artist_profiles`
  ADD CONSTRAINT `fk_artist_profiles_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `artist_style_tag`
--
ALTER TABLE `artist_style_tag`
  ADD CONSTRAINT `fk_artist_style_tag_artist` FOREIGN KEY (`artist_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_artist_style_tag_tag` FOREIGN KEY (`style_tag_id`) REFERENCES `style_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `fk_bookmarks_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmarks_tattoo` FOREIGN KEY (`tattoo_id`) REFERENCES `tattoos` (`tattoo_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `map_data`
--
ALTER TABLE `map_data`
  ADD CONSTRAINT `fk_map_data_artist_profile` FOREIGN KEY (`artist_profile_id`) REFERENCES `artist_profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_artist` FOREIGN KEY (`artist_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tattoos`
--
ALTER TABLE `tattoos`
  ADD CONSTRAINT `fk_tattoos_artist` FOREIGN KEY (`artist_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tattoo_style_tag`
--
ALTER TABLE `tattoo_style_tag`
  ADD CONSTRAINT `fk_tattoo_style_tag_tag` FOREIGN KEY (`style_tag_id`) REFERENCES `style_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tattoo_style_tag_tattoo` FOREIGN KEY (`tattoo_id`) REFERENCES `tattoos` (`tattoo_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
