-- MySQL SQL Dump of Alfarhan Trading E-commerce Database
-- Generated programmatically

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `product_variations`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `user_coupons`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `order_items`;

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_17_000001_create_products_table', 1),
(5, '2026_06_17_000002_create_product_variations_table', 1),
(6, '2026_06_17_000003_create_coupons_table', 1),
(7, '2026_06_17_000004_create_user_coupons_table', 1),
(8, '2026_06_17_000005_create_cart_items_table', 1),
(9, '2026_06_17_000006_create_orders_table', 1),
(10, '2026_06_17_000007_create_order_items_table', 1);

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@ecomm.com', NULL, '$2y$12$8NCY/OUMiMU1qqykmpyJu.pa0AOv6IM0cm1lSIOOYQqmBCi2YdRfG', 'admin', NULL, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(2, 'Outdoor Sales Agent', 'sales@ecomm.com', NULL, '$2y$12$DzUD2BqWzNNafMO2Inra3elQGFpSylTh8wCafMyndQ1jBVkPjevaq', 'outdoor_sales', NULL, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(3, 'Inventory Purchaser', 'purchaser@ecomm.com', NULL, '$2y$12$SgWJOxuDQdgz3fQqYH9XDOuvEkfFeELUyHFeX/RZadxtzwL7OmWKK', 'purchaser', NULL, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(4, 'Storekeeper Keeper', 'storekeeper@ecomm.com', NULL, '$2y$12$JVYOJD45LVH.IJFbkpTYf.C.CTy48LjvmA2BhqT6LNYtDnKe662Ii', 'storekeeper', NULL, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(5, 'John Customer', 'customer@ecomm.com', NULL, '$2y$12$WJZEkmirr3WRc9Vpu3X/peDYbhbmThPv3WfkNAB9CckZPH/QDB/ue', 'customer', NULL, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(6, 'Irfan Zihni', 'irfanzabidi12@gmail.com', NULL, '$2y$12$Nrom1CIQaDVJ/bCBUk1paua.yMVZR0WRai2nzqdwn8PS36NDbofdO', 'customer', NULL, '2026-06-17 04:06:43', '2026-06-17 04:06:43');

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sessions`
-- --------------------------------------------------------
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sessions`
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('RGWOEiBGWucBiO8oPQOG4mUn5dQV3u8PHpw6wOWp', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVnBjMzVqalF2alROZlZVQU5OSFVwYW1wQktKY0VGcWRqMGMwNlpHSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWN0LzIiO3M6NToicm91dGUiO3M6OToic2hvcC5zaG93Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1781675692),
('FR5EO3dfCj3XBfGWTQDP2L6ku2C8ZXXDIBXIAzIG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieXJobUp5cTZSekJieGZ0eTVBTHBHcjlRNVhjZ0lYdG5tY0Jrams1aiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo5OiJzaG9wLmhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1781746237),
('w9ViK2bLIpWXpiQWr1zbnzzpzQ4MomNOa7SnJCJ2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibkhnSGxDNzlocW1OZmt4WDNZWVZpR04xMzlOellCV2Ztc0J2SDhCTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo5OiJzaG9wLmhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjI2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvY2FydCI7fX0=', 1781748788),
('Tp9GiUyRHrKyD3vKu1POdKDFEzW8U9NtvfCLfUEV', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOElkT2JFa2VLcGJSMFJLazBNQlJ4NFFHOHFCdVFJeXBZYndodHd2UyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo5OiJzaG9wLmhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1782206781);

-- --------------------------------------------------------
-- Table structure for table `cache`
-- --------------------------------------------------------
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cache_locks`
-- --------------------------------------------------------
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `jobs`
-- --------------------------------------------------------
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `job_batches`
-- --------------------------------------------------------
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `failed_jobs`
-- --------------------------------------------------------
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `base_price` decimal(8,2) NOT NULL,
  `discount_price` decimal(8,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `products`
INSERT INTO `products` (`id`, `name`, `description`, `category`, `base_price`, `discount_price`, `stock`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Premium Ajwa Dates (Al-Madinah)', 'Soft, dry variety of date fruit from Saudi Arabia. Known for its dark color, rich texture, and intense flavor. Directly imported from Al-Madinah.', 'dates', 30, 25, 0, '/images/products/ajwa_dates.jpg', '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(2, 'Pure Organic Sidr Honey', 'Rare mono-floral honey made from the nectar of Sidr trees. Extremely rich in nutrients and antioxidants, with a uniquely delicious, warm taste.', 'honey', 50, NULL, 0, '/images/products/sidr_honey.jpg', '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(3, 'Cold-Pressed Extra Virgin Olive Oil', 'Premium quality cold-pressed extra virgin olive oil. Rich in healthy monounsaturated fats and antioxidants. Perfect for salads, cooking, or raw consumption.', 'oil', 20, 16, 75, '/images/products/olive_oil.jpg', '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(4, 'Sun-Dried Organic Figs', 'Naturally sun-dried premium organic figs. Soft, sweet, chewy, and packed with fiber, potassium, and magnesium. Free from artificial preservatives.', 'dried_fruit', 15, NULL, 35, '/images/products/dried_figs.jpg', '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(5, 'Sweet Taif Pomegranates', 'Fresh, sweet, and juicy pomegranates sourced directly from the orchards of Taif. Celebrated for their vibrant ruby seeds and sweet flavor.', 'sunnah_fruit', 10, 8.5, 4, '/images/products/pomegranates.jpg', '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(6, 'Premium Black Seed Oil (Habbatussauda)', '100% pure cold-pressed oil from Nigella Sativa (black cumin seeds). Celebrated for its immune-supporting qualities and overall wellness benefits.', 'oil', 24, NULL, 0, '/images/products/black_seed_oil.jpg', '2026-06-17 03:50:29', '2026-06-17 03:50:29');

-- --------------------------------------------------------
-- Table structure for table `product_variations`
-- --------------------------------------------------------
CREATE TABLE `product_variations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `weight` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_variations_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_variations`
INSERT INTO `product_variations` (`id`, `product_id`, `name`, `value`, `price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 1, 'Size', '500g', 25, 45, '2026-06-17 03:50:29', '2026-06-17 05:33:20'),
(2, 1, 'Size', '1kg', 45, 30, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(3, 2, 'Weight', '250g', 25, 15, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(4, 2, 'Weight', '500g', 48, 20, '2026-06-17 03:50:29', '2026-06-17 03:50:29'),
(5, 2, 'Weight', '1kg', 85, 8, '2026-06-17 03:50:29', '2026-06-17 03:50:29');

-- --------------------------------------------------------
-- Table structure for table `coupons`
-- --------------------------------------------------------
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `discount_amount` decimal(8,2) NOT NULL,
  `discount_type` varchar(255) NOT NULL,
  `min_spend` decimal(8,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `coupons`
INSERT INTO `coupons` (`id`, `code`, `discount_amount`, `discount_type`, `min_spend`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME20', 20, 'percent', 30, 1, '2026-06-17 03:50:29', NOW()),
(2, 'SUNNAH20', 20, 'percent', 50, 1, '2026-06-17 03:50:29', '2026-06-17 03:50:29');

-- --------------------------------------------------------
-- Table structure for table `user_coupons`
-- --------------------------------------------------------
CREATE TABLE `user_coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `coupon_id` bigint(20) unsigned NOT NULL,
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_coupons_user_id_foreign` (`user_id`),
  KEY `user_coupons_coupon_id_foreign` (`coupon_id`),
  CONSTRAINT `user_coupons_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_coupons_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `user_coupons`
INSERT INTO `user_coupons` (`id`, `user_id`, `coupon_id`, `claimed_at`, `used_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-06-17 04:03:19', NULL, '2026-06-17 04:03:19', '2026-06-17 04:03:19');

-- --------------------------------------------------------
-- Table structure for table `cart_items`
-- --------------------------------------------------------
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variation_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_user_id_foreign` (`user_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_product_variation_id_foreign` (`product_variation_id`),
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variation_id_foreign` FOREIGN KEY (`product_variation_id`) REFERENCES `product_variations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `order_type` varchar(255) NOT NULL DEFAULT 'online',
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `total_amount` decimal(8,2) NOT NULL,
  `discount_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(8,2) NOT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_created_by_foreign` (`created_by`),
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `orders`
INSERT INTO `orders` (`id`, `user_id`, `order_type`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `total_amount`, `discount_amount`, `final_amount`, `coupon_code`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'online', 'Admin User', 'admin@ecomm.com', '01160891312', 'no.16, jln cemara 3, saujana utama 1, 47000 sungai buloh, selangor', 25, 0, 25, NULL, 'completed', NULL, '2026-06-17 04:04:29', '2026-06-17 05:35:31');

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variation_id` bigint(20) unsigned DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variation_id_foreign` (`product_variation_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_variation_id_foreign` FOREIGN KEY (`product_variation_id`) REFERENCES `product_variations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `order_items`
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variation_id`, `price`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 25, 1, '2026-06-17 04:04:29', '2026-06-17 04:04:29');

SET FOREIGN_KEY_CHECKS = 1;
