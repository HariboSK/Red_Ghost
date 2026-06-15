-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hostiteľ: 127.0.0.1
-- Čas generovania: Po 15.Jún 2026, 19:10
-- Verzia serveru: 10.4.32-MariaDB
-- Verzia PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáza: `red_ghost`
--

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `address`
--

CREATE TABLE `address` (
  `id_address` int(11) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `zip` varchar(30) DEFAULT NULL,
  `country` varchar(60) DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT 0,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `address`
--

INSERT INTO `address` (`id_address`, `street`, `city`, `zip`, `country`, `is_default`, `id_user`) VALUES
(1, '', NULL, NULL, NULL, 0, 5);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `category`
--

CREATE TABLE `category` (
  `id_category` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `category`
--

INSERT INTO `category` (`id_category`, `name`) VALUES
(8, 'Ostrá'),
(9, 'Mierna'),
(10, 'Extrémna'),
(11, 'Ostrá'),
(12, 'Mierna'),
(13, 'Extrémna'),
(14, 'Susene Chilli'),
(15, 'Extrkat'),
(16, 'Omačky'),
(17, 'Sol'),
(18, 'Klučenka'),
(19, 'uncategorized');

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id_contact_msg` int(11) NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_email` varchar(80) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` enum('new','read','replied','closed') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `contact_messages`
--

INSERT INTO `contact_messages` (`id_contact_msg`, `sender_name`, `sender_email`, `subject`, `status`, `created_at`, `id_user`) VALUES
(1, 'admin', 'admin@admin.com', 'AHOOOOOJ', 'replied', '2026-05-28 18:08:38', NULL),
(2, 'Jacob', 'admin@admin.com', 'AHOOOOOJ', 'read', '2026-06-08 19:19:19', NULL),
(6, 'admin', 'admin@admin.com', 'RE: Odpoveď z profilu | Odpoveď: Ide tento messenger konečne ?', 'new', '2026-06-09 00:52:37', 5),
(7, 'admin', 'admin@admin.com', 'Teraz by malo prist čisto len toto', 'read', '2026-06-09 00:54:22', 5),
(8, 'admin', 'admin@admin.com', 'Nový produkt', 'replied', '2026-06-09 18:32:13', NULL),
(9, 'admin', 'admin@admin.com', 'sadsadada', 'new', '2026-06-11 11:44:28', 5),
(10, 'admin', 'admin@admin.com', 'TEST', 'new', '2026-06-11 11:45:18', NULL),
(11, 'admin', 'admin@admin.com', 'Ahooooj toto je skuska dal som preč require načitava sa z base stranky ako App:Init', 'replied', '2026-06-11 11:56:24', 5),
(12, 'DOminik', 'dominik@dominik.sk', 'AHOOOOOJ', 'replied', '2026-06-11 15:27:54', NULL),
(13, 'DOminik', 'dominik@dominik.sk', 'sews', 'new', '2026-06-11 15:30:18', 7);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `contact_replies`
--

CREATE TABLE `contact_replies` (
  `id_replies` int(11) NOT NULL,
  `sender_type` enum('user','admin') DEFAULT NULL,
  `message_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_message` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `contact_replies`
--

INSERT INTO `contact_replies` (`id_replies`, `sender_type`, `message_text`, `created_at`, `id_message`) VALUES
(1, 'user', 'TESTOVANY AHOOOOOOOJ', '2026-05-28 18:08:38', 1),
(2, 'admin', 'sers', '2026-05-28 18:14:19', 1),
(3, 'user', 'Toto je testovacia sprava som zvedavy ci pride', '2026-06-08 19:19:19', 2),
(7, 'user', 'Okej', '2026-06-09 00:25:07', 2),
(10, 'user', 'Zaujíma ma či bude nový produkt niejaky a či sa budu dorabať klučenky', '2026-06-09 18:32:13', 8),
(11, 'admin', 'Ano', '2026-06-09 18:33:54', 8),
(12, 'user', 'Testujem či fungje zjedodušenie', '2026-06-11 11:45:18', 10),
(13, 'admin', 'Super ide', '2026-06-11 11:56:52', 11),
(14, 'user', 'AHOOOOOJAHOOOOOJAHOOOOOJAHOOOOOJAHOOOOOJ', '2026-06-11 15:27:54', 12),
(15, 'admin', 'Serrs', '2026-06-11 15:28:57', 12);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `discount_code`
--

CREATE TABLE `discount_code` (
  `id_discount_code` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `discount_code`
--

INSERT INTO `discount_code` (`id_discount_code`, `code`, `description`, `discount_type`, `value`, `min_order_value`, `valid_from`, `valid_to`, `is_active`, `created_at`) VALUES
(2, 'PHPJEHALUZ', 'php projekt', 'percent', 50.00, 5.00, '2026-06-15 17:27:00', '2026-06-21 17:27:00', 1, '2026-06-15 15:28:02');

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `discount_code_redemption`
--

CREATE TABLE `discount_code_redemption` (
  `id_redemption` int(11) NOT NULL,
  `id_discount_code` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_order` int(11) DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `order`
--

CREATE TABLE `order` (
  `id_order` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `delivery_method` enum('courier','post','alzabox') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `order_address`
--

CREATE TABLE `order_address` (
  `id_order_address` int(11) NOT NULL,
  `type` enum('billing','shipping') DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip` varchar(30) DEFAULT NULL,
  `country` varchar(60) DEFAULT NULL,
  `id_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `order_item`
--

CREATE TABLE `order_item` (
  `id_order_item` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `id_order` int(11) DEFAULT NULL,
  `id_product` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id_status` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `payment`
--

CREATE TABLE `payment` (
  `id_payment` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','paid','failed','cancelled','refunded') DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `id_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `product`
--

CREATE TABLE `product` (
  `id_product` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `featured` tinyint(4) DEFAULT 0,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `discount` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `product`
--

INSERT INTO `product` (`id_product`, `name`, `description`, `price`, `image`, `rating`, `featured`, `stock`, `created_at`, `updated_at`, `discount`) VALUES
(20, 'Red Ghost Chilli Omacka', 'Vynikajúca chilli omacka s bohatou chutou a paprikovými nádychmi.', 12.99, '/assets/images/omacka3.webp', 5, 1, 15, '2026-06-15 16:53:08', NULL, 0),
(21, 'Domaca Chilli Pasta', 'Tradičná slovenská recepta s domácimi paprikami.', 8.99, '/assets/images/omacky2.webp', 4, 1, 20, '2026-06-15 16:53:08', NULL, 0),
(22, 'Susene Chilli Papriky', 'Prírodne sušené chilli papriky bez chemických prídavkov.', 14.99, '/assets/images/susene-chilli-Picsart-AiImageEnhancer.webp', 4, 0, 8, '2026-06-15 16:53:08', NULL, 0),
(23, 'Hot Sauce XXL', 'Extrémna chilli omacka pre odvážnych. Intenzívna palivosť.', 16.99, '/assets/images/omacka3.webp', 5, 0, 5, '2026-06-15 16:53:08', NULL, 0),
(24, 'Jemná Papriková Omacka', 'Mierna omacka vhodná pre jemnejšie chute.', 9.99, '/assets/images/omacky2.webp', 3, 0, 25, '2026-06-15 16:53:08', NULL, 0),
(25, 'Red Ghost Chilli Omacka', 'Vynikajúca chilli omacka s bohatou chutou a paprikovými nádychmi.', 12.99, '/assets/images/omacka3.webp', 5, 1, 15, '2026-06-15 16:53:55', NULL, 0),
(26, 'Domaca Chilli Pasta', 'Tradičná slovenská recepta s domácimi paprikami.', 8.99, '/assets/images/omacky2.webp', 4, 1, 20, '2026-06-15 16:53:55', NULL, 0),
(27, 'Susene Chilli Papriky', 'Prírodne sušené chilli papriky bez chemických prídavkov.', 14.99, '/assets/images/susene-chilli-Picsart-AiImageEnhancer.webp', 4, 0, 8, '2026-06-15 16:53:55', NULL, 0),
(28, 'Hot Sauce XXL', 'Extrémna chilli omacka pre odvážnych. Intenzívna palivosť.', 16.99, '/assets/images/omacka3.webp', 5, 0, 5, '2026-06-15 16:53:55', NULL, 0),
(29, 'Jemná Papriková Omacka', 'Mierna omacka vhodná pre jemnejšie chute.', 9.99, '/assets/images/omacky2.webp', 3, 0, 25, '2026-06-15 16:53:55', NULL, 0),
(30, 'Red Ghost Chilli Omacka', 'Vynikajúca chilli omacka s bohatou chutou a paprikovými nádychmi.', 12.99, '/assets/images/omacka3.webp', 5, 1, 15, '2026-06-15 16:54:07', NULL, 0),
(31, 'Domaca Chilli Pasta', 'Tradičná slovenská recepta s domácimi paprikami.', 8.99, '/assets/images/omacky2.webp', 4, 1, 20, '2026-06-15 16:54:07', NULL, 0),
(32, 'Susene Chilli Papriky', 'Prírodne sušené chilli papriky bez chemických prídavkov.', 14.99, '/assets/images/susene-chilli-Picsart-AiImageEnhancer.webp', 4, 0, 8, '2026-06-15 16:54:07', NULL, 0),
(33, 'Hot Sauce XXL', 'Extrémna chilli omacka pre odvážnych. Intenzívna palivosť.', 16.99, '/assets/images/omacka3.webp', 5, 0, 5, '2026-06-15 16:54:07', NULL, 0),
(34, 'Jemná Papriková Omacka', 'Mierna omacka vhodná pre jemnejšie chute.', 9.99, '/assets/images/omacky2.webp', 3, 0, 25, '2026-06-15 16:54:07', NULL, 0),
(35, 'Red Ghost Chilli Omacka', 'Vynikajúca chilli omacka s bohatou chutou a paprikovými nádychmi.', 12.99, '/assets/images/omacka3.webp', 5, 1, 15, '2026-06-15 16:54:17', NULL, 10),
(36, 'Domaca Chilli Pasta', 'Tradičná slovenská recepta s domácimi paprikami.', 8.99, '/assets/images/omacky2.webp', 4, 1, 20, '2026-06-15 16:54:17', NULL, 0),
(37, 'Susene Chilli Papriky', 'Prírodne sušené chilli papriky bez chemických prídavkov.', 14.99, '/assets/images/susene-chilli-Picsart-AiImageEnhancer.webp', 4, 0, 8, '2026-06-15 16:54:17', NULL, 0),
(38, 'Hot Sauce XXL', 'Extrémna chilli omacka pre odvážnych. Intenzívna palivosť.', 16.99, '/assets/images/omacka3.webp', 5, 0, 5, '2026-06-15 16:54:17', NULL, 0),
(39, 'Jemná Papriková Omacka', 'Mierna omacka vhodná pre jemnejšie chute.', 9.99, '/assets/images/omacky2.webp', 3, 0, 25, '2026-06-15 16:54:17', NULL, 0),
(40, 'Carolina', 'Extra Spicy', 15.00, '/assets/images/CarolinaReaper.webp', 4, 0, 10, '2026-06-15 16:57:10', NULL, 0),
(41, 'Pablano', 'Pablano je extra špecialita na varenie', 5.00, '/assets/images/Pablano.webp', 5, 1, 15, '2026-06-15 16:57:57', NULL, 0),
(42, 'Habareno', 'Habareno Orange', 10.00, '/assets/images/habanero-orange-67-0.webp', 3, 0, 15, '2026-06-15 16:58:34', NULL, 0),
(43, 'Jalapeno', 'Jalapeno Red', 20.00, '/assets/images/Jalapeno.webp', 3, 0, 5, '2026-06-15 16:59:08', NULL, 0),
(44, 'Extrakt', 'Extrakt je do polievok a na masko', 20.00, '/assets/images/exktrakt.webp', 4, 1, 5, '2026-06-15 16:59:54', NULL, 10),
(45, 'Chilli omačky', 'Omačky na chuť', 10.00, '/assets/images/345.webp', 4, 1, 10, '2026-06-15 17:00:33', NULL, 0),
(46, 'Chilli Sol', 'Soľ vyborna z chilli', 50.00, '/assets/images/chilli-sol.webp', 5, 1, 10, '2026-06-15 17:01:33', NULL, 20),
(47, 'Klučenka', 'Klučenka chilli na cestu !', 10.00, '/assets/images/klucenka.webp', 4, 1, 20, '2026-06-15 17:02:11', NULL, 20),
(48, 'Test', 'Testujem zlavu', 100.00, '/assets/images/sadenice-Picsart-AiImageEnhancer.webp', 4, 0, 10, '2026-06-15 17:07:56', NULL, 0),
(49, 'test', 'test', 150.00, '/assets/images/sadenice-Picsart-AiImageEnhancer.webp', 4, 0, 45, '2026-06-15 17:09:23', NULL, 50);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `product_category`
--

CREATE TABLE `product_category` (
  `id_product` int(11) NOT NULL,
  `id_category` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `product_category`
--

INSERT INTO `product_category` (`id_product`, `id_category`) VALUES
(35, 14),
(40, 14),
(41, 14),
(42, 14),
(43, 14),
(44, 15),
(45, 16),
(46, 17),
(47, 18),
(48, 19),
(49, 19);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `product_review`
--

CREATE TABLE `product_review` (
  `id_review` int(11) NOT NULL,
  `rating` tinyint(1) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_product` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_order_item` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `shop_review`
--

CREATE TABLE `shop_review` (
  `id_shop_review` int(11) NOT NULL,
  `reviewer_name` varchar(100) NOT NULL,
  `rating` tinyint(1) UNSIGNED NOT NULL,
  `review_text` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `shop_review`
--

INSERT INTO `shop_review` (`id_shop_review`, `reviewer_name`, `rating`, `review_text`, `status`, `created_at`, `updated_at`, `id_user`) VALUES
(1, 'Marko', 5, 'Vynikajúci obchod! Rýchle doručenie a kvalitné produkty. Veľmi spokojný!', 'approved', '2026-05-27 11:25:03', NULL, NULL),
(2, 'Petra', 5, 'Skvelé chilli produkty. Ceny sú fair a kvalita je top. Odporúčam!', 'approved', '2026-05-27 11:25:03', NULL, NULL),
(3, 'Roman', 4, 'Dobré produkty, ale doručenie trvalo dlhšie. Inak všetko ok.', 'approved', '2026-05-27 11:25:03', NULL, NULL),
(4, 'Zuzana', 5, 'Láska na prvý pohľad! Taký chutný obsah, a ešte sa mi páčia sociálne siete.', 'approved', '2026-05-27 11:25:03', NULL, NULL),
(5, 'Miroslav', 4, 'Dobrý výber. Bol som milo prekvapený kvalitou balenia.', 'approved', '2026-05-27 11:25:03', NULL, NULL),
(6, 'Katarina', 5, 'Najlepší chilli obchod ktorý som kedy navštívil! Všetko 5 hviezd!', 'approved', '2026-05-27 11:25:03', NULL, NULL),
(8, 'admin', 4, 'Obchod je super všetko funguje ako ma', 'approved', '2026-06-09 18:32:44', '2026-06-09 18:33:36', NULL),
(9, 'admin', 5, 'SKušam to bez requirements', 'approved', '2026-06-11 11:58:01', '2026-06-11 11:58:11', 5),
(10, 'DOminik', 4, 'Megaaaaaaaaaaaaaa', 'approved', '2026-06-11 15:27:21', '2026-06-11 15:28:46', NULL),
(11, 'Jakub Admin', 5, 'Skuska či ide nova verzia cez Model', 'approved', '2026-06-14 12:30:57', '2026-06-14 12:31:04', 5);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `telephone` varchar(20) DEFAULT NULL,
  `unique_reset_passwd` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sťahujem dáta pre tabuľku `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `image`, `loyalty_points`, `role`, `created_at`, `telephone`, `unique_reset_passwd`) VALUES
(5, 'admin', 'admin@admin.com', '$2y$10$ZgOa1fnJQRxQBgt/iZdfM.vk4HGkf67W0rHRZDDoL1tgfh2AauZr6', 'avatar_5_1781541914.png', 650, 'admin', '2026-05-27 11:07:26', '421 94934353', '8a95066af2eb'),
(7, 'DOminik', 'dominik@dominik.sk', '$2y$10$9A08np6i9tgNVdQgb6ot2O.Xc2cStKrobT6TN.P2rQ.DFRAu6Bv0O', NULL, 50, 'customer', '2026-06-11 15:29:45', NULL, NULL),
(10, 'Marko', 'marko@test.com', '$2y$10$hash1', NULL, 0, 'customer', '2026-06-15 16:53:07', NULL, NULL),
(11, 'Petra', 'petra@test.com', '$2y$10$hash2', NULL, 0, 'customer', '2026-06-15 16:53:07', NULL, NULL),
(12, 'Roman', 'roman@test.com', '$2y$10$hash3', NULL, 0, 'customer', '2026-06-15 16:53:07', NULL, NULL),
(13, 'Admin', 'admin@test.com', '$2y$10$VygWHaBLmYW2w1dxDqVkJ.L0j6xDEyGLY5q5xyUEjpZKf6Vy9VC1i', NULL, 0, 'admin', '2026-06-15 16:53:07', NULL, NULL);

--
-- Kľúče pre exportované tabuľky
--

--
-- Indexy pre tabuľku `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`id_address`),
  ADD KEY `idx_address_user` (`id_user`);

--
-- Indexy pre tabuľku `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`);

--
-- Indexy pre tabuľku `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id_contact_msg`),
  ADD KEY `idx_contact_user` (`id_user`),
  ADD KEY `idx_contact_status` (`status`);

--
-- Indexy pre tabuľku `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD PRIMARY KEY (`id_replies`),
  ADD KEY `idx_reply_message` (`id_message`);

--
-- Indexy pre tabuľku `discount_code`
--
ALTER TABLE `discount_code`
  ADD PRIMARY KEY (`id_discount_code`),
  ADD UNIQUE KEY `uq_discount_code` (`code`);

--
-- Indexy pre tabuľku `discount_code_redemption`
--
ALTER TABLE `discount_code_redemption`
  ADD PRIMARY KEY (`id_redemption`),
  ADD UNIQUE KEY `id_user` (`id_user`,`id_discount_code`),
  ADD KEY `id_user_2` (`id_user`),
  ADD KEY `id_discount_code` (`id_discount_code`),
  ADD KEY `fk_redemption_order` (`id_order`);

--
-- Indexy pre tabuľku `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `idx_order_user` (`user_id`),
  ADD KEY `idx_order_status` (`status`);

--
-- Indexy pre tabuľku `order_address`
--
ALTER TABLE `order_address`
  ADD PRIMARY KEY (`id_order_address`),
  ADD KEY `id_order` (`id_order`);

--
-- Indexy pre tabuľku `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id_order_item`),
  ADD KEY `idx_order_item_order` (`id_order`),
  ADD KEY `idx_order_item_product` (`id_product`);

--
-- Indexy pre tabuľku `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id_status`),
  ADD KEY `id_order` (`id_order`);

--
-- Indexy pre tabuľku `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id_payment`),
  ADD KEY `idx_payment_order` (`id_order`),
  ADD KEY `idx_payment_status` (`status`);

--
-- Indexy pre tabuľku `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id_product`);

--
-- Indexy pre tabuľku `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`id_product`,`id_category`),
  ADD KEY `id_category` (`id_category`);

--
-- Indexy pre tabuľku `product_review`
--
ALTER TABLE `product_review`
  ADD PRIMARY KEY (`id_review`),
  ADD UNIQUE KEY `uq_review_user_product` (`id_user`,`id_product`),
  ADD KEY `idx_review_product_status` (`id_product`,`status`,`created_at`),
  ADD KEY `idx_review_user` (`id_user`),
  ADD KEY `idx_review_order_item` (`id_order_item`);

--
-- Indexy pre tabuľku `shop_review`
--
ALTER TABLE `shop_review`
  ADD PRIMARY KEY (`id_shop_review`),
  ADD KEY `idx_shop_review_status_created` (`status`,`created_at`),
  ADD KEY `idx_shop_review_user` (`id_user`);

--
-- Indexy pre tabuľku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pre exportované tabuľky
--

--
-- AUTO_INCREMENT pre tabuľku `address`
--
ALTER TABLE `address`
  MODIFY `id_address` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pre tabuľku `category`
--
ALTER TABLE `category`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pre tabuľku `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id_contact_msg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pre tabuľku `contact_replies`
--
ALTER TABLE `contact_replies`
  MODIFY `id_replies` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pre tabuľku `discount_code`
--
ALTER TABLE `discount_code`
  MODIFY `id_discount_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pre tabuľku `discount_code_redemption`
--
ALTER TABLE `discount_code_redemption`
  MODIFY `id_redemption` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pre tabuľku `order`
--
ALTER TABLE `order`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pre tabuľku `order_address`
--
ALTER TABLE `order_address`
  MODIFY `id_order_address` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pre tabuľku `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id_order_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pre tabuľku `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pre tabuľku `payment`
--
ALTER TABLE `payment`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pre tabuľku `product`
--
ALTER TABLE `product`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pre tabuľku `product_review`
--
ALTER TABLE `product_review`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pre tabuľku `shop_review`
--
ALTER TABLE `shop_review`
  MODIFY `id_shop_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pre tabuľku `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Obmedzenie pre exportované tabuľky
--

--
-- Obmedzenie pre tabuľku `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

--
-- Obmedzenie pre tabuľku `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

--
-- Obmedzenie pre tabuľku `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD CONSTRAINT `contact_replies_ibfk_1` FOREIGN KEY (`id_message`) REFERENCES `contact_messages` (`id_contact_msg`);

--
-- Obmedzenie pre tabuľku `discount_code_redemption`
--
ALTER TABLE `discount_code_redemption`
  ADD CONSTRAINT `fk_redemption_code` FOREIGN KEY (`id_discount_code`) REFERENCES `discount_code` (`id_discount_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_redemption_order` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`),
  ADD CONSTRAINT `fk_redemption_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Obmedzenie pre tabuľku `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Obmedzenie pre tabuľku `order_address`
--
ALTER TABLE `order_address`
  ADD CONSTRAINT `order_address_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`);

--
-- Obmedzenie pre tabuľku `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`id_product`) REFERENCES `product` (`id_product`);

--
-- Obmedzenie pre tabuľku `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`);

--
-- Obmedzenie pre tabuľku `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`);

--
-- Obmedzenie pre tabuľku `product_category`
--
ALTER TABLE `product_category`
  ADD CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `product` (`id_product`),
  ADD CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`);

--
-- Obmedzenie pre tabuľku `product_review`
--
ALTER TABLE `product_review`
  ADD CONSTRAINT `product_review_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_review_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_review_ibfk_3` FOREIGN KEY (`id_order_item`) REFERENCES `order_item` (`id_order_item`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Obmedzenie pre tabuľku `shop_review`
--
ALTER TABLE `shop_review`
  ADD CONSTRAINT `shop_review_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
