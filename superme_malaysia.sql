-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2025 at 10:31 AM
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
-- Database: `superme_malaysia`
--
CREATE DATABASE IF NOT EXISTS `superme_malaysia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `superme_malaysia`;

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password_hash` varchar(200) NOT NULL,
  `email` varchar(80) NOT NULL,
  `gender` char(1) DEFAULT NULL,
  `photo` varchar(50) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `is_banned` tinyint(1) NOT NULL,
  `wallet_balance` double(15,2) NOT NULL,
  `last_notice_visit` datetime DEFAULT NULL,
  `pending_delete_expire` datetime DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  `account_type_id` char(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `name`, `password_hash`, `email`, `gender`, `photo`, `is_verified`, `is_banned`, `wallet_balance`, `last_notice_visit`, `pending_delete_expire`, `creation_time`, `account_type_id`) VALUES
(1, 'Malaysia Superme', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'malaysiasuperme@gmail.com', '-', NULL, 1, 0, 0.00, NULL, NULL, '2025-04-20 21:48:13', 'sup'),
(19, 'wen jing', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'liewwj-wm24@student.tarc.edu.my', 'm', NULL, 1, 0, 7071.65, NULL, NULL, '2020-07-16 11:51:15', 'cus'),
(32, 'ivan', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'chailf-wm24@student.tarc.edu.my', 'm', NULL, 1, 0, 0.00, NULL, NULL, '2025-04-27 14:34:12', 'cus'),
(34, 'cheng yu', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'hoicy-wm24@student.tarc.edu.my', 'm', NULL, 1, 0, 0.00, NULL, NULL, '2025-04-27 14:40:42', 'mkt'),
(35, 'zack', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'pangzk-wm24@student.tarc.edu.my', 'm', NULL, 1, 0, 277.77, '2025-04-27 20:40:15', NULL, '2022-04-01 14:42:52', 'cus'),
(36, 'ken', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'liewwenjing34882@gmail.com', 'm', NULL, 1, 0, 58.00, '2025-05-02 08:18:14', NULL, '2025-05-02 08:02:08', 'cus');

-- --------------------------------------------------------

--
-- Table structure for table `account_type`
--

DROP TABLE IF EXISTS `account_type`;
CREATE TABLE `account_type` (
  `id` char(3) NOT NULL,
  `name` varchar(30) NOT NULL,
  `sales_report` tinyint(1) NOT NULL,
  `manage_customer` tinyint(1) NOT NULL,
  `manage_admin` tinyint(1) NOT NULL,
  `manage_category` tinyint(1) NOT NULL,
  `manage_product` tinyint(1) NOT NULL,
  `manage_order` tinyint(1) NOT NULL,
  `manage_notice` tinyint(1) NOT NULL,
  `manage_voucher` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_type`
--

INSERT INTO `account_type` (`id`, `name`, `sales_report`, `manage_customer`, `manage_admin`, `manage_category`, `manage_product`, `manage_order`, `manage_notice`, `manage_voucher`) VALUES
('csp', 'Customer Support', 0, 1, 0, 0, 0, 1, 0, 0),
('cus', 'Customer', 0, 0, 0, 0, 0, 0, 0, 0),
('hrm', 'HR Manager', 0, 1, 1, 0, 0, 0, 0, 0),
('mkt', 'Marketing Admin', 1, 0, 0, 0, 0, 0, 1, 1),
('sup', 'Super Admin', 1, 1, 1, 1, 1, 1, 1, 1),
('whm', 'Warehouse Manager', 0, 0, 0, 1, 1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `product_variant_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`product_variant_id`, `account_id`, `quantity`) VALUES
(29, 36, 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `photo`, `creation_time`) VALUES
(4, 'Hoodie', '8fe1e922b8f96ed23902576716a944.jpg', '2025-04-25 08:34:03'),
(5, 'T-Shirt', '22a624f0bd6d2827bc0b7fd770d581.jpg', '2025-04-25 08:34:16'),
(8, 'Polo', '75825b2717a37e8bdbf13b2a1b5873.jpg', '2025-04-27 01:01:48'),
(9, 'Tank Top', '463926329cb491a5f58404d6b1ae27.jpg', '2025-04-27 01:03:16'),
(10, 'Sweatshirt', 'db06349d609f6182427f04792fd673.jpg', '2025-04-27 01:07:08'),
(11, 'Jacket', '4315cdcc578611f0a318a3e9a70ff1.jpg', '2025-04-27 01:08:29'),
(12, 'Coat', '7fe5560d1db2867b2e4fa237b09d47.jpg', '2025-04-27 01:09:27'),
(13, 'Cardigan', '379612f1a94862ba60eea499e4ebeb.jpg', '2025-04-27 01:11:18'),
(14, 'Shorts', '70b7c01b824545a58b9ac1db645b45.jpg', '2025-04-27 01:12:19'),
(15, 'Jeans', 'e5722741948a36a39fdf961e84e688.jpg', '2025-04-27 01:12:57'),
(16, 'Pants', 'e4f08f09d1c8ed19e6c83c71079e12.jpg', '2025-04-27 01:13:27'),
(17, 'Joggers', '334330c2a31d079c7af8028ea4f682.jpg', '2025-04-27 01:18:16'),
(18, 'Leggings', '60df4466ce6a3560f9f7a226ddef02.jpg', '2025-04-27 01:19:11'),
(19, 'Pajamas', '4f44d70de9a2d4a49826f584ccb078.jpg', '2025-04-27 01:20:09'),
(20, 'Gym Shorts', '6969d2a30e84be29c6037ded48979b.jpg', '2025-04-27 01:22:10'),
(21, 'Track Pants', 'd606b0940569fc8ea41b804e6b6d8e.jpg', '2025-04-27 01:25:48'),
(22, 'Hats', '324c593d33acf25708c6962a5e3228.jpg', '2025-04-27 01:33:21'),
(23, 'Socks', '7c355f6d105d902f445acf949c2e26.jpg', '2025-04-27 01:33:48'),
(24, 'Scarves', 'c3b5ddb46ed2e44d6bb2b0af050679.jpg', '2025-04-27 01:34:29'),
(25, 'Gloves', '3874e6a78159360d48e2c9192b00c3.jpg', '2025-04-27 01:35:44'),
(26, 'ken cat', '669891f471ab513606469100dd7f98.jpg', '2025-05-02 08:03:46');

-- --------------------------------------------------------

--
-- Table structure for table `change_email`
--

