-- MariaDB dump 10.19-11.2.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: db-mmenu-demo-v2
-- ------------------------------------------------------
-- Server version	11.2.3-MariaDB-1:11.2.3+maria~ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cafe`
--

DROP TABLE IF EXISTS `cafe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cafe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `uniq_name` varchar(100) NOT NULL,
  `cafe_title` varchar(250) NOT NULL,
  `chief_cook` varchar(1000) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire_on` timestamp NULL DEFAULT NULL,
  `cafe_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=test, 1=archive, 2=contract',
  `cafe_currency` varchar(6) NOT NULL,
  `skin_label` varchar(30) NOT NULL DEFAULT 'dark-classical',
  `extra_langs` text NOT NULL,
  `cafe_address` varchar(1000) NOT NULL,
  `cafe_phone` varchar(400) NOT NULL,
  `cart_mode` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `has_delivery` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `order_way` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '	0-TG, 1-TG THEN IIKO	',
  `price_precision` tinyint(1) NOT NULL DEFAULT 0,
  `lang` enum('en','ru') NOT NULL DEFAULT 'ru',
  `work_hours` varchar(1000) NOT NULL,
  `cafe_description` text NOT NULL,
  `subdomain` varchar(250) NOT NULL,
  `qrcode` varchar(300) NOT NULL,
  `generated_time_sec` varchar(30) NOT NULL,
  `sample` varchar(2) NOT NULL,
  `iiko_api_key` varchar(64) NOT NULL,
  `iiko_organizations` text NOT NULL,
  `iiko_extmenus` text NOT NULL,
  `iiko_tables` text NOT NULL,
  `iiko_terminal_groups` text NOT NULL,
  `iiko_current_extmenu_id` varchar(64) NOT NULL,
  `iiko_current_extmenu_hash` varchar(64) NOT NULL,
  `iiko_order_types` text NOT NULL,
  `tables_uniq_names` text NOT NULL,
  `requested_contract_date` timestamp NULL DEFAULT NULL,
  `requested_qrcode_date` timestamp NULL DEFAULT NULL,
  `rev` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`uniq_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=320 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contract_name` varchar(30) NOT NULL,
  `regdate` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire_on` timestamp NULL DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_cafe` int(10) unsigned NOT NULL,
  `cafe_uniq_name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_name` (`contract_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `iiko_params`
--

DROP TABLE IF EXISTS `iiko_params`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iiko_params` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cafe` int(10) unsigned NOT NULL,
  `modif_dictionary` text NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_cafe` (`id_cafe`),
  CONSTRAINT `iiko_params_ibfk_2` FOREIGN KEY (`id_cafe`) REFERENCES `cafe` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_external` varchar(50) NOT NULL COMMENT 'iiko or some other',
  `id_menu` int(11) unsigned NOT NULL,
  `id_cafe` int(10) unsigned NOT NULL,
  `sku` varchar(64) NOT NULL COMMENT 'external item sku',
  `title` varchar(1000) NOT NULL,
  `title_original` varchar(1000) NOT NULL COMMENT 'iiko original title',
  `description` varchar(1600) NOT NULL,
  `sizes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `extra_data` text NOT NULL COMMENT 'for other languages',
  `image_name` varchar(300) NOT NULL,
  `image_url` varchar(300) NOT NULL,
  `mode_spicy` tinyint(1) NOT NULL DEFAULT 0,
  `mode_vege` tinyint(1) NOT NULL DEFAULT 0,
  `mode_hit` tinyint(1) NOT NULL DEFAULT 0,
  `iiko_modifiers` text NOT NULL,
  `iiko_sizes` text NOT NULL,
  `iiko_order_item_type` varchar(64) NOT NULL,
  `created_by` varchar(20) NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `pos` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `hidden` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_menu` (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=2980 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_cafe_generating`
--

DROP TABLE IF EXISTS `log_cafe_generating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_cafe_generating` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cafe` int(10) unsigned NOT NULL,
  `sample` varchar(20) NOT NULL,
  `regdate` datetime NOT NULL DEFAULT current_timestamp(),
  `generated_time_sec` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_cafe_removing`
--

DROP TABLE IF EXISTS `log_cafe_removing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_cafe_removing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cafe_uniq_name` varchar(30) NOT NULL,
  `user_owner_email` varchar(50) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `removed_date` datetime NOT NULL DEFAULT current_timestamp(),
  `contracts` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_external` varchar(50) NOT NULL COMMENT 'iiko or some other',
  `id_cafe` int(10) unsigned NOT NULL,
  `title` varchar(250) NOT NULL,
  `extra_data` text NOT NULL COMMENT 'for other languages',
  `id_icon` tinyint(4) NOT NULL DEFAULT 0,
  `pos` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `rev` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `id_cafe` (`id_cafe`)
) ENGINE=InnoDB AUTO_INCREMENT=2124 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_saved`
--

DROP TABLE IF EXISTS `menu_saved`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_saved` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cafe` int(11) NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `menu_json` text NOT NULL,
  `items_json` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `one_time_auth`
--

DROP TABLE IF EXISTS `one_time_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `one_time_auth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(300) NOT NULL,
  `token` varchar(100) NOT NULL,
  `agent_info` varchar(500) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cafe_uniq_name` varchar(30) NOT NULL,
  `id_uniq` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `short_number` varchar(30) NOT NULL,
  `order_target` varchar(64) NOT NULL,
  `table_number` smallint(6) unsigned DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pending_mode` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `state` enum('created','taken','sentout') NOT NULL DEFAULT 'created',
  `manager` varchar(30) NOT NULL COMMENT 'tg_user, who did change the status of the order',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=379 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tg_keys`
--

DROP TABLE IF EXISTS `tg_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tg_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cafe_uniq_name` varchar(30) NOT NULL,
  `tg_key` varchar(30) NOT NULL,
  `role` enum('waiter','manager','supervisor') NOT NULL DEFAULT 'waiter',
  `regdate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tg_key_uniq` (`tg_key`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tg_users`
--

DROP TABLE IF EXISTS `tg_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tg_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tg_user_id` varchar(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `nickname` varchar(30) NOT NULL,
  `role` enum('waiter','manager','supervisor') NOT NULL DEFAULT 'supervisor',
  `state` enum('active','inactive') NOT NULL DEFAULT 'inactive' COMMENT 'inactive do not getting orders',
  `cafe_uniq_name` varchar(20) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `regdate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(300) NOT NULL,
  `name` varchar(150) NOT NULL,
  `password` varchar(400) NOT NULL,
  `regdate` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `new_password` varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `lang` enum('en','ru') NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=282 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-05  5:21:43
