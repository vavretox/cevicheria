-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: cevicheria_pos
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `beverage_stock_entries`
--

DROP TABLE IF EXISTS `beverage_stock_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beverage_stock_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `movement_type` enum('entry','exit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entry',
  `entry_type` enum('unit','box') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `units_per_box` int unsigned DEFAULT NULL,
  `total_units` int unsigned NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `beverage_stock_entries_user_id_foreign` (`user_id`),
  KEY `beverage_stock_entries_product_id_created_at_index` (`product_id`,`created_at`),
  KEY `beverage_stock_entries_movement_type_created_at_index` (`movement_type`,`created_at`),
  CONSTRAINT `beverage_stock_entries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `beverage_stock_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beverage_stock_entries`
--

LOCK TABLES `beverage_stock_entries` WRITE;
/*!40000 ALTER TABLE `beverage_stock_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `beverage_stock_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_sessions`
--

DROP TABLE IF EXISTS `cash_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `opening_amount` decimal(10,2) NOT NULL,
  `opening_note` text COLLATE utf8mb4_unicode_ci,
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `expected_amount` decimal(10,2) DEFAULT NULL,
  `counted_amount` decimal(10,2) DEFAULT NULL,
  `difference_amount` decimal(10,2) DEFAULT NULL,
  `closing_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_sessions_user_id_status_index` (`user_id`,`status`),
  KEY `cash_sessions_opened_at_index` (`opened_at`),
  KEY `cash_sessions_closed_at_index` (`closed_at`),
  CONSTRAINT `cash_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_sessions`
--

LOCK TABLES `cash_sessions` WRITE;
/*!40000 ALTER TABLE `cash_sessions` DISABLE KEYS */;
INSERT INTO `cash_sessions` VALUES (1,2,'closed',190.00,'SE APERTURO CAJA','2026-04-02 17:29:21','2026-04-02 19:08:59',911.00,721.00,-190.00,NULL,'2026-04-02 17:29:21','2026-04-02 19:08:59'),(2,2,'open',20.00,NULL,'2026-04-02 19:31:17',NULL,NULL,NULL,NULL,NULL,'2026-04-02 19:31:17','2026-04-02 19:31:17');
/*!40000 ALTER TABLE `cash_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Ceviches','ceviches','Variedad de ceviches frescos',1,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(2,'Bebidas','bebidas','Bebidas frías y refrescantes',1,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(3,'Entradas','entradas','Entradas y aperitivos',1,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(4,'Platos de Fondo','platos_de_fondo','Platos principales',1,'2026-02-10 01:36:05','2026-02-10 01:36:05');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2024_01_01_000001_create_users_table',1),(2,'2024_01_01_000002_create_categories_table',1),(3,'2024_01_01_000003_create_products_table',1),(4,'2024_01_01_000004_create_orders_table',1),(5,'2024_01_01_000005_create_order_details_table',1),(6,'2026_02_10_000006_drop_tax_from_orders_table',2),(7,'2026_02_10_000007_add_revert_reason_to_orders_table',3),(8,'2026_02_10_000008_create_order_audits_table',4),(9,'2026_04_02_000009_create_tables_table',5),(10,'2026_04_02_000010_add_table_id_to_orders_table',5),(11,'2026_04_02_000011_add_phase2_fields_to_tables_table',6),(12,'2026_04_02_000012_create_cash_sessions_table',7),(13,'2026_04_02_000013_add_cash_session_id_to_orders_table',7),(14,'2026_04_02_000014_add_payment_fields_to_orders_table',8),(15,'2026_04_02_000015_add_daily_sequence_to_orders_table',9),(16,'2026_04_02_000016_add_service_type_to_order_details_table',10),(17,'2026_04_02_000017_add_service_mode_to_orders_table',11),(18,'2026_04_02_000018_add_split_payment_amounts_to_orders_table',12),(19,'2026_04_02_000019_create_beverage_stock_entries_table',13),(20,'2026_04_02_000020_add_movement_type_to_beverage_stock_entries_table',14),(21,'2026_04_06_000022_add_performance_indexes',15),(22,'2026_04_06_000021_add_codes_and_channels',16);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_audits`
--

DROP TABLE IF EXISTS `order_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_audits_user_id_foreign` (`user_id`),
  KEY `order_audits_order_id_created_at_index` (`order_id`,`created_at`),
  KEY `order_audits_action_created_at_index` (`action`,`created_at`),
  CONSTRAINT `order_audits_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_audits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_audits`
--

LOCK TABLES `order_audits` WRITE;
/*!40000 ALTER TABLE `order_audits` DISABLE KEYS */;
INSERT INTO `order_audits` VALUES (1,3,3,'created','{\"items\": [{\"name\": \"Ceviche Clásico\", \"notes\": \"SIN CEBOLLA\", \"price\": \"25\", \"quantity\": \"1\", \"product_id\": \"1\"}, {\"name\": \"Ceviche de La Casa\", \"notes\": \"SIN LECHUGA\", \"price\": \"35\", \"quantity\": \"1\", \"product_id\": \"3\"}, {\"name\": \"Ceviche Mixto\", \"notes\": \"COMPLETO\", \"price\": \"30\", \"quantity\": \"1\", \"product_id\": \"2\"}, {\"name\": \"Agua Mineral\", \"notes\": null, \"price\": \"3\", \"quantity\": \"1\", \"product_id\": \"8\"}], \"table_number\": \"MESA 5\"}','2026-04-02 02:54:26'),(2,3,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN CEBOLLA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 13, \"unit_price\": \"28.00\"}]}, \"before\": {\"items\": [{\"notes\": \"SIN CEBOLLA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}]}}','2026-04-02 03:02:20'),(3,3,3,'updated','{\"after\": {\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 2, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 13, \"unit_price\": \"28.00\"}]}, \"before\": {\"items\": [{\"notes\": \"SIN CEBOLLA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 13, \"unit_price\": \"28.00\"}]}}','2026-04-02 03:05:38'),(4,3,3,'updated','{\"after\": {\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 2, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 13, \"unit_price\": \"28.00\"}, {\"notes\": \"SIN CILANTRO\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}]}, \"before\": {\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 2, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 13, \"unit_price\": \"28.00\"}]}}','2026-04-02 16:04:44'),(5,4,3,'created','{\"items\": [{\"name\": \"Ceviche Clásico\", \"notes\": \"SIN LECHUGA\", \"price\": \"25\", \"item_key\": \"1::sin lechuga\", \"quantity\": \"1\", \"product_id\": \"1\"}], \"table_id\": 1, \"table_number\": \"5\"}','2026-04-02 16:38:59'),(6,4,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}], \"table_id\": 1, \"table_number\": \"5\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}], \"table_id\": 1, \"table_number\": \"5\"}}','2026-04-02 16:39:19'),(7,4,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 10, \"unit_price\": \"10.00\"}], \"table_id\": 1, \"table_number\": \"5\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}], \"table_id\": 1, \"table_number\": \"5\"}}','2026-04-02 16:51:36'),(8,4,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 10, \"unit_price\": \"10.00\"}], \"table_id\": 2, \"table_number\": \"6\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 10, \"unit_price\": \"10.00\"}], \"table_id\": 1, \"table_number\": \"5\"}}','2026-04-02 16:51:53'),(9,4,2,'completed','{\"total\": \"38.00\", \"details_count\": 3}','2026-04-02 17:01:45'),(10,3,2,'cancelled','[]','2026-04-02 17:27:50'),(11,5,3,'created','{\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 1, \"service_type\": \"dine_in\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 3, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 1\"}','2026-04-02 18:16:53'),(12,7,3,'created','{\"items\": [], \"table_id\": 1, \"service_mode\": \"takeaway\", \"table_number\": \"MESA 1\"}','2026-04-02 18:19:19'),(13,5,3,'updated','{\"after\": {\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 1\"}, \"before\": {\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 1\"}}','2026-04-02 18:19:19'),(14,7,3,'updated','{\"after\": {\"items\": [{\"notes\": \"COMPLETO\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"takeaway\", \"table_number\": \"MESA 1\"}, \"before\": {\"items\": [{\"notes\": null, \"quantity\": 1, \"product_id\": 5, \"unit_price\": \"5.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"takeaway\", \"table_number\": \"MESA 1\"}}','2026-04-02 18:19:52'),(15,8,3,'created','{\"items\": [{\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 2\"}','2026-04-02 18:26:25'),(16,9,3,'created','{\"items\": [{\"notes\": null, \"quantity\": 1, \"product_id\": 1, \"service_type\": \"takeaway\"}], \"table_id\": 2, \"service_mode\": \"takeaway\", \"table_number\": \"MESA 2\"}','2026-04-02 18:26:25'),(17,5,3,'cancelled','[]','2026-04-02 18:28:42'),(18,7,3,'cancelled','[]','2026-04-02 18:28:44'),(19,9,3,'cancelled','[]','2026-04-02 18:28:47'),(20,8,3,'cancelled','[]','2026-04-02 18:28:50'),(21,10,3,'created','{\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}','2026-04-02 18:31:57'),(22,10,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}}','2026-04-02 18:32:52'),(23,10,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 2, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}}','2026-04-02 18:33:49'),(24,10,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 2, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\", \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 2, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}}','2026-04-02 18:34:06'),(25,10,3,'cancelled','[]','2026-04-02 18:34:43'),(26,11,3,'created','{\"items\": [{\"notes\": null, \"quantity\": 2, \"product_id\": 1, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 4, \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 7, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 6, \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}','2026-04-02 18:39:21'),(27,11,3,'updated','{\"after\": {\"items\": [{\"notes\": null, \"quantity\": 2, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 4, \"unit_price\": \"28.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 7, \"unit_price\": \"6.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 6, \"unit_price\": \"4.00\", \"service_type\": \"takeaway\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}, \"before\": {\"items\": [{\"notes\": null, \"quantity\": 2, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"unit_price\": \"30.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 4, \"unit_price\": \"28.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 7, \"unit_price\": \"6.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 6, \"unit_price\": \"4.00\", \"service_type\": \"takeaway\"}], \"table_id\": 1, \"service_mode\": \"mixed\", \"table_number\": \"MESA 1\"}}','2026-04-02 18:42:01'),(28,12,3,'created','{\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 2\"}','2026-04-02 18:42:18'),(29,12,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 2\"}}','2026-04-02 18:42:56'),(30,12,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 6, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}}','2026-04-02 18:44:34'),(31,12,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 6, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 5, \"unit_price\": \"5.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 6, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}}','2026-04-02 18:45:15'),(32,12,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 16, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 5, \"unit_price\": \"5.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 6, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 5, \"unit_price\": \"5.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}}','2026-04-02 18:50:23'),(33,12,3,'updated','{\"after\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 26, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 5, \"unit_price\": \"5.00\", \"service_type\": \"dine_in\"}], \"table_id\": 3, \"service_mode\": \"mixed\", \"table_number\": \"MESA 3\"}, \"before\": {\"items\": [{\"notes\": \"SIN LECHUGA\", \"quantity\": 1, \"product_id\": 1, \"unit_price\": \"25.00\", \"service_type\": \"dine_in\"}, {\"notes\": \"SIN LECHUGA\", \"quantity\": 10, \"product_id\": 3, \"unit_price\": \"35.00\", \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 16, \"product_id\": 8, \"unit_price\": \"3.00\", \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 5, \"unit_price\": \"5.00\", \"service_type\": \"dine_in\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}}','2026-04-02 18:54:17'),(34,13,3,'created','{\"items\": [{\"notes\": null, \"quantity\": 1, \"product_id\": 1, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 2, \"service_type\": \"dine_in\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 3, \"service_type\": \"takeaway\"}], \"table_id\": 2, \"service_mode\": \"mixed\", \"table_number\": \"MESA 2\"}','2026-04-02 19:00:53'),(35,11,2,'completed','{\"total\": \"173.00\", \"change_amount\": 0, \"details_count\": 7, \"payment_method\": \"cash\", \"qr_paid_amount\": 0, \"amount_received\": 173, \"cash_paid_amount\": 173}','2026-04-02 19:02:19'),(36,12,2,'completed','{\"total\": \"458.00\", \"change_amount\": 0, \"details_count\": 4, \"payment_method\": \"qr\", \"qr_paid_amount\": 458, \"amount_received\": 458, \"cash_paid_amount\": 0}','2026-04-02 19:02:36'),(37,13,2,'completed','{\"total\": \"90.00\", \"change_amount\": 0, \"details_count\": 3, \"payment_method\": \"mixed\", \"qr_paid_amount\": 40, \"amount_received\": 90, \"cash_paid_amount\": 50}','2026-04-02 19:02:50'),(38,14,3,'created','{\"items\": [{\"notes\": null, \"quantity\": 9, \"product_id\": 8, \"service_type\": \"dine_in\"}], \"table_id\": 5, \"service_mode\": \"dine_in\", \"table_number\": \"MESA 5\"}','2026-04-02 19:16:12'),(39,15,2,'created','{\"items\": [{\"notes\": null, \"quantity\": 1, \"product_id\": 9, \"unit_price\": null, \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 4, \"product_id\": 1, \"unit_price\": null, \"service_type\": \"takeaway\"}], \"table_id\": null, \"waiter_id\": 5, \"service_mode\": \"takeaway\", \"table_number\": \"Delivery\"}','2026-04-02 19:24:37'),(40,16,2,'created','{\"items\": [{\"notes\": null, \"quantity\": 1, \"product_id\": 9, \"unit_price\": null, \"service_type\": \"takeaway\"}, {\"notes\": null, \"quantity\": 1, \"product_id\": 8, \"unit_price\": null, \"service_type\": \"takeaway\"}], \"table_id\": null, \"waiter_id\": 5, \"service_mode\": \"takeaway\", \"table_number\": \"Delivery\"}','2026-04-02 19:31:37'),(41,15,2,'completed','{\"total\": \"112.00\", \"change_amount\": 0, \"details_count\": 2, \"payment_method\": \"cash\", \"qr_paid_amount\": 0, \"amount_received\": 112, \"cash_paid_amount\": 112}','2026-04-02 19:31:49'),(42,16,2,'completed','{\"total\": \"15.00\", \"change_amount\": 0, \"details_count\": 2, \"payment_method\": \"cash\", \"qr_paid_amount\": 0, \"amount_received\": 15, \"cash_paid_amount\": 15}','2026-04-02 19:32:03'),(43,14,2,'completed','{\"total\": \"27.00\", \"change_amount\": 0, \"details_count\": 1, \"payment_method\": \"cash\", \"qr_paid_amount\": 0, \"amount_received\": 27, \"cash_paid_amount\": 27}','2026-04-02 19:32:28');
/*!40000 ALTER TABLE `order_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `service_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dine_in',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_details_product_id_foreign` (`product_id`),
  KEY `order_details_order_id_product_id_index` (`order_id`,`product_id`),
  CONSTRAINT `order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
INSERT INTO `order_details` VALUES (1,1,12,3,32.00,96.00,NULL,'dine_in','2026-02-10 02:29:03','2026-02-10 02:40:28'),(2,1,1,1,25.00,25.00,NULL,'dine_in','2026-02-10 02:29:03','2026-02-10 02:29:03'),(3,1,8,2,3.00,6.00,NULL,'dine_in','2026-02-10 02:38:37','2026-02-10 02:40:11'),(4,1,11,1,15.00,15.00,NULL,'dine_in','2026-02-10 02:43:07','2026-02-10 02:43:07'),(5,2,1,1,25.00,25.00,NULL,'dine_in','2026-02-10 16:49:10','2026-02-10 16:49:10'),(6,2,3,1,35.00,35.00,NULL,'dine_in','2026-02-10 16:49:10','2026-02-10 16:49:10'),(7,2,6,1,4.00,4.00,NULL,'dine_in','2026-02-10 16:49:10','2026-02-10 16:49:10'),(8,2,9,1,12.00,12.00,NULL,'dine_in','2026-02-10 16:49:23','2026-02-10 16:49:23'),(14,3,1,2,25.00,50.00,'COMPLETO','dine_in','2026-04-02 16:04:44','2026-04-02 16:04:44'),(15,3,3,1,35.00,35.00,'SIN LECHUGA','dine_in','2026-04-02 16:04:44','2026-04-02 16:04:44'),(16,3,2,1,30.00,30.00,'COMPLETO','dine_in','2026-04-02 16:04:44','2026-04-02 16:04:44'),(17,3,8,1,3.00,3.00,NULL,'dine_in','2026-04-02 16:04:44','2026-04-02 16:04:44'),(18,3,13,1,28.00,28.00,'SIN LECHUGA','dine_in','2026-04-02 16:04:44','2026-04-02 16:04:44'),(19,3,1,1,25.00,25.00,'SIN CILANTRO','dine_in','2026-04-02 16:04:44','2026-04-02 16:04:44'),(26,4,1,1,25.00,25.00,'SIN LECHUGA','dine_in','2026-04-02 16:51:53','2026-04-02 16:51:53'),(27,4,8,1,3.00,3.00,NULL,'dine_in','2026-04-02 16:51:53','2026-04-02 16:51:53'),(28,4,10,1,10.00,10.00,'COMPLETO','dine_in','2026-04-02 16:51:53','2026-04-02 16:51:53'),(35,5,1,1,25.00,25.00,'COMPLETO','dine_in','2026-04-02 18:19:19','2026-04-02 18:19:19'),(36,5,3,1,35.00,35.00,'COMPLETO','dine_in','2026-04-02 18:19:19','2026-04-02 18:19:19'),(37,5,8,1,3.00,3.00,NULL,'dine_in','2026-04-02 18:19:19','2026-04-02 18:19:19'),(39,7,1,1,25.00,25.00,'COMPLETO','takeaway','2026-04-02 18:19:52','2026-04-02 18:19:52'),(40,8,3,1,35.00,35.00,NULL,'dine_in','2026-04-02 18:26:25','2026-04-02 18:26:25'),(41,9,1,1,25.00,25.00,NULL,'takeaway','2026-04-02 18:26:25','2026-04-02 18:26:25'),(56,10,1,1,25.00,25.00,'SIN LECHUGA','dine_in','2026-04-02 18:34:06','2026-04-02 18:34:06'),(57,10,1,1,25.00,25.00,'SIN LECHUGA','takeaway','2026-04-02 18:34:06','2026-04-02 18:34:06'),(58,10,3,1,35.00,35.00,NULL,'dine_in','2026-04-02 18:34:06','2026-04-02 18:34:06'),(59,10,8,2,3.00,6.00,NULL,'dine_in','2026-04-02 18:34:06','2026-04-02 18:34:06'),(60,10,3,1,35.00,35.00,'SIN LECHUGA','takeaway','2026-04-02 18:34:06','2026-04-02 18:34:06'),(61,10,2,1,30.00,30.00,NULL,'dine_in','2026-04-02 18:34:06','2026-04-02 18:34:06'),(68,11,1,2,25.00,50.00,NULL,'dine_in','2026-04-02 18:42:01','2026-04-02 18:42:01'),(69,11,2,1,30.00,30.00,NULL,'takeaway','2026-04-02 18:42:01','2026-04-02 18:42:01'),(70,11,2,1,30.00,30.00,NULL,'dine_in','2026-04-02 18:42:01','2026-04-02 18:42:01'),(71,11,4,1,28.00,28.00,NULL,'takeaway','2026-04-02 18:42:01','2026-04-02 18:42:01'),(72,11,7,1,6.00,6.00,NULL,'dine_in','2026-04-02 18:42:01','2026-04-02 18:42:01'),(73,11,6,1,4.00,4.00,NULL,'takeaway','2026-04-02 18:42:01','2026-04-02 18:42:01'),(74,11,1,1,25.00,25.00,'SIN LECHUGA','dine_in','2026-04-02 18:42:01','2026-04-02 18:42:01'),(89,12,1,1,25.00,25.00,'SIN LECHUGA','dine_in','2026-04-02 18:54:17','2026-04-02 18:54:17'),(90,12,3,10,35.00,350.00,'SIN LECHUGA','takeaway','2026-04-02 18:54:17','2026-04-02 18:54:17'),(91,12,8,26,3.00,78.00,NULL,'dine_in','2026-04-02 18:54:17','2026-04-02 18:54:17'),(92,12,5,1,5.00,5.00,NULL,'dine_in','2026-04-02 18:54:17','2026-04-02 18:54:17'),(93,13,1,1,25.00,25.00,NULL,'dine_in','2026-04-02 19:00:53','2026-04-02 19:00:53'),(94,13,2,1,30.00,30.00,NULL,'dine_in','2026-04-02 19:00:53','2026-04-02 19:00:53'),(95,13,3,1,35.00,35.00,NULL,'takeaway','2026-04-02 19:00:53','2026-04-02 19:00:53'),(96,14,8,9,3.00,27.00,NULL,'dine_in','2026-04-02 19:16:12','2026-04-02 19:16:12'),(97,15,9,1,12.00,12.00,NULL,'takeaway','2026-04-02 19:24:37','2026-04-02 19:24:37'),(98,15,1,4,25.00,100.00,NULL,'takeaway','2026-04-02 19:24:37','2026-04-02 19:24:37'),(99,16,9,1,12.00,12.00,NULL,'takeaway','2026-04-02 19:31:37','2026-04-02 19:31:37'),(100,16,8,1,3.00,3.00,NULL,'takeaway','2026-04-02 19:31:37','2026-04-02 19:31:37');
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `cash_session_id` bigint unsigned DEFAULT NULL,
  `table_id` bigint unsigned DEFAULT NULL,
  `table_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dine_in',
  `order_date` date DEFAULT NULL,
  `daily_sequence` int unsigned DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_received` decimal(10,2) DEFAULT NULL,
  `cash_paid_amount` decimal(10,2) DEFAULT NULL,
  `qr_paid_amount` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `revert_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_date_daily_sequence_unique` (`order_date`,`daily_sequence`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_cashier_id_foreign` (`cashier_id`),
  KEY `orders_cash_session_id_foreign` (`cash_session_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_completed_at_index` (`completed_at`),
  KEY `orders_table_id_status_index` (`table_id`,`status`),
  CONSTRAINT `orders_cash_session_id_foreign` FOREIGN KEY (`cash_session_id`) REFERENCES `cash_sessions` (`id`),
  CONSTRAINT `orders_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,3,2,NULL,1,'5','dine_in','2026-02-09',1,142.00,142.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-02-10 16:57:00',NULL,'2026-02-10 02:29:03','2026-02-10 16:57:00'),(2,3,2,NULL,2,'6','dine_in','2026-02-10',1,76.00,76.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-02-10 16:55:46',NULL,'2026-02-10 16:49:10','2026-02-10 16:55:46'),(3,3,NULL,NULL,3,'MESA 5','dine_in','2026-04-01',1,171.00,171.00,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 02:54:26','2026-04-02 17:27:50'),(4,3,2,NULL,2,'6','dine_in','2026-04-02',1,38.00,38.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-04-02 17:01:45',NULL,'2026-04-02 16:38:59','2026-04-02 17:01:45'),(5,3,NULL,NULL,1,'MESA 1','dine_in','2026-04-02',2,63.00,63.00,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 18:16:53','2026-04-02 18:28:42'),(7,3,NULL,NULL,1,'MESA 1','takeaway','2026-04-02',3,25.00,25.00,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 18:19:19','2026-04-02 18:28:44'),(8,3,NULL,NULL,2,'MESA 2','dine_in','2026-04-02',4,35.00,35.00,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 18:26:25','2026-04-02 18:28:50'),(9,3,NULL,NULL,2,'MESA 2','takeaway','2026-04-02',5,25.00,25.00,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 18:26:25','2026-04-02 18:28:47'),(10,3,NULL,NULL,1,'MESA 1','mixed','2026-04-02',6,156.00,156.00,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 18:31:57','2026-04-02 18:34:43'),(11,3,2,1,1,'MESA 1','mixed','2026-04-02',7,173.00,173.00,'completed','cash',173.00,173.00,0.00,0.00,'2026-04-02 19:02:19',NULL,'2026-04-02 18:39:21','2026-04-02 19:02:19'),(12,3,2,1,3,'MESA 3','mixed','2026-04-02',8,458.00,458.00,'completed','qr',458.00,0.00,458.00,0.00,'2026-04-02 19:02:36',NULL,'2026-04-02 18:42:18','2026-04-02 19:02:36'),(13,3,2,1,2,'MESA 2','mixed','2026-04-02',9,90.00,90.00,'completed','mixed',90.00,50.00,40.00,0.00,'2026-04-02 19:02:50',NULL,'2026-04-02 19:00:53','2026-04-02 19:02:50'),(14,3,2,2,5,'MESA 5','dine_in','2026-04-02',10,27.00,27.00,'completed','cash',27.00,27.00,0.00,0.00,'2026-04-02 19:32:28',NULL,'2026-04-02 19:16:12','2026-04-02 19:32:28'),(15,5,2,2,NULL,'Delivery','takeaway','2026-04-02',11,112.00,112.00,'completed','cash',112.00,112.00,0.00,0.00,'2026-04-02 19:31:49',NULL,'2026-04-02 19:24:37','2026-04-02 19:31:49'),(16,5,2,2,NULL,'Delivery','takeaway','2026-04-02',12,15.00,15.00,'completed','cash',15.00,15.00,0.00,0.00,'2026-04-02 19:32:03',NULL,'2026-04-02 19:31:37','2026-04-02 19:32:03');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Ceviche Clásico','Pescado fresco marinado en limón con cebolla, ají y culantro',25.00,'uploads/products/378bd760-5740-4ce3-a469-5363e2e01ff2.jpeg',1,40,'2026-02-10 01:36:05','2026-04-02 19:31:49'),(2,1,'Ceviche Mixto','Combinación de pescado, camarones y pulpo',30.00,'uploads/products/1c0b9c09-c1b8-4627-92d3-2ff812637e32.jpeg',1,37,'2026-02-10 01:36:05','2026-04-02 19:02:50'),(3,1,'Ceviche de La Casa','Exquisito ceviche de la casa',35.00,'uploads/products/5b7aa762-5a99-4d2e-b44a-1fd86b2b73d8.jpeg',1,19,'2026-02-10 01:36:05','2026-04-02 19:02:50'),(4,4,'Arroz Chaufa','Arroz Chaufa con frutos de mar',28.00,'uploads/products/3dfbcbbb-a6a0-4a54-8db1-bef0ac1460a4.jpeg',1,34,'2026-02-10 01:36:05','2026-04-02 19:02:19'),(5,4,'Pacú Frito','Pacú frito con papas y mote',5.00,'uploads/products/ed8da651-5f27-49be-b0f7-6f5236170258.jpeg',1,99,'2026-02-10 01:36:05','2026-04-02 19:02:36'),(6,4,'Pacú a la Parrilla','Pacú a la Parrilla',4.00,'uploads/products/0c8560c9-1de0-43ff-9b56-9db116cc31be.jpeg',1,79,'2026-02-10 01:36:05','2026-04-02 19:02:19'),(7,4,'Chicharron de Paiche','Chicharrón de Paiche con papas y mote',6.00,'uploads/products/de4d7160-6e42-4a4f-b7c7-62b95e70f351.jpeg',1,59,'2026-02-10 01:36:05','2026-04-02 19:02:19'),(8,2,'Agua Mineral','Agua mineral 625ml',3.00,NULL,1,63,'2026-02-10 01:36:05','2026-04-02 19:32:28'),(9,3,'Causa Limeña','Papa amarilla rellena con pollo o atún',12.00,NULL,1,38,'2026-02-10 01:36:05','2026-04-02 19:32:03'),(10,3,'Papa a la Huancaína','Papas con salsa de ají amarillo',10.00,NULL,1,49,'2026-02-10 01:36:05','2026-04-02 17:01:45'),(11,3,'Choritos a la Chalaca','Mejillones con salsa criolla',15.00,NULL,1,30,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(12,4,'Arroz con Mariscos','Arroz con variedad de mariscos',32.00,NULL,1,35,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(13,4,'Sudado de Pescado','Pescado en salsa con yucas',28.00,NULL,1,40,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(14,4,'Chicharrón de Calamar','Calamar frito crujiente',26.00,NULL,1,45,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(15,4,'Jalea Mixta','Variedad de mariscos fritos',35.00,NULL,1,30,'2026-02-10 01:36:05','2026-02-10 01:36:05');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `reservation_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reservation_at` timestamp NULL DEFAULT NULL,
  `reservation_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tables_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
INSERT INTO `tables` VALUES (1,'MESA 1',NULL,NULL,1,NULL,NULL,NULL,'2026-04-02 16:20:59','2026-04-02 17:31:30'),(2,'MESA 2',NULL,NULL,1,NULL,NULL,NULL,'2026-04-02 16:20:59','2026-04-02 17:31:38'),(3,'MESA 3',NULL,NULL,1,NULL,NULL,NULL,'2026-04-02 16:20:59','2026-04-02 17:31:42'),(4,'MESA 4',NULL,NULL,1,NULL,NULL,NULL,'2026-04-02 17:31:57','2026-04-02 17:31:57'),(5,'MESA 5',NULL,NULL,1,NULL,NULL,NULL,'2026-04-02 17:32:07','2026-04-02 17:32:07');
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','cajero','mesero') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mesero',
  `order_channel` enum('table','delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'table',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrador','admin@cevicheria.com','$2y$12$jOxwXe4GtWcSRU3yyahZfuF7HsmedOvZ9IFjYtj8eGJRQNix7Jfcy','admin','table',1,NULL,'2026-02-10 01:36:04','2026-02-10 01:36:04'),(2,'Juan Pérez','cajero@cevicheria.com','$2y$12$8SXShDOKCFm8oCVjFWX2X.uRuKAF0PwHmNkq0MLxdUwsHYiY70mGS','cajero','table',1,NULL,'2026-02-10 01:36:04','2026-02-10 01:36:04'),(3,'María García','mesero@cevicheria.com','$2y$12$E8kF.gx6Q3pJ18YzViQ2/.3v7DmSTZS3Irz.jLgwcv4Y2PR62tXJe','mesero','table',1,NULL,'2026-02-10 01:36:05','2026-02-10 01:36:05'),(4,'Horacio Poveda','horacio@cevicheria.com','$2y$12$N/FlpB1nc9R1.AMTLxlv4u5hL9J4VS47lOiwrsi81aABfpEhdOnvO','mesero','table',1,NULL,'2026-02-10 23:09:02','2026-02-10 23:26:02'),(5,'DELIVERY','delivery@cevicheria.com','$2y$12$h..br4GOcySzrB6xUTJ4T.JFN8RCGyD6Zve2wFUAKBdstSri2QBK.','mesero','delivery',1,NULL,'2026-04-02 19:24:09','2026-04-02 19:24:09');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'cevicheria_pos'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-06 10:21:45