DROP TABLE IF EXISTS `change_email`;
CREATE TABLE `change_email` (
  `token` char(150) NOT NULL,
  `expire` datetime NOT NULL,
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delete_account`
--

DROP TABLE IF EXISTS `delete_account`;
CREATE TABLE `delete_account` (
  `token` char(150) NOT NULL,
  `expire` datetime NOT NULL,
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favourite`
--

DROP TABLE IF EXISTS `favourite`;
CREATE TABLE `favourite` (
  `account_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourite`
--

INSERT INTO `favourite` (`account_id`, `product_id`, `creation_time`) VALUES
(19, 53, '2025-04-27 21:24:17'),
(19, 54, '2025-04-27 21:20:04'),
(19, 71, '2025-04-27 20:25:17'),
(32, 23, '2025-04-27 15:17:14');

-- --------------------------------------------------------

--
-- Table structure for table `like_review`
--

DROP TABLE IF EXISTS `like_review`;
CREATE TABLE `like_review` (
  `account_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `like_review`
--

INSERT INTO `like_review` (`account_id`, `product_id`, `reviewer_id`) VALUES
(19, 13, 19),
(19, 71, 19),
(32, 13, 19),
(35, 23, 35),
(35, 25, 35);

-- --------------------------------------------------------

--
-- Table structure for table `notice`
--

DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
  `id` int(11) NOT NULL,
  `title` varchar(70) NOT NULL,
  `content` longtext DEFAULT NULL,
  `photo` varchar(100) DEFAULT NULL,
  `link` varchar(300) DEFAULT NULL,
  `link_text` varchar(50) DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice`
--

INSERT INTO `notice` (`id`, `title`, `content`, `photo`, `link`, `link_text`, `creation_time`) VALUES
(17, 'Happy Lunar New Year!!!', 'Wishing you a year full of good luck, laughter, and adventures.', 'b0e61cfc601d8abba4b8594a3bed87.jpg', '/get-voucher?token=6da9aedfba0fce93102ca0caf5615ba1f9c601c59ed231b716f2f09dcce27e6362425d79c7a1fc6384c061e11c804eafadcdbdd50e890ee7c9412e567e05e169cdee1e5e3f069be21b125b', 'Get Free Voucher', '2025-04-27 15:00:09'),
(18, 'Selamat Hari Raya 2025', 'It\'s time to celebrate Hari Raya Aidilfitri! This festival is super important for Muslims everywhere, marking the end of Ramadan fasting with lots of joy. Families and friends gather to share love and happiness, celebrating their spiritual journey together. So get ready to embrace the festivities with open arms and cherish this special time with your loved ones!\r\n\r\nGather with family and friends, savor the scrumptious delicacies, share stories of gratitude and love, and bask in the merriment of this new month in the Islamic calendar.', '0e333c48a0ab4ca48ded0efe1257aa.jpg', '/get-voucher?token=4802842189fae93bc4f9fbdb1819a37fa6e32d8553298f881f3b822b40eef074a18eb15a4b9cd53227fd442faa0e65ab9e6fb24931c2186e3d803230c334145e43e2094d8512e218713124', 'Get Free Voucher', '2025-04-27 15:01:48'),
(19, 'Which product should we launch next?', 'We’re working on exciting new products and we want your opinion.\r\nPick your favorite below and stay tuned — your choice could be our next big release!', NULL, NULL, NULL, '2025-04-27 15:05:29'),
(20, '🎉 Superme\'s Anniversary Flash Sale! 🎉', 'It\'s our anniversary, and we\'re showering YOU with gifts! For a limited time, enjoy a fantastic discount on all our trendy apparel. Use the voucher below to snag your favorite pieces at an unbeatable price. Happy shopping, Superme Fam!\r\nClick the button below to get RM 100 off your entire order, limited to the 1000 people only.', NULL, '/get-voucher?token=4beaba9f94fb62562c3533feaca9382cfd1f78b8511b4e42603e543ded24b7090f8ef02d556b0519b74362edba20ee0de340be4069a16d9140bd770c8766c28f7ab1197cfd548f3b69019a', 'Get RM 100 off your entire order.', '2025-02-01 22:19:49'),
(21, '📢 Important Announcement: New Store Hours! 📢', 'Please note that our physical store will be operating under new hours starting May 5th, 2025. \r\nOur updated hours are: \r\nWeekdays 10am - 10pm\r\nWeekends 8am - 12am \r\nThank you for your understanding!', NULL, NULL, NULL, '2023-10-20 22:28:01'),
(22, '🤔 What Styles Would You Love to See Next? 🤔', 'We\'re always looking to bring you the latest and greatest in fashion! Tell us what kind of clothing you\'d love to see in our upcoming collections. Your feedback helps us shape the future of Superme!', NULL, NULL, NULL, '2023-04-01 22:28:44'),
(23, '📸 Share Your Superme Style! 📸', 'We love seeing how you rock your Superme outfits! Tag us in your photos on social media using #SupermeStyle for a chance to be featured on our page! Let\'s inspire each other with amazing looks!', NULL, NULL, NULL, '2020-08-30 22:29:11'),
(24, '🌍 Superme Cares: Our Commitment to Sustainability 🌍', 'At Superme, we\'re committed to making a positive impact. We\'re actively exploring more sustainable practices in our sourcing and production. Stay tuned for updates on our journey towards a greener future!', NULL, NULL, NULL, '2025-03-21 22:29:23'),
(25, '👋 Meet Our New Brand Ambassador! 👋', 'We\'re thrilled to welcome Lady Gagai to the Superme family! She will be collaborating with us on exciting new projects and showcasing their favorite Superme looks. Give her a warm welcome!', '25ce85c1adfccc35d22f945686a807.jpg', NULL, NULL, '2024-06-01 22:30:40'),
(26, '✨ New Arrivals Just Dropped! ✨', 'Fresh styles have landed at Superme! Be the first to rock our latest collection and enjoy a special introductory offer. Grab the voucher below and upgrade your wardrobe with the freshest looks. Don\'t miss out!', NULL, '/get-voucher?token=3484577bb1135c374b07475ea96863bc431632ef5ef2c41b350bf08558a36b8112fe58a9a88fce9d669f18bd943e04fbdd5745e52d0c73c5856fa38eb90b2a592a5b091c4f6c7193bb740e', 'Get Voucher', '2025-01-01 22:21:23'),
(27, '🔥 Weekend Wardrobe Refresh! 🔥', 'Ready to revamp your weekend style? Superme\'s got you covered! Use the voucher below to get a sweet discount on all weekend-perfect outfits. Time to look your best!', NULL, '/get-voucher?token=86a2ec4a314dfeae662367d7dfc5421436c8e55a81066447a3be7c87e80d3abee1d96a50808174a9ee5efbb58263e5347666383f2487b55c778c20a6580a539ce80fff920a27c3c9053548', 'Get Voucher', '2023-02-25 22:23:44'),
(28, '🎁 Treat Yourself Tuesday! 🎁', 'It\'s Tuesday, and you deserve a treat! Enjoy a special discount on your Superme favorites today only. Use the voucher below to indulge in some retail therapy. You earned it!', NULL, '/get-voucher?token=792c9709016ea52f7130a616c4b52bf74dba5cd4c8225f70516b43ac96b13aaab9f97e7b8bb7876de1563cf2543b441b4aefe2032816253251b4934769ae8ce14613f20c40faaa47bd64fd', 'Get Voucher', '2023-06-22 22:22:13'),
(29, '👖 Denim Delight Discount! 👖', 'Calling all denim lovers! Get a fantastic discount on all our stylish jeans, jackets, and more. Use the voucher below to upgrade your denim game. Superme has the perfect fit for you!', NULL, '/get-voucher?token=cf1596099a63a389e5d8cd214a73f2b7106e9ea9567c6eeb686e046aa8f97462cf91e721d9df51319ec7bdd5f104e8f6243c83b9bd5e990da089d404224698e555e476090e8b66511826bd', 'Get Voucher', '2021-09-14 22:22:52'),
(30, '✨ Style Spotlight: Dresses! ✨', 'Our dress collection is stealing the show! From flowy maxis to chic minis, find your perfect dress at a special discounted price. Use the voucher below to look effortlessly fabulous.', NULL, '/get-voucher?token=11762c6da6b33112f9cbe77427951901bb392dc269aedb8de663a86802cadc052aa500f8d616c419d9abec2b22d4f5e35317024b55ea183788dcb392babfb62946de38ddfcabf92ad8b0b7', 'Get Voucher', '2024-02-27 22:24:07'),
(31, '🧥 Cozy Up with Superme Outerwear! 🧥', 'Stay warm and stylish with our amazing collection of outerwear! Get a special discount using the voucher below. Perfect for those cooler evenings or adding a layer of chic to any outfit.', NULL, '/get-voucher?token=61da8470c5c56e9e6cfa99df6e576e5f7790e8a9da413506b325c4f9a8ec6e2394ccdd15f14ff78e6ea4633c8e511f3c48da8080d17fb31eaed9642833668a0e2a5307182ad5b53ccd3c7f', 'Get Voucher', '2022-07-20 22:24:25'),
(32, '💖 Superme Loves Loyalty! 💖', 'We appreciate your amazing support! As a thank you, enjoy this exclusive voucher on your next purchase. It\'s our little way of saying \"thank you\" for being part of the Superme family.', NULL, '/get-voucher?token=5c864674719a17530f193d0bb477eb683912dd8ee01b068870551b50fb2514ff1fb399385a706e31de5a2b2cb6a05f0e8e1c3ccdc7d8e187c139b6966bae0da7521fa27efd17d4674a06b9', 'Get Voucher', '2019-02-27 22:25:00'),
(33, '🎁 Surprise Savings Inside! 🎁', 'Unlock a special discount just for you! Use the voucher below to reveal your surprise savings on your Superme order. Happy discovering!', NULL, '/get-voucher?token=3bc46a86a7fbc27ea2e123dc2f42dd5396588a1d60fa6ae9e0b95e0ebb31d15a7ef51731c25da452abc776c4d1c0c19b13880a6bdf95f75dd17744817ac846e2a528d8559d911a47805745', 'Get Voucher', '2021-06-01 22:25:39'),
(34, '✨ Last Chance: Spring Styles Discount! ✨', 'Our spring collection sale is ending soon! Don\'t miss your chance to grab those fresh spring styles at a discounted price. Use the voucher below before it\'s too late!', NULL, '/get-voucher?token=1a8cc14caa1550cae2d31ea850fb09eb6d142d612605e8a8ab6eddf991e04933f3cffe10e6c7c340f71ba4609673f2d78de02e1caaf2d0ae2107e97d180d5b631acf726123e2e0ce9181f6', 'Get Voucher', '2022-06-25 22:26:15'),
(35, 'new voucher', 'voucherfjewlkafj', NULL, '/get-voucher?token=6493a824b11d08ff055384e2c3ac43622009a9f3b86c21b5335a213af284a5be07210d17c6d119a5079f4425e557df47cd2ff5508c6005dcc6dc535f701c14ff3aa902f354b6f3a7f2afe2', 'get voucher', '2025-05-02 08:08:44');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` char(30) NOT NULL,
  `status` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `contact_number` varchar(12) NOT NULL,
  `address` longtext NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `postal_code` char(5) NOT NULL,
  `shipping_type` varchar(8) NOT NULL,
  `payment_option` varchar(6) DEFAULT NULL,
  `payment_card_last4` char(4) DEFAULT NULL,
  `payment_card_brand` varchar(20) DEFAULT NULL,
  `voucher_id` char(13) DEFAULT NULL,
  `voucher_value` int(11) DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  `expired_at` datetime DEFAULT NULL,
  `is_processing` tinyint(1) NOT NULL,
  `total_price` double(15,2) NOT NULL,
  `account_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `status`, `name`, `contact_number`, `address`, `city`, `state`, `postal_code`, `shipping_type`, `payment_option`, `payment_card_last4`, `payment_card_brand`, `voucher_id`, `voucher_value`, `creation_time`, `expired_at`, `is_processing`, `total_price`, `account_id`) VALUES
('297bf54afec638c31b463386728965', 'Canceled', 'afewlkj', '012-32131213', 'fwaef', 'fewajlkfjwa', 'Kelantan', '12331', 'Standard', 'Card', '4242', 'Visa', NULL, NULL, '2025-05-02 08:15:21', NULL, 0, 58.00, 36),
('2b2f827bdeb7449742ea9d32b93c5a', 'Delivered', 'Jie', '011-12345678', 'No. 1, Jalan Aman 2, Taman Bahagia, 47300 Petaling Jaya, Selangor', 'Petaling Jaya', 'Selangor', '47300', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2022-04-30 21:02:13', NULL, 0, 169.90, 35),
('34705a2c9ed0fe88893f402c766b49', 'Delivered', 'Obama', '011-12345678', 'No. 12A, Jalan Cempaka 3, Taman Cempaka, 70450 Seremban, Negeri Sembilan.', 'Seremban', 'Negeri Sembilan', '70450', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2020-08-15 21:43:14', NULL, 0, 107.90, 19),
('45f73dd64432f9ed0e50111a3a41b7', 'Canceled', 'Zack', '012-31232132', 'No 123, abc street', 'def city', 'Kelantan', '12345', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2025-04-27 15:51:31', NULL, 0, 111.00, 35),
('4d53984c23c085784245e566d0dcfd', 'Delivered', 'Kenny', '012-31231122', 'No 1, Jalan I love you', 'Pasir Gudang', 'Johor', '30000', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2025-01-13 16:03:31', NULL, 0, 165.00, 35),
('6705137575bb582ea04a71f459e92c', 'Delivered', 'Superman', '011-12345678', 'No. 27, Lorong Dahlia 3, Taman Seri Mawar, 08000 Sungai Petani, Kedah.', 'Sungai Petani', 'Kedah', '08000', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2023-04-27 21:25:23', NULL, 0, 1417.11, 19),
('68672442b0fcebd02cb90157c15474', 'Delivered', 'Jason', '011-12345678', 'No. 15, Jalan Aman 2, Taman Bahagia, 47300 Petaling Jaya, Selangor', 'Petaling Jaya', 'Selangor', '47300', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2023-08-16 20:29:22', NULL, 0, 1181.98, 19),
('6ab7dc33d97225e3fad8545ab17655', 'Delivered', 'Ali', '011-12345678', 'Lot 879, Jalan Bunga Tanjung, 87007 Labuan F.T., Wilayah Persekutuan Labuan.', 'Labuan F.T.', 'Labuan', '87007', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2024-09-27 21:54:01', NULL, 0, 4089.90, 19),
('748bab48e7af86f1e2fa3deea6eaae', 'Delivered', 'Lonely', '011-12345678', 'Lot 234, Taman Dahlia Indah, Jalan Batu Kawa, 93250 Kuching, Sarawak.', 'Kuching', 'Sarawak', '93250', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2020-10-27 21:28:24', NULL, 0, 30128.00, 19),
('79d36fea6cefdd2f1f413d33ffe3ab', 'Delivered', 'Bong', '011-12345678', 'No. 18, Lorong Delima 2, Taman Seri Delima, 11700 Gelugor, Pulau Pinang.', 'Gelugor', 'Pulau Pinang', '11700', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2023-09-27 21:42:26', NULL, 0, 123.51, 19),
('7fa7bd8aa9c36755290793578c4069', 'Delivered', 'xin yi', '011-12345678', 'Block C, Apartment Anggerik, Jalan Sepangar Laut, 88400 Kota Kinabalu, Sabah.', 'Kota Kinabalu', 'Sabah', '88400', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2021-07-27 21:27:33', NULL, 0, 215.70, 19),
('82d4c7171b05eaeaf23e0ec7e5e24b', 'Delivered', 'Gary', '011-12345678', 'PT 1234, Jalan Sekolah Kubang Kerian, Kampung Wakaf Mek Zainab, 15200 Kota Bharu, Kelantan.', 'Kota Bharu', 'Kelantan', '15200', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2023-09-27 21:31:52', NULL, 0, 1655.95, 19),
('89da69da3ace8ea5402f4df94b88e7', 'Delivered', 'mr lam', '012-3213213', 'fwaefjlkj', 'abc', 'Kedah', '12321', 'Standard', 'Card', '4242', 'Visa', 'e8ac0efc1f7dc', 10, '2025-05-02 08:12:07', NULL, 0, 48.00, 36),
('8e3f5fd5b4512638fb2ecd67ff1d5e', 'Delivered', 'Biden', '011-12345678', 'Lot 1015, Kampung Bukit Pak Apil, Mukim Kuala Nerus, 21300 Kuala Terengganu, Terengganu.', 'Kuala Terengganu', 'Terengganu', '21300', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2022-03-27 21:26:36', NULL, 0, 10695.00, 19),
('a8b5480ef05bc4a1529f3667cd486e', 'Delivered', 'Wen Jing', '011-12345678', 'Lot 567, Jalan Padang Nyu, Kampung Paya Sena, 01000 Kangar, Perlis.', 'Kangar', 'Perlis', '01000', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2025-01-27 21:43:58', NULL, 0, 193.00, 19),
('ad19b9737e8ecd242c6e5ac9ee20c8', 'Delivered', 'Hoi Cheng Yu', '011-12345678', 'No. 22, Jalan P14/2, Presint 14, 62000 Putrajaya, Wilayah Persekutuan Putrajaya.', 'Putrajaya', 'Putrajaya', '62000', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2023-09-27 21:54:52', NULL, 0, 2571.80, 19),
('afde66221c39982a8f30e774387483', 'Delivered', 'Ivan', '012-3456789', 'NO 11, Jalan Superman', 'Super City', 'Johor', '12345', 'Standard', 'Card', '4242', 'Visa', NULL, NULL, '2025-04-03 14:47:30', NULL, 0, 665.00, 32),
('b6629aafba339fb06cbedf89ecb5e1', 'Delivered', 'Zack', '011-12345678', 'Unit 18-A, Menara Jaya, Jalan Tun Razak, 50400 Kuala Lumpur.', 'Kuala Lumpur', 'Kuala Lumpur', '50400', 'Express', 'Wallet', NULL, NULL, NULL, NULL, '2023-11-30 21:29:25', NULL, 0, 8737.00, 19),
('b7a02829cd282fabc285b39470bfa3', 'Delivered', 'Wen Jing', '012-3456788', 'No 123, Jalan 123', 'Puchong', 'Selangor', '40000', 'Standard', 'Card', '4242', 'Visa', NULL, NULL, '2024-11-07 14:55:53', NULL, 0, 44.17, 19),
('b89f5e8477569d2034d2a8805fa113', 'Delivered', 'Caby', '011-12345678', 'No. 1, Jalan Aman 2, Taman Bahagia, 47300 Petaling Jaya, Selangor', 'Petaling Jaya', 'Selangor', '47300', 'Standard', 'Card', '4242', 'Visa', NULL, NULL, '2022-06-09 21:03:01', NULL, 0, 33.80, 35),
('cdeacdde72a6acc89484e8f27ca549', 'Delivered', 'Trump', '011-12345678', 'No. 3, Lorong Mawar 7, Taman Mawar, 25150 Kuantan, Pahang.', 'Kuantan', 'Pahang', '25150', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2021-12-27 21:51:45', NULL, 0, 4628.00, 19),
('e50e318da931f01580c09f8b4b08e3', 'Preparing', 'Ken', '011-12345678', 'No. 5, Jalan Kenanga 1, Taman Kenanga, 75200 Melaka.', 'Melaka', 'Melaka', '75200', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2025-04-27 21:30:42', NULL, 0, 6299.00, 19),
('f3f7e331c594de080383661aca8045', 'In Transit', 'Xi', '012-3456789', 'No 11, Jalan Alor', 'Kuala Lumpur', 'Kuala Lumpur', '40000', 'Express', 'Card', '4242', 'Visa', NULL, NULL, '2025-04-27 14:49:10', NULL, 0, 84.99, 32),
('faa9c6186e64ff6fb4fc03302af843', 'Delivered', 'Abu', '011-12345678', 'No. 16, Jalan Merpati 5, Taman Merpati, 30250 Ipoh, Perak', 'Ipoh', 'Perak', '30250', 'Standard', 'Wallet', NULL, NULL, NULL, NULL, '2024-01-31 21:53:07', NULL, 0, 918.00, 19),
('fcd5e0f994190b8db8e24a467de567', 'Canceled', 'Zack', '012-3456789', 'No 1, Zackery street', 'Petaling Jaya', 'Selangor', '40000', 'Standard', 'Card', '4242', 'Visa', NULL, NULL, '2025-04-27 14:54:13', NULL, 0, 164.00, 35);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
  `order_id` char(30) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `colour` varchar(50) NOT NULL,
  `size` char(3) NOT NULL,
  `price` double(15,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_id`, `product_variant_id`, `name`, `colour`, `size`, `price`, `quantity`, `product_id`, `category_id`) VALUES
('297bf54afec638c31b463386728965', 443, 'ken glove', 'black', 'S', 50.00, 1, NULL, 26),
('2b2f827bdeb7449742ea9d32b93c5a', 40, 'Polo Ralph Lauren', 'Orange', 'L', 96.00, 1, 23, 8),
('2b2f827bdeb7449742ea9d32b93c5a', 51, 'Women\'s Cardio Fitness Muscle Back Tank Top My Top', 'Black', 'S', 20.00, 2, 25, 9),
('2b2f827bdeb7449742ea9d32b93c5a', 87, 'New Era 920 Neyyan Mini Cap', 'Black', 'M', 18.90, 1, 32, 22),
('34705a2c9ed0fe88893f402c766b49', 58, 'Loose Fit Zip-through Sweatshirt', 'Grey Marl', 'S', 99.90, 1, 26, 10),
('45f73dd64432f9ed0e50111a3a41b7', 39, 'Polo Ralph Lauren', 'Orange', 'M', 96.00, 1, 23, 8),
('4d53984c23c085784245e566d0dcfd', 157, 'C Trench Coat Women', 'Natural', 'M', 150.00, 1, 47, 12),
('6705137575bb582ea04a71f459e92c', 11, 'Hanes - ComfortSoft 100% Cotton T-Shirt', 'Oxford Gray', 'S', 36.17, 3, 13, 5),
('6705137575bb582ea04a71f459e92c', 47, 'Cotton On Tank Top', 'Oatmeal Marle', 'S', 53.90, 24, 24, 9),
('68672442b0fcebd02cb90157c15474', 68, 'Hoodie Unisex Asics', 'Lavender Grey', 'M', 349.00, 3, 27, 4),
('68672442b0fcebd02cb90157c15474', 412, 'Couple Silk Pajamas Set', 'White', 'S', 49.99, 1, 71, 19),
('68672442b0fcebd02cb90157c15474', 434, 'Pile-lined gloves', 'Black', 'Fre', 69.99, 1, 76, 25),
('6ab7dc33d97225e3fad8545ab17655', 347, 'Sweat Wide Pants', 'Black', 'M', 79.90, 21, 65, 16),
('6ab7dc33d97225e3fad8545ab17655', 348, 'Sweat Wide Pants', 'Black', 'L', 79.90, 30, 65, 16),
('748bab48e7af86f1e2fa3deea6eaae', 168, 'Hybrid Down Coat Men', 'Black', 'S', 120.00, 30, 48, 12),
('748bab48e7af86f1e2fa3deea6eaae', 169, 'Hybrid Down Coat Men', 'Black', 'L', 120.00, 66, 48, 12),
('748bab48e7af86f1e2fa3deea6eaae', 170, 'Hybrid Down Coat Men', 'Black', 'M', 120.00, 4, 48, 12),
('748bab48e7af86f1e2fa3deea6eaae', 171, 'Hybrid Down Coat Men', 'Dark Gray', 'S', 120.00, 52, 48, 12),
('748bab48e7af86f1e2fa3deea6eaae', 172, 'Hybrid Down Coat Men', 'Dark Gray', 'M', 120.00, 99, 48, 12),
('79d36fea6cefdd2f1f413d33ffe3ab', 11, 'Hanes - ComfortSoft 100% Cotton T-Shirt', 'Oxford Gray', 'S', 36.17, 3, 13, 5),
('7fa7bd8aa9c36755290793578c4069', 48, 'Cotton On Tank Top', 'Oatmeal Marle', 'M', 53.90, 2, 24, 9),
('7fa7bd8aa9c36755290793578c4069', 55, 'Loose Fit Zip-through Sweatshirt', 'Black', 'S', 99.90, 1, 26, 10),
('82d4c7171b05eaeaf23e0ec7e5e24b', 88, 'Women\'s Foldable Straw Sun Hat with Wide Brim and Adjustable Fit', 'Pitch White', 'M', 49.99, 5, 34, 22),
('82d4c7171b05eaeaf23e0ec7e5e24b', 197, 'Women Harry Potter Quidditch Regular Fit Short', 'Beige', 'L', 69.90, 20, 53, 14),
('89da69da3ace8ea5402f4df94b88e7', 444, 'ken glove', 'white', 'S', 50.00, 1, NULL, 26),
('8e3f5fd5b4512638fb2ecd67ff1d5e', 198, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Light Blue', 'S', 89.00, 20, 54, 15),
('8e3f5fd5b4512638fb2ecd67ff1d5e', 199, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Light Blue', 'M', 89.00, 23, 54, 15),
('8e3f5fd5b4512638fb2ecd67ff1d5e', 200, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Light Blue', 'L', 89.00, 77, 54, 15),
('a8b5480ef05bc4a1529f3667cd486e', 431, 'Belting-Print Silk Scarves', 'Belting-Print', 'Fre', 89.00, 2, 74, 24),
('ad19b9737e8ecd242c6e5ac9ee20c8', 355, 'Sweat Wide Pants', 'Khaki', 'M', 79.90, 30, 65, 16),
('ad19b9737e8ecd242c6e5ac9ee20c8', 357, 'Sweat Wide Pants', 'Khaki', 'XL', 79.90, 2, 65, 16),
('afde66221c39982a8f30e774387483', 29, 'Basic Cool Era Dark Green Short Sleeve T-Shirt', 'Dark Green', 'S', 89.00, 2, 21, 5),
('afde66221c39982a8f30e774387483', 33, 'Basic Cool Era Dark Green Short Sleeve T-Shirt', 'Dark Green', 'XXL', 89.00, 1, 21, 5),
('afde66221c39982a8f30e774387483', 73, 'EKD Badge Cotton Hoodie Burberry', 'Pool', 'M', 390.00, 1, 28, 4),
('b6629aafba339fb06cbedf89ecb5e1', 201, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Dark Blue', 'S', 89.00, 10, 54, 15),
('b6629aafba339fb06cbedf89ecb5e1', 202, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Dark Blue', 'M', 89.00, 66, 54, 15),
('b6629aafba339fb06cbedf89ecb5e1', 203, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Dark Blue', 'L', 89.00, 22, 54, 15),
('b7a02829cd282fabc285b39470bfa3', 11, 'Hanes - ComfortSoft 100% Cotton T-Shirt', 'Oxford Gray', 'S', 36.17, 1, 13, 5),
('b89f5e8477569d2034d2a8805fa113', 142, 'Cotton long neck socks', 'Black', '25-', 12.90, 2, 44, 23),
('cdeacdde72a6acc89484e8f27ca549', 192, 'Men Sweat Shorts', 'Sienna', 'S', 70.00, 66, 52, 14),
('e50e318da931f01580c09f8b4b08e3', 195, 'Women Harry Potter Quidditch Regular Fit Short', 'Beige', 'S', 69.90, 30, 53, 14),
('e50e318da931f01580c09f8b4b08e3', 196, 'Women Harry Potter Quidditch Regular Fit Short', 'Beige', 'M', 69.90, 60, 53, 14),
('f3f7e331c594de080383661aca8045', 434, 'Pile-lined gloves', 'Black', 'Fre', 69.99, 1, 76, 25),
('faa9c6186e64ff6fb4fc03302af843', 193, 'Men Sweat Shorts', 'Sienna', 'M', 70.00, 13, 52, 14),
('fcd5e0f994190b8db8e24a467de567', 38, 'Polo Ralph Lauren', 'Orange', 'S', 96.00, 1, 23, 8),
('fcd5e0f994190b8db8e24a467de567', 435, 'Sport Socks', 'Black', '25-', 30.00, 2, 46, 23);

-- --------------------------------------------------------

--
-- Table structure for table `poll_option`
--

DROP TABLE IF EXISTS `poll_option`;
CREATE TABLE `poll_option` (
  `id` int(11) NOT NULL,
  `text` varchar(100) NOT NULL,
  `notice_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_option`
--

INSERT INTO `poll_option` (`id`, `text`, `notice_id`) VALUES
(14, 'New Hoodie Designs', 19),
(15, 'Summer T-Shirts', 19),
(16, 'Casual Dresses', 19),
(17, 'Sport Shorts', 19),
(18, 'Kids\' Outfits', 19),
(19, 'Sustainable Fashion', 22),
(20, 'Athleisure', 22),
(21, 'Formal Wear', 22),
(22, 'Vintage-Inspired', 22);

-- --------------------------------------------------------

--
-- Table structure for table `poll_vote`
--

DROP TABLE IF EXISTS `poll_vote`;
CREATE TABLE `poll_vote` (
  `account_id` int(11) NOT NULL,
  `notice_id` int(11) NOT NULL,
  `poll_option_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_vote`
--

INSERT INTO `poll_vote` (`account_id`, `notice_id`, `poll_option_id`) VALUES
(19, 19, 14),
(32, 19, 14),
(35, 19, 15);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `price` double(15,2) NOT NULL,
  `photo` longtext DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `description`, `price`, `photo`, `creation_time`, `category_id`) VALUES
(1, 'Black Relaxed Fit Heavyweight 100% Cotton T-Shirt', 'Our relaxed fit tee has a longer body length, features a dropped shoulder, longer sleeve and fits wider through the body.\r\n\r\nOur heavyweight t-shirt is a time-tested staple at the core of any wardrobe. Designed with a ribbed crew neck, short sleeves and dual stitched hem.\r\n\r\nHeavyweight fabric - 300gsm\r\nRelaxed fit\r\nCrew neck\r\nShort sleeves\r\n100% Cotton', 79.00, 'e5109cdba00c72e20d71b0fa2bbe26.jpg\r\n2906dfedc03d499217632e5f4d0da1.jpg\r\nb81dba84e7547424141fc276abd2c7.jpg\r\nf56529c37a7aaa6c2fec2e57729cd0.jpg', '2025-03-30 13:55:05', 5),
(13, 'Hanes - ComfortSoft 100% Cotton T-Shirt', 'Fabric: 5.2-ounce 100% ComfortSoft cotton 99/1 cotton/poly (Ash) 0/10 cotton/poly (Light Steel) \r\nFeatures: Double-needle coverseamed crewneck Lay-flat collar Tag-free label Shoulder-to-shoulder taping Double-needle sleeves and hem\r\n\r\nPlease note: This product is transitioning from tag-free labels to tearaway labels.', 36.17, '46c279ba71b6816c47308dec38a7a0.jpg\r\n16032af73024f07be89204952ffc0f.jpg\r\n29d5708e3bb4a50276ea0ab8bca6d3.jpg\r\n91ff4d5144ba30622f6eff669785b3.jpg\r\nce1a1f544f0ef0c8a69a0903e78699.jpg', '2025-04-27 07:59:28', 5),
(21, 'Basic Cool Era Dark Green Short Sleeve T-Shirt', 'New Era Basic Cool Era Dark Green Short Sleeve T-Shirt', 89.00, '5a0f31e864e8364475e41a0d811b30.jpg\r\n1c53aa65b32521e1b8ebdd269ec1e1.jpg\r\ncc258fe33970f4722bd040c7624289.jpg\r\n038aa965c81b9b78de4e1f64ca5714.jpg', '2025-04-27 08:46:17', 5),
(22, 'Original Fit Mesh Polo Shirt', 'An American style standard since 1972, the Polo shirt has been imitated, but never matched. Over the decades, Ralph Lauren has reimagined his signature style in a wide array of colours and fits, yet all retain the quality and attention to detail of the initial icon. Treated for a timeworn look, this version is cut in our Original Fit, which is slightly more relaxed on the shoulder than our Classic Fit and has a longer back length.\r\n\r\nDetails\r\n- Original Fit: Slightly more relaxed at the shoulder than our Classic Fit and with a longer back length.\r\n- Size M has a 72.4 cm front body length, a 77.5 cm back body length, a 48.3 cm shoulder and a 111.8 cm chest.\r\n- Ribbed Polo collar. Two-button placket.\r\n- Short sleeves with ribbed armbands.\r\n- Tennis tail.\r\n- Signature embroidered Pony on the left chest.\r\n- 100% cotton. Machine washable. Imported.\r\n- Due to the natural characteristics of this material, the colouring may rub off onto fabrics and upholstery.\r\n- Male-presenting model is 6\'1\"/185 cm and wears a size M. Female-presenting model is 5\'10\"/178 cm and wears a size S.', 119.99, '98a0214c1416162913eb6288a6a982.jpg\r\n70127c703109f7568031dd5ebe09aa.jpg\r\n243a4affc6f0d7a7d9348c23b9f3d3.jpg\r\nd73974d60d4a5337b91f365b58f8f8.jpg\r\n0885ac3f907e7dc4a674805e8df157.jpg\r\n18b04f5bf34cb378f05ceb8174c2fe.jpg\r\n98d479d11c9d0bdb4f40b99d472300.jpg\r\n8f493c09dfb492f83730ba39a9b756.jpg', '2025-04-27 09:03:31', 8),
(23, 'Polo Ralph Lauren', 'Orange cotton short sleeve polo shirt from Polo Ralph Lauren featuring a ribbed polo collar, a contrast embroidered logo at the chest and a straight hem.\r\n\r\nComposition\r\nCotton 100%\r\n\r\nWashing instructions\r\nMachine Wash\r\n\r\nWearing\r\nThe model is 1.87 m wearing size L', 96.00, '362e25476ab665e99ea764da22d65a.jpg\r\n286055d92a9d537430c299ec76a81d.jpg\r\nfa29d2ecb79fe0f4a48b87cd7803b7.jpg\r\nab053102cdaa7bb0727b503f4c9da5.jpg\r\n85bbee24ee3a510d769acff1dceac2.jpg', '2025-04-27 09:08:07', 8),
(24, 'Cotton On Tank Top', '95% Cotton, 5% Elastane\r\nMachine Washable\r\n\r\n- Solid tone basic rib knit tank top\r\n- Crew neckline\r\n- Unlined\r\n- Relaxed fit\r\n- Slip on style\r\n- Sleeveless\r\n- Stretchable\r\n- Cotton blend', 53.90, '98bba8df566d2cf348b0db578f9a28.jpg\r\nffbdef4dbd4a08ba327d61318a4390.jpg\r\n0490f4bfa6be9f8ef6b7dc54c626df.jpg\r\n11e45113b9b1d4634c76f50096d240.jpg\r\n1cbe7f4761e9088127ee4936cba886.jpg\r\nc351301f8cab845fb421392ad3823f.jpg\r\n251c3e3ccadcab68681b4589140cd8.jpg\r\n1eeda0f9ed79edd0066f59fa2d735b.jpg\r\na441fcedf910fe0071c5fbf9fb72cd.jpg', '2025-04-27 10:14:13', 9),
(25, 'Women\'s Cardio Fitness Muscle Back Tank Top My Top', 'A 1st price muscle back tank top with a rounded hemline that can provide all the comfort you need for your workout. You\'ll appreciate its more feminine and trendier new standard cut.\r\n\r\nA lightweight, breathable basic fitness tank top designed for workouts or to go with any relaxed style.', 20.00, '875855ed49e2c5df1894283f58e176.jpg\r\n10955a471c3303804b1752a3bacdf3.jpg\r\n9afac43ed0cb6de6f7b04bdb357435.jpg\r\n1bf2bdfa597d5587464c3039892ff9.jpg\r\n3bcd513f457e639a13d22cdd9c1fed.jpg', '2025-04-27 10:18:52', 9),
(26, 'Loose Fit Zip-through Sweatshirt', 'Zip-through in lightweight sweatshirt fabric made from a cotton blend with a soft brushed inside. Jersey-lined, drawstring hood, a zip down the front, diagonal, welt side pockets and wide ribbing at the cuffs and hem. Loose fit for a generous but not oversized silhouette.\r\n\r\nLength:\r\nRegular length\r\n\r\nSleeve Length:\r\nLong sleeve\r\n\r\nFit:\r\nLoose fit', 99.90, 'adc0067d0666c039ac9a4ca77b99cb.jpg\r\n448771554d3737c4509f4b1879614e.jpg\r\nf19ae4b1582121c643bb778bf9bb6e.jpg\r\na0505e8ff89573d1a0409baed15112.jpg\r\n8ab626599199c1b1c0702dd377a63b.jpg\r\nc390e9459f740b72a9a901c8315994.jpg', '2025-04-27 10:24:53', 10),
(27, 'Hoodie Unisex Asics', 'This hoodie promotes versatility with practical and authentic design​ details.​\r\n\r\nThe main material incorporates a bulky jersey made of recycled polyester.​\r\n\r\nIt promotes good comfort by adopting lightweight fabrics and ribbed features.​', 349.00, '5b02f76ec765746eb2bda51fc5cde1.jpg\r\n0cec44b75c1003f1b828bfd8b61d08.jpg\r\n33b445616ad32c22b33f2e67ec43e1.jpg\r\nf5fa811f41b6e575f0a831bf0c9c0c.jpg', '2025-04-27 10:33:16', 4),
(28, 'EKD Badge Cotton Hoodie Burberry', 'A hoodie in lightweight loop-back cotton, cut to a relaxed fit. The print at the back is inspired by a Burberry catalogue from the first half of the 20th century and features our Equestrian Knight Design surrounded by the words \'Burberry Protection\' – a nod to our long-standing tradition of crafting innovative outerwear for explorers and pioneers.\r\n\r\n– Drawcord hood\r\n– Pouch pockets\r\n– Rib-knit trims\r\n– Printed Equestrian Knight Design at chest\r\n– Relaxed fit: a looser cut than our regular fit.\r\n– Machine wash\r\n– Item 81062701', 390.00, '743617491b5fdaf93c86eba63e022a.jpg\r\n531cc8d8088070bed29b73619ea508.jpg\r\n7f2f2bc2289b60997805cba308d185.jpg\r\n6e5d2f227ae98ced0a3407f370444c.jpg\r\ncb343548244986373accf844ef2863.jpg', '2025-04-27 10:40:59', 4),
(29, 'Miracle Air Jacket | Wool Like | Pattern | Co-ord', 'Incredibly comfortable thanks to the lightweight, stretchy, and quick-drying fabric jointly developed by Toray.\r\n\r\nMade with two-way stretch fabric.\r\n\r\nWith DRY technology.\r\n\r\nDetails\r\n- Sleek silhouette for any occasion.\r\n- Sleek regular fit that’s less fitted at the waist.\r\n- Versatile jacket with a natural rounded shape from shoulders to sleeves.', 249.90, '6b104a012877eaa530d26e7f1360f9.jpg\r\n1c4ee07af7d9c0871f29896b2e262e.jpg\r\n60c5d45256f4d07ce4f74ac69b5871.jpg\r\na4094765121af37354e82d5e9af7f2.jpg', '2025-04-27 10:47:28', 11),
(30, 'KEFITEVD Men\'s Jacket Winter Warm Bomber Jackets Full Zip Windbreaker Casual Windproof Jackets Coats', 'This men\'s jacket is a stylish and classic must-have item in winter wardrobes, suitable for various occasions from daily life to outdoor activities.\r\n\r\nCrafted with high-quality insulation, this jacket ensures exceptional warmth for cold winter days, making it perfect for outdoor activities and daily wear.\r\n\r\nIts bomber design offers a stylish yet casual look, allowing easy pairing with various outfits, from jeans to joggers, making it suitable for any occasion.\r\n\r\nEquipped with wind-resistant fabric, this jacket effectively shields against harsh weather, providing ultimate comfort and protection without sacrificing breathability.\r\n\r\nFabric type Polyester\r\nCare instructions Hand Wash Only\r\nMaterial: Polyester. Warm Windproof and Comfortable.\r\nWarm Lining - The pure color quilted lining is both warm and stylish. Full zip up, stylish stand collar and elasticated cuffs - all these details keep you warm.\r\nMulti Pockets: 2 hand pockets, 2 pencil pockets and 1 zipper pocket on the left sleeve, 1 interior patch pocket for safe storage of everyday essentials.\r\nFeatures: Rib knit collar, cuffs and hem for a comfortable fit. Excellent windproof design can prevent cold air from entering the interior of the jacket and minimize body heat loss.\r\nOccasion：This is a stylish and classic men\'s jacket that can be paired with jeans or pants, making it a must-have item in winter wardrobes. Perfect for daily leisure life, sports, business work, party, golf, outdoor, etc.', 407.00, '492dedd7a1e4a55ebfe6fbdd34e768.jpg\r\n15b1791d8b24d9339ee3efda6f09b0.jpg', '2025-04-27 10:53:31', 11),
(31, 'Cheetah Men Microfiber Polyester Track Pants with Flat Hem and Side Stripes Tracksuits', 'Cheetah Micro Fibre Track Pants\r\n\r\nRegular Fit\r\nFlat opening legs\r\n2 sides pocket without zip\r\nBack pocket with zip\r\nNo inner lining\r\n100% polyester\r\nDrawcord on waist\r\nCare Details\r\n\r\nWash Separately\r\nMachine Wash, Cold\r\nDo Not Bleach\r\nWarm Iron / Do Not Iron On Printing\r\nTumble Dry Low\r\nDo Not Dry Clean', 39.90, '1ee57379d61ce4146d981531a3c0c3.jpg\r\nee4d3d16bc4e04243abf9ed87549d2.jpg\r\n99ae6b03c28d42f38d1ff39828da3b.jpg', '2025-04-27 10:55:54', 21),
(32, 'New Era 920 Neyyan Mini Cap', 'The 920 Neyyan Mini Cap offers a stylish and modern twist on classic headwear. Designed with a relaxed fit and an adjustable strap, this cap ensures comfort and a perfect fit for all-day wear. Made from high-quality materials, it features a mini embroidered logo that adds a subtle yet trendy touch to your look. The curved visor provides sun protection while enhancing its sleek design. Whether you’re heading to a game, running errands, or just enjoying a day out, the 920 Neyyan Mini Cap is a versatile accessory that effortlessly showcases your style and team pride.', 18.90, '5717ab7f19324071485b97db1c7367.jpg\r\nbffb291bbb4f11ef6f1736d748f7d9.jpg\r\ne844b86366f43c4767eabde7deb893.jpg', '2025-04-27 11:01:34', 22),
(34, 'Women\'s Foldable Straw Sun Hat with Wide Brim and Adjustable Fit', 'Floppy wide brim provides a perfect shade to your face, help protecting from the sun\'s harmful rays.', 49.99, 'd4ce6f84e7e851ccfe01dbb8398f01.jpg', '2025-04-27 11:06:19', 22),
(44, 'Cotton long neck socks', '- Special deodorizing yarn neutralizes odors.\r\n- Non-constrictive elastic at the ankle won’t leave sock marks.\r\n- 59% Cotton, 12% Nylon, 2% Spandex, 1% Polyester', 12.90, '34db35bd20c0e6272e1c52bf7f0930.jpg\r\n106719652ff594d9675c8947301bb7.jpg', '2025-04-27 10:32:36', 23),
(46, 'Sport Socks', '- Perfect for sport \r\n- Provide excellent support and comfort during intense games\r\n- Soft cotton blend fabric keeps sweat away from the skin to keep their feet dry and comfortable, while the cushioned - \r\n  sole Provides extra comfort and protection during long matches', 30.00, '3d07a730ed43fd85a79b15a9ac75ce.jpg', '2025-04-27 10:52:41', 23),
(47, 'C Trench Coat Women', '- 100% cotton twill outer.\r\n- Classic trench coat details.\r\n- Relaxed, sleek cut.\r\n- Easily dresses up or down.', 150.00, '9c9f298c4b10b8ff50357b5601a0f1.jpg\r\n3badb200b222155997e66ae548d737.jpg\r\nbb85c24429a0282c65bc120f0ef96b.jpg\r\ndab4bc342abfed7c55990697cc841f.jpg', '2025-04-27 11:08:26', 12),
(48, 'Hybrid Down Coat Men', 'A perfect balance of high-performance padding and warm, premium down. Jointly developed with pro snowboarder Ayumu Hirano.\r\n- A combination of lightweight yet warm cotton padding and premium down with a fill power of 750* or more. *Measured by the IDFB method\r\n- Water-repellent finish. *The fabric is coated with a water-repellent agent so the effect lasts longer. The finish is not permanent.\r\n- Hybrid-down outerwear jointly developed with Toray and pro snowboarder Ayumu Hirano.\r\n- Performance sheet padding at the arms and hem for a sleek look and easy movement.\r\n- Breathable mesh and ventilation holes keep you comfortable.\r\n- Relaxed fit can be worn both in and outside the office.\r\n- Sleek, non-quilt design.\r\n- 3-dimensional construction for unrestricted arm movement.\r\n- Hook-and-loop fastener and elastic shirring let you adjust the cuffs to block out cold wind.\r\n- Removable hood.\r\n- Convenient pockets provide plenty of storage.\r\n- Featuring waterproof fasteners at the chest pockets and inner pockets that can be reached without unzipping the front.\r\n- Textured pull tab grips for easy operation even with wet hands.\r\n- This hybrid down coat combines functionality and style.', 120.00, '1d2dd276f3c4d92b53dbf5bb5de30a.jpg\r\n8fcb09a3d171bbb016ec8f39499096.jpg\r\n6c27e40169cc2a7ad3f26429817b88.jpg\r\nf7ca5bf392416bd44f0315ecf0f51c.jpg\r\nf86667ec76842bae8ce44be940cff6.jpg', '2025-04-27 11:18:49', 12),
(49, 'Cable Crew Neck Cardigan', '- Puffy soft cotton-blend fabric.\r\n- Statement cable knit.\r\n\r\n- 51% Cotton, 33% Polyester, 14% Nylon, 2% Spandex ( 33% Uses Recycled Polyester Fiber )\r\n\r\n- Sheer: Not Sheer\r\n- Fit: Fitted\r\n- Pockets: No Pockets\r\nWashing instructions\r\nHand wash cold, Dry Clean, Do not tumble dry.', 150.00, 'a8206eb1c119f3948b82270136a00a.jpg\r\n6267f39bd64cabd239c1a07b4842ec.jpg\r\ncac119619d98eb64d2cfd499da4af9.jpg', '2025-04-27 11:31:43', 13),
(50, 'V Neck Cardigan', '- Smooth touch on the inside and out.\r\n- Special looped lining prevents pilling.\r\n- Sleek and functional side pockets.\r\n- Versatile, roomy silhouette.\r\n- Wide placket for a sleek look whether buttoned up or unbuttoned.\r\n- 100% Cotton/ Rib: 82% Cotton, 18% Polyester', 80.00, 'a80689285501e1d3946da99de7cb8f.jpg\r\nab0767919a4ebac70c935e98e8f7a3.jpg\r\n17869ba9054a1bcea3352f5fdad436.jpg', '2025-04-27 11:42:22', 13),
(51, 'Rebels Short', 'Take these fresh Rebels and go out and get some fresh air! Be bold, be fearless and embrace your inner renegade. The Rebels are Frankster\'s army green stretch cotton men\'s chino shorts. Challenge conventions and pave your own path.\r\n\r\n98% cotton, 2% spandex, and 100% ready for whatever you throw at them.\r\n\r\n- Partly elasticated waistband and a super comfortable fit.\r\n- High-quality zipper with some cheeky Frankster branding (if anyone is close enough to see this, you’re in for a good night).\r\n- Two large side-pockets. Plus a secret zippered pocket on the right-hand side so you never lose your valuables. Big enough to fit the latest iPhone or Android - no judgement here.\r\n- A pocket at the back\r\n- Belt loops on the waistband for those times you wanna jazz it up with a belt (be it for office showdowns or wedding throw-downs).', 50.00, 'f8de82b4bb1295ef8f4bf91c2ff168.jpg\r\nc8d5cca14e9437ff86484b044da8da.jpg\r\n89e64ea095815f688d2b9a7dbec639.jpg', '2025-04-27 11:49:30', 14),
(52, 'Men Sweat Shorts', '- Solid color knitted fabric\r\n- Elicited & adjustable drawstring at the waist\r\n- Front 2 side pockets\r\n- Back patch pockets\r\n- Printing at front pocket\r\n- Regular fit', 70.00, '98a551be8179d9b451ebe65839a4e0.jpg\r\nd0b9dae0cb3cc3a5daec80f7ab166e.jpg', '2025-04-27 11:53:18', 14),
(53, 'Women Harry Potter Quidditch Regular Fit Short', 'Harry Potter Special Edition\r\nSolid tone \r\nElastic waistband with hidden drawstring\r\nSide pockets & back with 1 patch pocket\r\nPant length 13-14.5 inches\r\nRegular fit\r\n55% Cotton 37% nylon 8% spandex 260gsm\r\nAll characters and elements © & ™ Warner\r\nBros. Entertainment Inc. WB SHIELD: © & ™\r\nWBEI. Publishing Rights © JKR. (s25)', 69.90, '4d5de1ca6b43fffd914d39d18a2704.jpg\r\n510b5f515e40eee121de2cdcdca232.jpg\r\n2613186003e024967719bbb7d59c8c.jpg', '2025-04-27 11:57:55', 14),
(54, 'Men Skinny Fit Cotton Spandex Stretchable Denim Jeans', 'Stretchable denim with washing\r\nWaistband with button and zipper fly closure\r\n5 pockets design\r\nOutseam length 40\"-41\"\r\nSkinny fit\r\nCotton Spandex', 89.00, 'fc35870e1dce45fa034ea125b47db3.jpg\r\n91c1bad109ec3d374bfae5953fb8a5.jpg\r\n5298eec5d5f54c51734e859d85ca57.jpg', '2025-04-27 12:00:44', 15),
(56, 'Women Stretchable Washed Denim Cotton Blend With Spandex Basic Slim Cut Jeans', 'Stretchable Washed Denim\r\nWaistband with button and zip fastening\r\n5 pockets design\r\nOutseam length 38.25-39.25 inches\r\nSlim fit\r\nCotton blend with spandex', 79.99, '764e3fdfd53168f1de566cdce843fb.jpg\r\nb5c7d5964501f82f73649bf50dcce5.jpg', '2025-04-27 12:10:52', 15),
(65, 'Sweat Wide Pants', 'Fabric details\r\nBody: 86% Cotton, 14% Polyester/ Pocket Lining: Outer Layer: 100% Cotton/ Pocket Lining: Inner Layer: 86% Cotton, 14% Polyester\r\nFunction details\r\n- Sheer: Not Sheer\r\n- Fit: Loose\r\n- Silhouette: Straight\r\n- Pockets: With Pockets\r\n\r\nWashing instructions\r\nMachine wash cold, gentle cycle, Dry Clean, Do not tumble dry.', 79.90, '9f739524294276d4fda39537715411.jpg\r\n15a84926a120501fc5557ec53f25c2.jpg\r\na67ec0c3dadeec42bd282785e75d54.jpg', '2025-04-27 10:58:15', 16),
(66, 'Straight Loose Pants', 'Material : Denim\r\nStyle : Basic, Korean, Retro, Street Style\r\nPattern : Others\r\nTall Fit : No\r\nWaist Height : Mid Waist\r\nBottoms Fit Type : Straight\r\nPlus Size : No\r\nBottoms Length : Full Length\r\nCare Instructions : Machine Washable\r\nFit : Fitted\r\nInseam Length : Full Length\r\nSize Type : Regular\r\nClosure Type : Drawstring', 59.90, '2afb01c2a9aaf3d7d64f6e620a166a.jpg\r\n8f2cb1e80818c6c0ebf612fa7d1f71.jpg\r\nce802f5c0b73bd2b6347919673744f.jpg', '2025-04-27 11:10:15', 16),
(67, 'Jogger Long', 'Material\r\n90% Polyester\r\n10% Spandex', 89.00, 'f0058c15dc950c19d97f8e4520d4d1.jpg\r\nd7c4962b97870fdf8ed35a45e2dd7a.jpg', '2025-04-27 11:15:30', 17),
(68, 'Ultra Stretch Leggings Pants', 'Fabric details\r\n[00 WHITE, 01 OFF WHITE] 52% Cotton, 33% Polyester, 15% Spandex\r\n[05 GRAY, 08 DARK GRAY, 09 BLACK, 34 BROWN, 56 OLIVE, 58 DARK GREEN] 51% Cotton, 33% Polyester, 16% Spandex\r\nFunction details\r\n- Sheer: Not Sheer(Only 01 Off White is slightly sheer)\r\n- Fit: Skinny\r\n- Silhouette: Tapered\r\n- Pockets: With Pockets\r\n\r\nWashing instructions\r\nMachine wash cold, gentle cycle, Do not Dry Clean, Do not tumble dry.', 29.90, 'd3248dadc29fecd0505d396d1cc9d4.jpg\r\na60062fbf014b3fd59c357ee1c9c15.jpg', '2025-04-27 11:24:26', 18),
(69, 'Long Sleeve Pajamas', 'Fabric details\r\nTops: 74% Cotton, 26% Polyester ( 26% Uses Recycled Polyester Fiber )/ Bottoms: 74% Cotton, 26% Polyester ( 26% Uses Recycled Polyester Fiber )\r\nFunction details\r\n- Sheer: Not Sheer(Only 31 Beige is slightly sheer)\r\n- Fit: Regular\r\n- Silhouette: Straight\r\n- Pockets: With Pockets\r\n\r\nWashing instructions\r\nMachine wash cold, gentle cycle, Do not Dry Clean, Do not tumble dry.', 99.00, 'bb7c837f83ddc10e26c3f338ef9f73.jpg\r\n3f67599dc86464c3a689f9bd63f20c.jpg', '2025-04-27 11:29:59', 19),
(70, 'Baby Dry Pajamas', 'Size\r\n18-23 months (80cm)\r\n2 years old (90cm)\r\n3-4 years old (100cm)\r\n4-5 years old (110cm)\r\n\r\nFabric details\r\nTops: Body: 71% Cotton, 25% Polyester, 4% Spandex/ Rib: 97% Cotton, 3% Spandex/ Bottoms: 71% Cotton, 25% Polyester, 4% Spandex\r\n\r\nWashing instructions\r\nMachine wash cold, Do not Dry Clean, Do not tumble dry.', 59.90, '7835e76f6e58907b67b6224f818b5c.jpg\r\nd946c4c5f6a14ae9dfb745c8ac282a.jpg', '2025-04-27 11:43:27', 19),
(71, 'Couple Silk Pajamas Set', 'Season : Autumn\r\nBottoms Length : Full Length\r\nSleeve Length : Long Sleeves\r\nPattern : Others\r\nPlus Size : Yes\r\nMaterial : silk', 49.99, '5d30c125c219aa41142ec5c6132416.jpg\r\n978835f7842f9172886c32231fa6df.jpg', '2025-04-27 11:53:44', 19),
(72, 'Quick Dry Gym Short', 'Gender : Male\r\nSports Type : Gym\r\nMaterial : Microfiber', 29.99, '55be6f5b102a10dea16cf6bf59c1ca.jpg\r\n440981f427b4402328204520380979.jpg\r\n0cabe9038f249bdf5bc41dcab890a2.jpg', '2025-04-27 12:01:16', 20),
(73, 'Polyseter Scarves', 'Material\r\n100% POLYESTER\r\n\r\nCare Label\r\n\"Hand Wash\", \"Low Iron\", \r\n\"Do Not Tumble Dry\",\r\n\"Do not bleach\",\r\n\"Do Not Dry Clean\"', 199.99, 'bfc86e0609a0e9daa8a270773b2e85.jpg', '2025-04-27 12:08:22', 24),
(74, 'Belting-Print Silk Scarves', '68\" L x 7\" W.\r\n“LRL” block-printed logo at the hem.\r\n100% silk.\r\nDry clean.\r\nImported.', 89.00, '7485912db8249c4943f62c724b5270.jpg\n4388f0af3989634574eacc633885ea.jpg\n8f4a044a9d5c864588b846121af1c7.jpg', '2025-04-27 12:14:38', 24),
(75, 'Fingerless Gloves', 'Composition:\r\nPolyester 57%, Acrylic 41%, Elastodiene 2%\r\n\r\nCare instructions\r\n- Low iron\r\n- Machine wash at 40°\r\n- Can be dry cleaned\r\n- Use softener\r\n- Line dry\r\n- Only non-chlorine bleach when needed', 29.99, '25bc3a32a7723d97cb301831073b97.jpg\r\nac86f896efc65c7619630a4731e862.jpg', '2025-04-27 12:18:47', 25),
(76, 'Pile-lined gloves', 'Composition:\r\nPolyester 88%, Elastane 12%\r\n\r\nMaterial:\r\nPU material, Teddy', 69.99, '3b90c14b1a21489c4e079f82b93573.jpg\r\n5cfefe9233f89479244bc162e2a49f.jpg', '2025-04-27 12:26:24', 25);

-- --------------------------------------------------------

--
-- Table structure for table `product_keyword`
--

DROP TABLE IF EXISTS `product_keyword`;
CREATE TABLE `product_keyword` (
  `id` int(11) NOT NULL,
  `keyword` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_keyword`
--

INSERT INTO `product_keyword` (`id`, `keyword`, `product_id`) VALUES
(154, 'comfort', 13),
(155, 't-shirt', 13),
(156, 'cotton', 13),
(157, 'hanes', 13),
(158, 'tshirt', 13),
(159, 't shirt', 13),
(169, 'dark green', 21),
(170, 'short', 21),
(171, 'sleeve', 21),
(172, 'tshirt', 21),
(173, 't-shirt', 21),
(174, 't shirt', 21),
(175, 'basic', 21),
(176, 'cool', 21),
(177, 'new era', 21),
(178, 't-shirt', 1),
(179, 'shirt', 1),
(180, 'black tshirt', 1),
(181, 'cotton shirt', 1),
(182, 'polo', 22),
(183, 'original fit', 22),
(184, 'mesh', 22),
(185, 'shirt', 22),
(186, 'navy blue', 22),
(187, 'polo', 23),
(188, 'Ralph Lauren', 23),
(189, 'cotton', 23),
(190, 'short sleeve', 23),
(191, 'tank top', 24),
(192, 'white', 24),
(193, 'cotton', 24),
(194, 'women', 25),
(195, 'cardio', 25),
(196, 'fitness', 25),
(197, 'muscle', 25),
(198, 'tank top', 25),
(202, 'zip through', 26),
(203, 'zip', 26),
(204, 'through', 26),
(205, 'loose fit', 26),
(206, 'hoodie', 27),
(207, 'asics', 27),
(208, 'lightweight', 27),
(209, 'polyester', 27),
(210, 'burberry', 28),
(211, 'drawcord', 28),
(212, 'ekd badge', 28),
(213, 'cotton hoodie', 28),
(214, 'jacket', 29),
(215, 'wool like', 29),
(216, 'coord', 29),
(217, 'co-ord', 29),
(218, 'co ord', 29),
(219, 'dry tech', 29),
(220, 'warm jacket', 30),
(221, 'full zip', 30),
(222, 'wind breaker', 30),
(223, 'wind proof', 30),
(224, 'polyester', 30),
(225, 'track pants', 31),
(226, 'cheetah', 31),
(227, 'micro fiber', 31),
(228, 'flat hem', 31),
(229, 'cap. neyyan', 32),
(230, 'new era', 32),
(231, '920', 32),
(232, 'women hat', 34),
(233, 'sun hat', 34),
(234, 'wide brim', 34),
(291, 'socks', 44),
(292, 'long neck socks', 44),
(293, 'stoking', 44),
(294, 'sock', 44),
(300, 'socks', 46),
(301, 'sports socks', 46),
(302, 'Trench Coat', 47),
(303, 'Coat', 47),
(304, 'Hybrid Coat', 48),
(305, 'coat', 48),
(306, 'men', 48),
(307, 'Crew Neck Cardigan', 49),
(308, 'Cardigan', 49),
(309, 'V Neck', 50),
(310, 'V Neck Cardigan', 50),
(311, 'hoodie', 50),
(316, 'Sweat shorts', 52),
(317, 'shorts', 52),
(318, 'casual shorts', 52),
(322, 'fit shorts', 53),
(323, 'women shorts', 53),
(324, 'casual shorts', 53),
(325, 'Button shorts', 51),
(326, 'formal shorts', 51),
(327, 'shorts', 51),
(328, 'pants', 51),
(333, 'SLIM CUT', 56),
(400, 'Side Wide Pant', 65),
(401, 'Straight Loose Pants', 66),
(402, 'Pants', 66),
(403, 'Loose Pants', 66),
(404, 'Jogger Long Men', 67),
(405, 'Jogger Long', 67),
(406, 'Jogger', 67),
(407, 'Ultra Stretch Leggings Pants', 68),
(408, 'Leggings Pants', 68),
(409, 'Leggings', 68),
(410, 'Long Sleeve Pajamas', 69),
(411, 'Pajamas', 69),
(412, 'baby pajamas', 70),
(413, 'Couple Silk Pajamas Set', 71),
(414, 'Silk Pajamas', 71),
(415, 'Couple Pajamas', 71),
(416, 'Gym short', 72),
(417, 'Quick Dry Gym Short', 72),
(418, 'Scarves', 73),
(419, 'Polyseter Scarves', 73),
(420, 'Slik Scarves', 74),
(421, 'Scarves', 74),
(422, 'Belting-Print Silk Scarves', 74),
(425, 'Pile-lined gloves', 76),
(426, 'Fingerless Gloves', 75),
(427, 'Gloves', 75),
(428, 'pants', 65),
(429, 'Wide pants', 65),
(430, 'skinny fit', 54),
(431, 'jeans', 54);

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

DROP TABLE IF EXISTS `product_variant`;
CREATE TABLE `product_variant` (
  `id` int(11) NOT NULL,
  `colour` varchar(50) NOT NULL,
  `size` varchar(30) NOT NULL,
  `stock` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variant`
--

INSERT INTO `product_variant` (`id`, `colour`, `size`, `stock`, `product_id`) VALUES
(4, 'Black', 'M', 98, 1),
(5, 'Black', 'L', 0, 1),
(7, 'Black', 'S', 0, 1),
(11, 'Oxford Gray', 'S', 23, 13),
(12, 'Oxford Gray', 'M', 30, 13),
(13, 'Oxford Gray', 'L', 30, 13),
(14, 'Charcoal Heather', 'S', 30, 13),
(15, 'Charcoal Heather', 'M', 30, 13),
(16, 'Charcoal Heather', 'L', 30, 13),
(17, 'Purple', 'S', 30, 13),
(18, 'Purple', 'M', 30, 13),
(19, 'Purple', 'L', 30, 13),
(20, 'Denim Blue', 'S', 30, 13),
(21, 'Denim Blue', 'M', 30, 13),
(22, 'Denim Blue', 'L', 30, 13),
(23, 'Pale Pink', 'S', 30, 13),
(24, 'Pale Pink', 'M', 30, 13),
(25, 'Pale Pink', 'L', 30, 13),
(28, 'Dark Green', 'XS', 9, 21),
(29, 'Dark Green', 'S', 21, 21),
(30, 'Dark Green', 'M', 30, 21),
(31, 'Dark Green', 'L', 12, 21),
(32, 'Dark Green', 'XL', 15, 21),
(33, 'Dark Green', 'XXL', 9, 21),
(34, 'Navy Blue', 'S', 30, 22),
(35, 'Navy Blue', 'M', 30, 22),
(36, 'Navy Blue', 'L', 30, 22),
(37, 'Navy Blue', 'XL', 30, 22),
(38, 'Orange', 'S', 12, 23),
(39, 'Orange', 'M', 3, 23),
(40, 'Orange', 'L', 0, 23),
(41, 'White', 'S', 30, 24),
(42, 'White', 'M', 30, 24),
(43, 'White', 'L', 30, 24),
(44, 'Black', 'S', 30, 24),
(45, 'Black', 'M', 30, 24),
(46, 'Black', 'L', 30, 24),
(47, 'Oatmeal Marle', 'S', 6, 24),
(48, 'Oatmeal Marle', 'M', 28, 24),
(49, 'Oatmeal Marle', 'L', 30, 24),
(50, 'Black', 'XS', 30, 25),
(51, 'Black', 'S', 28, 25),
(52, 'Black', 'M', 30, 25),
(53, 'Black', 'L', 30, 25),
(54, 'Black', 'XL', 30, 25),
(55, 'Black', 'S', 12, 26),
(56, 'Black', 'M', 3, 26),
(57, 'Black', 'L', 1, 26),
(58, 'Grey Marl', 'S', 1, 26),
(59, 'Grey Marl', 'M', 3, 26),
(60, 'Grey Marl', 'L', 23, 26),
(61, 'Light Beige', 'S', 11, 26),
(62, 'Light Beige', 'M', 3, 26),
(63, 'Light Beige', 'L', 3, 26),
(64, 'Graphite Grey', 'S', 600, 27),
(65, 'Graphite Grey', 'M', 300, 27),
(66, 'Graphite Grey', 'L', 120, 27),
(67, 'Lavender Grey', 'S', 120, 27),
(68, 'Lavender Grey', 'M', 330, 27),
(69, 'Lavender Grey', 'L', 110, 27),
(70, 'Pool', 'XXS', 1, 28),
(71, 'Pool', 'XS', 2, 28),
(72, 'Pool', 'S', 7, 28),
(73, 'Pool', 'M', 8, 28),
(75, 'Pool', 'L', 12, 28),
(76, 'Pool', 'XL', 1, 28),
(77, 'Pool', 'XXL', 7, 28),
(78, 'Gray', 'S', 1, 29),
(79, 'Gray', 'M', 2, 29),
(80, 'Gray', 'L', 8, 29),
(81, 'Gray', 'XL', 12, 29),
(82, 'Army Green', 'S', 1, 30),
(83, 'Army Green', 'M', 2, 30),
(84, 'Army Green', 'L', 10, 30),
(85, 'Black', 'M', 2, 31),
(86, 'Black', 'L', 2, 31),
(87, 'Black', 'M', 1, 32),
(88, 'Pitch White', 'M', 95, 34),
(141, 'White', '25-27cm', 100, 44),
(142, 'Black', '25-27cm', 98, 44),
(156, 'Natural', 'S', 100, 47),
(157, 'Natural', 'M', 99, 47),
(158, 'Natural', 'L', 100, 47),
(159, 'Black', 'S', 100, 47),
(160, 'Black', 'M', 100, 47),
(161, 'Black', 'L', 100, 47),
(162, 'Navy', 'S', 100, 48),
(163, 'Navy', 'M', 100, 48),
(165, 'Dark Green', 'S', 100, 48),
(166, 'Dark Green', 'M', 100, 48),
(167, 'Dark Green', 'L', 100, 48),
(168, 'Black', 'S', 70, 48),
(169, 'Black', 'L', 34, 48),
(170, 'Black', 'M', 96, 48),
(171, 'Dark Gray', 'S', 48, 48),
(172, 'Dark Gray', 'M', 1, 48),
(173, 'Dark Gray', 'L', 100, 48),
(174, 'Gray', 'S', 100, 49),
(175, 'Gray', 'M', 1000, 49),
(176, 'Gray', 'L', 100, 49),
(177, 'White', 'S', 200, 49),
(178, 'White', 'M', 100, 49),
(179, 'White', 'L', 100, 49),
(180, 'Beige', 'S', 100, 50),
(181, 'Beige', 'M', 100, 50),
(182, 'Beige', 'L', 100, 50),
(183, 'Black', 'S', 100, 50),
(184, 'Black', 'M', 100, 50),
(185, 'Black', 'L', 100, 50),
(186, 'Pink', 'S', 100, 50),
(187, 'Pink', 'M', 100, 50),
(188, 'Pink', 'L', 100, 50),
(189, 'Army Green', 'S', 10, 51),
(190, 'Army Green', 'M', 100, 51),
(191, 'Army Green', 'L', 100, 51),
(192, 'Sienna', 'S', 34, 52),
(193, 'Sienna', 'M', 87, 52),
(194, 'Sienna', 'L', 100, 52),
(195, 'Beige', 'S', 70, 53),
(196, 'Beige', 'M', 40, 53),
(197, 'Beige', 'L', 80, 53),
(198, 'Light Blue', 'S', 80, 54),
(199, 'Light Blue', 'M', 77, 54),
(200, 'Light Blue', 'L', 23, 54),
(201, 'Dark Blue', 'S', 90, 54),
(202, 'Dark Blue', 'M', 34, 54),
(203, 'Dark Blue', 'L', 78, 54),
(207, 'Blue', 'S', 100, 56),
(208, 'Blue', 'M', 100, 56),
(209, 'Blue', 'L', 100, 56),
(342, 'Blue', 'S', 15, 65),
(343, 'Blue', 'M', 50, 65),
(344, 'Blue', 'L', 90, 65),
(345, 'Blue', 'XL', 0, 65),
(346, 'Black', 'S', 80, 65),
(347, 'Black', 'M', 79, 65),
(348, 'Black', 'L', 120, 65),
(349, 'Black', 'XL', 50, 65),
(350, 'Gray', 'S', 50, 65),
(351, 'Gray', 'M', 60, 65),
(352, 'Gray', 'L', 40, 65),
(353, 'Gray', 'XL', 0, 65),
(354, 'Khaki', 'S', 20, 65),
(355, 'Khaki', 'M', 58, 65),
(356, 'Khaki', 'L', 22, 65),
(357, 'Khaki', 'XL', 31, 65),
(358, 'Blue', 'S', 10, 66),
(359, 'Blue', 'M', 1, 66),
(360, 'Blue', 'L', 3, 66),
(361, 'Blue', 'XL', 90, 66),
(362, 'Black', 'S', 80, 66),
(363, 'Black', 'M', 60, 66),
(364, 'Black', 'L', 70, 66),
(365, 'Black', 'XL', 50, 66),
(366, 'Gray', 'S', 0, 66),
(367, 'Gray', 'M', 100, 66),
(368, 'Gray', 'L', 80, 66),
(369, 'Gray', 'XL', 10, 66),
(370, 'Black', 'S', 10, 67),
(371, 'Black', 'M', 99, 67),
(372, 'Black', 'L', 50, 67),
(373, 'Black', 'XL', 40, 67),
(374, 'Olive', 'S', 10, 68),
(375, 'Olive', 'M', 50, 68),
(376, 'Olive', 'L', 90, 68),
(377, 'Olive', 'XL', 10, 68),
(378, 'White', 'S', 20, 68),
(379, 'White', 'M', 80, 68),
(380, 'White', 'L', 90, 68),
(381, 'White', 'XL', 30, 68),
(382, 'Black', 'S', 23, 68),
(383, 'Black', 'M', 48, 68),
(384, 'Black', 'L', 76, 68),
(385, 'Black', 'XL', 32, 68),
(386, 'Dark Gray', 'S', 34, 68),
(387, 'Dark Gray', 'M', 26, 68),
(388, 'Dark Gray', 'L', 49, 68),
(389, 'Dark Gray', 'XL', 59, 68),
(390, 'Navy', 'S', 40, 69),
(391, 'Navy', 'M', 107, 69),
(392, 'Navy', 'L', 52, 69),
(393, 'Navy', 'XL', 17, 69),
(394, 'Pink', 'S', 3, 69),
(395, 'Pink', 'M', 18, 69),
(396, 'Pink', 'L', 0, 69),
(397, 'Pink', 'XL', 17, 69),
(398, 'Yellow', '80cm', 44, 70),
(399, 'Yellow', '90cm', 77, 70),
(400, 'Yellow', '110cm', 73, 70),
(401, 'Yellow', '120cm', 82, 70),
(402, 'Yellow', '100cm', 49, 70),
(403, 'Blue', '80cm', 72, 70),
(404, 'Blue', '90cm', 60, 70),
(405, 'Blue', '100cm', 70, 70),
(406, 'Blue', '110cm', 69, 70),
(407, 'Blue', '120cm', 60, 70),
(408, 'Black', 'S', 58, 71),
(409, 'Black', 'M', 91, 71),
(410, 'Black', 'L', 99, 71),
(411, 'Black', 'XL', 84, 71),
(412, 'White', 'S', 71, 71),
(413, 'White', 'M', 68, 71),
(414, 'White', 'L', 74, 71),
(415, 'White', 'XL', 99, 71),
(416, 'Black', 'M', 75, 72),
(417, 'Black', 'S', 70, 72),
(418, 'Black', 'L', 79, 72),
(419, 'Black', 'XL', 64, 72),
(420, 'Navy', 'S', 24, 72),
(421, 'Navy', 'M', 53, 72),
(422, 'Navy', 'L', 74, 72),
(423, 'Navy', 'XL', 72, 72),
(424, 'Blue', 'S', 56, 72),
(425, 'Blue', 'M', 40, 72),
(426, 'Blue', 'L', 35, 72),
(427, 'Blue', 'XL', 78, 72),
(428, 'Blue', 'Free Size', 164, 73),
(429, 'Black', 'Free Size', 324, 73),
(430, 'Red', 'Free Size', 150, 73),
(431, 'Belting-Print', 'Free Size', 173, 74),
(432, 'Black', 'Free Size', 66, 75),
(433, 'Dark Gary', 'Free Size', 61, 75),
(434, 'Black', 'Free Size', 88, 76),
(435, 'Black', '25-28cm', 100, 46),
(436, 'Black', '22-25cm', 100, 46),
(437, 'White', '25-28cm', 100, 46),
(438, 'White', '22-25cm', 100, 46),
(439, 'Olive', 'S', 100, 47),
(440, 'Olive', 'M', 100, 47),
(441, 'Olive', 'L', 100, 47),
(442, 'Navy', 'L', 100, 48);

-- --------------------------------------------------------

--
-- Table structure for table `reset_password`
--

DROP TABLE IF EXISTS `reset_password`;
CREATE TABLE `reset_password` (
  `token` char(150) NOT NULL,
  `expire` datetime NOT NULL,
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
  `account_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `content` varchar(200) NOT NULL,
  `rating` int(11) NOT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`account_id`, `product_id`, `content`, `rating`, `creation_time`) VALUES
(19, 13, 'Quite comfortable for me to wear', 4, '2025-04-27 15:11:29'),
(19, 27, 'Good good good', 5, '2025-04-27 21:50:31'),
(19, 71, 'My attempts to use this resulted in a series of increasingly frustrated noises that startled my neighbors and possibly attracted local wildlife.', 5, '2025-04-27 21:19:16'),
(32, 21, 'It\'s very hot and I got all itchy when wearing it!', 2, '2025-04-27 15:15:46'),
(35, 23, 'The color was described as \'sunset orange.\' It\'s more like \'the questionable stain on a public bus seat\' orange.', 1, '2025-04-27 21:16:51'),
(35, 25, 'The \'stylish design\' appears to have been inspired by a potato. A lumpy, brown potato', 5, '2025-04-27 21:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `id` char(30) NOT NULL,
  `token` char(150) DEFAULT NULL,
  `otp` char(6) NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `expire` datetime NOT NULL,
  `address` varchar(200) NOT NULL,
  `device_os` varchar(100) NOT NULL,
  `device_type` varchar(100) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `last_login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`id`, `token`, `otp`, `is_verified`, `expire`, `address`, `device_os`, `device_type`, `browser`, `last_login_time`, `account_id`) VALUES
('363c373a75e64acb7fb603b6ac0634', '0c2d14c3cc5a99725268fa6bdd427848fb7ee2eb053a5eb8a25cd7502c48bdf1e272821a3069918c0fd61982b78ba9d595cb769280091a8d2098895d40ad821969dae81f184ff0a7e784c4', '498542', 1, '2025-06-01 08:02:32', 'Kuala Lumpur, Kuala Lumpur, Malaysia', 'Windows', 'Computer', 'Microsoft Edge', '2025-05-02 08:02:09', 36),
('44e787f3a9bb7889678960d4d3a61b', '3d2e828b0558a003249c18cf94587f6754e26009cb6dee9dc33e0c67f8d64e3f373cf27a4528d4e4ebde77896fb099d8aa2827de883332e9e7f5142dccc798c10601d672e10d494f155ae3', '926252', 1, '2025-06-01 08:00:58', 'Kuala Lumpur, Kuala Lumpur, Malaysia', 'Windows', 'Computer', 'Google Chrome', '2025-05-02 08:00:45', 1),
('a36555dea80df706671b384a71e25d', '20b46f2e3eb08621aaa0b1f6537c35b7370b2514f06fb4a0e03f0d8c2b9fd2136738998f5ac43d872a04c623f7a301f56009a97eb1d97b12277842a633e53fc71549b2d58d38b48ea46840', '861589', 1, '2025-06-01 08:20:40', 'Local Host', 'Windows', 'Computer', 'Microsoft Edge', '2025-05-02 08:20:28', 36);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE `transaction` (
  `id` char(30) NOT NULL,
  `value` double(15,2) NOT NULL,
  `detail` varchar(100) DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  `order_id` char(30) DEFAULT NULL,
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `value`, `detail`, `creation_time`, `order_id`, `account_id`) VALUES
('05eb95c240d74e2705c5fe1982ec45', -111.00, 'Order ID: 45f73dd64432f9ed0e50111a3a41b7', '2025-04-27 15:51:35', '45f73dd64432f9ed0e50111a3a41b7', 35),
('151f651744dba611484ae96c5b887b', -169.90, 'Order ID: 2b2f827bdeb7449742ea9d32b93c5a', '2025-04-27 21:02:15', '2b2f827bdeb7449742ea9d32b93c5a', 35),
('16717f7bed259dc877b2b3b0e9619e', -2571.80, 'Order ID: ad19b9737e8ecd242c6e5ac9ee20c8', '2025-04-27 21:54:53', 'ad19b9737e8ecd242c6e5ac9ee20c8', 19),
('30a6cbbd4df7111450b9f106eb5e9c', 10.50, 'Top Up: 30a6cbbd4df7111450b9f106eb5e9c', '2025-04-27 20:31:46', NULL, 19),
('3ebf2fd1e69f35f187934bd72b1b50', -918.00, 'Order ID: faa9c6186e64ff6fb4fc03302af843', '2025-04-27 21:53:08', 'faa9c6186e64ff6fb4fc03302af843', 19),
('3f198da068bacb4c2dcfb0bb15af73', -6299.00, 'Order ID: e50e318da931f01580c09f8b4b08e3', '2025-04-27 21:30:43', 'e50e318da931f01580c09f8b4b08e3', 19),
('42e61b65bb96a0e0eb29f164decf8b', -123.51, 'Order ID: 79d36fea6cefdd2f1f413d33ffe3ab', '2025-04-27 21:42:27', '79d36fea6cefdd2f1f413d33ffe3ab', 19),
('5338a0ec86ddeb5a6002cf2fd11117', -215.70, 'Order ID: 7fa7bd8aa9c36755290793578c4069', '2025-04-27 21:27:34', '7fa7bd8aa9c36755290793578c4069', 19),
('6ad0488fe1bedc8d27228176b04753', -4089.90, 'Order ID: 6ab7dc33d97225e3fad8545ab17655', '2025-04-27 21:54:02', '6ab7dc33d97225e3fad8545ab17655', 19),
('6c388a273232855704e17f18bae900', 111.00, 'Order ID: 45f73dd64432f9ed0e50111a3a41b7 Refunded', '2025-04-27 15:52:49', '45f73dd64432f9ed0e50111a3a41b7', 35),
('6f0592adc24d25c7c11d2579023b49', 92.01, 'Top Up: 6f0592adc24d25c7c11d2579023b49', '2025-04-27 20:41:59', NULL, 35),
('78963326b057571d98adae5d0ce66f', -165.00, 'Order ID: 4d53984c23c085784245e566d0dcfd', '2025-04-27 16:09:37', '4d53984c23c085784245e566d0dcfd', 35),
('7f845650a642045dd2c1a78b0887e7', -1417.11, 'Order ID: 6705137575bb582ea04a71f459e92c', '2025-04-27 21:25:25', '6705137575bb582ea04a71f459e92c', 19),
('8ce0f96319ce02f6e5f13671f3874e', 334.21, 'Top Up: 8ce0f96319ce02f6e5f13671f3874e', '2025-04-27 21:00:15', NULL, 35),
('96e097a821f806cbcfe97b7151cc22', 10.01, 'Top Up: 96e097a821f806cbcfe97b7151cc22', '2025-04-27 20:31:27', NULL, 19),
('a6ce7d30651ca3f3592d9bb8babb06', 164.00, 'Order ID: fcd5e0f994190b8db8e24a467de567 Refunded', '2025-04-27 15:46:08', 'fcd5e0f994190b8db8e24a467de567', 35),
('a73a79f76fa832e7073b3f528a2034', 10.00, 'Top Up: a73a79f76fa832e7073b3f528a2034', '2025-04-27 16:09:28', NULL, 35),
('ad0c52f17055302769a134ab0c84df', -30128.00, 'Order ID: 748bab48e7af86f1e2fa3deea6eaae', '2025-04-27 21:28:25', '748bab48e7af86f1e2fa3deea6eaae', 19),
('b6bbd08c3b0424a64b51274527f19c', 30000.00, 'Top Up: b6bbd08c3b0424a64b51274527f19c', '2025-04-27 20:30:08', NULL, 19),
('b93b77e06c2287a23b31a5ba8f0198', -10695.00, 'Order ID: 8e3f5fd5b4512638fb2ecd67ff1d5e', '2025-04-27 21:26:38', '8e3f5fd5b4512638fb2ecd67ff1d5e', 19),
('c11fc53173a6e42de7cdff77924873', -1181.98, 'Order ID: 68672442b0fcebd02cb90157c15474', '2025-04-27 20:39:31', '68672442b0fcebd02cb90157c15474', 19),
('caefec40072d7e7f4921d5144f4050', -1655.95, 'Order ID: 82d4c7171b05eaeaf23e0ec7e5e24b', '2025-04-27 21:41:26', '82d4c7171b05eaeaf23e0ec7e5e24b', 19),
('cb5254d711b8fb5504fa16ebc2796f', 12.45, 'Top Up: cb5254d711b8fb5504fa16ebc2796f', '2025-04-27 20:41:39', NULL, 35),
('d5f9b8d4b26f6e06c49125747d17a9', 58.00, 'Order ID: 297bf54afec638c31b463386728965 Refunded', '2025-05-02 08:16:30', '297bf54afec638c31b463386728965', 36),
('df0f888843bbe7aec8130d1e046b18', 12.99, 'Top Up: df0f888843bbe7aec8130d1e046b18', '2025-04-27 20:32:07', NULL, 19),
('dfa66816849d3e30468bd560ea96d8', 50000.00, 'Top Up: dfa66816849d3e30468bd560ea96d8', '2025-04-27 20:30:30', NULL, 19),
('e32cee3a5c6a15de5b58dcef1436ee', -8737.00, 'Order ID: b6629aafba339fb06cbedf89ecb5e1', '2025-04-27 21:29:26', 'b6629aafba339fb06cbedf89ecb5e1', 19),
('f1ba1cc210903290814be70579346e', -107.90, 'Order ID: 34705a2c9ed0fe88893f402c766b49', '2025-04-27 21:43:16', '34705a2c9ed0fe88893f402c766b49', 19),
('f618bc930b5c7e6bd54eb08d574932', -4628.00, 'Order ID: cdeacdde72a6acc89484e8f27ca549', '2025-04-27 21:51:46', 'cdeacdde72a6acc89484e8f27ca549', 19),
('f8bae7e35f9477de9952f9b29022da', -193.00, 'Order ID: a8b5480ef05bc4a1529f3667cd486e', '2025-04-27 21:43:59', 'a8b5480ef05bc4a1529f3667cd486e', 19);

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

DROP TABLE IF EXISTS `voucher`;
CREATE TABLE `voucher` (
  `id` char(13) NOT NULL,
  `expiry_date` date NOT NULL,
  `is_used` tinyint(1) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `account_email` varchar(80) DEFAULT NULL,
  `voucher_template_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id`, `expiry_date`, `is_used`, `account_id`, `account_email`, `voucher_template_id`) VALUES
('334b69641d6d5', '2025-05-31', 0, 36, NULL, 25),
('a57cad66ec133', '2025-05-04', 0, 32, NULL, 12),
('aada1f4dc9e34', '2025-05-04', 0, 35, NULL, 12),
('e8ac0efc1f7dc', '2025-05-09', 1, 36, NULL, 12),
('ee4129d76bd45', '2025-05-11', 0, 35, NULL, 13);

-- --------------------------------------------------------

--
-- Table structure for table `voucher_template`
--

DROP TABLE IF EXISTS `voucher_template`;
CREATE TABLE `voucher_template` (
  `id` int(11) NOT NULL,
  `token` char(150) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `valid_days` int(11) DEFAULT NULL,
  `value` int(11) NOT NULL,
  `claim_limit` int(11) DEFAULT NULL,
  `total_claimed` int(11) DEFAULT NULL,
  `for_signup` tinyint(1) NOT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher_template`
--

INSERT INTO `voucher_template` (`id`, `token`, `name`, `expiry_date`, `valid_days`, `value`, `claim_limit`, `total_claimed`, `for_signup`, `creation_time`) VALUES
(12, NULL, 'Free Sign Up Voucher', NULL, 7, 10, NULL, 3, 1, '2025-04-26 00:08:48'),
(13, '4802842189fae93bc4f9fbdb1819a37fa6e32d8553298f881f3b822b40eef074a18eb15a4b9cd53227fd442faa0e65ab9e6fb24931c2186e3d803230c334145e43e2094d8512e218713124', 'Hari Raya 2025', '2025-05-30', 14, 50, NULL, 1, 0, '2025-04-27 14:57:58'),
(14, '6da9aedfba0fce93102ca0caf5615ba1f9c601c59ed231b716f2f09dcce27e6362425d79c7a1fc6384c061e11c804eafadcdbdd50e890ee7c9412e567e05e169cdee1e5e3f069be21b125b', 'Lunar New Year 2025', '2025-03-30', 7, 100, 100, 0, 0, '2025-04-27 14:58:27'),
(15, '4beaba9f94fb62562c3533feaca9382cfd1f78b8511b4e42603e543ded24b7090f8ef02d556b0519b74362edba20ee0de340be4069a16d9140bd770c8766c28f7ab1197cfd548f3b69019a', '🎉 Superme\'s Anniversary Flash Sale! 🎉', '2025-03-20', NULL, 100, 1000, 0, 0, '2025-02-01 22:19:49'),
(16, '3484577bb1135c374b07475ea96863bc431632ef5ef2c41b350bf08558a36b8112fe58a9a88fce9d669f18bd943e04fbdd5745e52d0c73c5856fa38eb90b2a592a5b091c4f6c7193bb740e', '✨ New Arrivals Just Dropped! ✨', '2025-01-03', NULL, 10, 100, 0, 0, '2025-01-01 22:21:23'),
(17, '792c9709016ea52f7130a616c4b52bf74dba5cd4c8225f70516b43ac96b13aaab9f97e7b8bb7876de1563cf2543b441b4aefe2032816253251b4934769ae8ce14613f20c40faaa47bd64fd', '🎁 Treat Yourself Tuesday! 🎁', '2023-07-27', NULL, 20, 200, 0, 0, '2023-06-22 22:22:13'),
(18, 'cf1596099a63a389e5d8cd214a73f2b7106e9ea9567c6eeb686e046aa8f97462cf91e721d9df51319ec7bdd5f104e8f6243c83b9bd5e990da089d404224698e555e476090e8b66511826bd', '👖 Denim Delight Discount! 👖', '2021-10-14', NULL, 50, 100, 0, 0, '2021-09-14 22:22:52'),
(19, '86a2ec4a314dfeae662367d7dfc5421436c8e55a81066447a3be7c87e80d3abee1d96a50808174a9ee5efbb58263e5347666383f2487b55c778c20a6580a539ce80fff920a27c3c9053548', '🔥 Weekend Wardrobe Refresh! 🔥', '2023-03-17', NULL, 10, 60, 0, 0, '2023-02-25 22:23:44'),
(20, '11762c6da6b33112f9cbe77427951901bb392dc269aedb8de663a86802cadc052aa500f8d616c419d9abec2b22d4f5e35317024b55ea183788dcb392babfb62946de38ddfcabf92ad8b0b7', '✨ Style Spotlight: Dresses! ✨', '2024-03-27', NULL, 50, 70, 0, 0, '2024-02-27 22:24:07'),
(21, '61da8470c5c56e9e6cfa99df6e576e5f7790e8a9da413506b325c4f9a8ec6e2394ccdd15f14ff78e6ea4633c8e511f3c48da8080d17fb31eaed9642833668a0e2a5307182ad5b53ccd3c7f', '🧥 Cozy Up with Superme Outerwear! 🧥', '2022-08-08', NULL, 20, 66, 0, 0, '2022-07-20 22:24:25'),
(22, '5c864674719a17530f193d0bb477eb683912dd8ee01b068870551b50fb2514ff1fb399385a706e31de5a2b2cb6a05f0e8e1c3ccdc7d8e187c139b6966bae0da7521fa27efd17d4674a06b9', '💖 Superme Loves Loyalty! 💖', '2019-03-27', NULL, 20, 100, 0, 0, '2019-02-27 22:25:00'),
(23, '3bc46a86a7fbc27ea2e123dc2f42dd5396588a1d60fa6ae9e0b95e0ebb31d15a7ef51731c25da452abc776c4d1c0c19b13880a6bdf95f75dd17744817ac846e2a528d8559d911a47805745', '🎁 Surprise Savings Inside! 🎁', '2021-06-16', NULL, 20, 200, 0, 0, '2021-06-01 22:25:39'),
(24, '1a8cc14caa1550cae2d31ea850fb09eb6d142d612605e8a8ab6eddf991e04933f3cffe10e6c7c340f71ba4609673f2d78de02e1caaf2d0ae2107e97d180d5b631acf726123e2e0ce9181f6', '✨ Last Chance: Spring Styles Discount! ✨', '2022-07-27', NULL, 50, 200, 0, 0, '2022-06-25 22:26:15'),
(25, '6493a824b11d08ff055384e2c3ac43622009a9f3b86c21b5335a213af284a5be07210d17c6d119a5079f4425e557df47cd2ff5508c6005dcc6dc535f701c14ff3aa902f354b6f3a7f2afe2', 'ken 100', '2025-05-31', NULL, 100, 5, 1, 0, '2025-05-02 08:08:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_type_id` (`account_type_id`);

--
-- Indexes for table `account_type`
--
ALTER TABLE `account_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`product_variant_id`,`account_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `change_email`
--
ALTER TABLE `change_email`
  ADD PRIMARY KEY (`token`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `delete_account`
--
ALTER TABLE `delete_account`
  ADD PRIMARY KEY (`token`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `favourite`
--
ALTER TABLE `favourite`
  ADD PRIMARY KEY (`account_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `like_review`
--
ALTER TABLE `like_review`
  ADD PRIMARY KEY (`account_id`,`product_id`,`reviewer_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notice`
--
ALTER TABLE `notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_id`,`product_variant_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `poll_option`
--
ALTER TABLE `poll_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notice_id` (`notice_id`);

--
-- Indexes for table `poll_vote`
--
ALTER TABLE `poll_vote`
  ADD PRIMARY KEY (`account_id`,`notice_id`),
  ADD KEY `notice_id` (`notice_id`),
  ADD KEY `poll_option_id` (`poll_option_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_keyword`
--
ALTER TABLE `product_keyword`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reset_password`
--
ALTER TABLE `reset_password`
  ADD PRIMARY KEY (`token`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`account_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `voucher_template_id` (`voucher_template_id`);

--
-- Indexes for table `voucher_template`
--
ALTER TABLE `voucher_template`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `notice`
--
ALTER TABLE `notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `poll_option`
--
ALTER TABLE `poll_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `product_keyword`
--
ALTER TABLE `product_keyword`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=436;

--
-- AUTO_INCREMENT for table `product_variant`
--
ALTER TABLE `product_variant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=445;

--
-- AUTO_INCREMENT for table `voucher_template`
--
ALTER TABLE `voucher_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_1` FOREIGN KEY (`account_type_id`) REFERENCES `account_type` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variant` (`id`);

--
-- Constraints for table `change_email`
--
ALTER TABLE `change_email`
  ADD CONSTRAINT `change_email_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `delete_account`
--
ALTER TABLE `delete_account`
  ADD CONSTRAINT `delete_account_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `favourite`
--
ALTER TABLE `favourite`
  ADD CONSTRAINT `favourite_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `favourite_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `like_review`
--
ALTER TABLE `like_review`
  ADD CONSTRAINT `like_review_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `like_review_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `like_review_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `order_item_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `poll_option`
--
ALTER TABLE `poll_option`
  ADD CONSTRAINT `poll_option_ibfk_1` FOREIGN KEY (`notice_id`) REFERENCES `notice` (`id`);

--
-- Constraints for table `poll_vote`
--
ALTER TABLE `poll_vote`
  ADD CONSTRAINT `poll_vote_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `poll_vote_ibfk_2` FOREIGN KEY (`notice_id`) REFERENCES `notice` (`id`),
  ADD CONSTRAINT `poll_vote_ibfk_3` FOREIGN KEY (`poll_option_id`) REFERENCES `poll_option` (`id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `product_keyword`
--
ALTER TABLE `product_keyword`
  ADD CONSTRAINT `product_keyword_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD CONSTRAINT `product_variant_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `reset_password`
--
ALTER TABLE `reset_password`
  ADD CONSTRAINT `reset_password_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `voucher`
--
ALTER TABLE `voucher`
  ADD CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `voucher_ibfk_2` FOREIGN KEY (`voucher_template_id`) REFERENCES `voucher_template` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
