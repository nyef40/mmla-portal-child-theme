mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: wordpress
-- ------------------------------------------------------
-- Server version	8.0.42

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
-- Table structure for table `lqbk_referral_submissions`
--

DROP TABLE IF EXISTS `lqbk_referral_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lqbk_referral_submissions` (
  `submission_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `provider_practice` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `provider_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `provider_phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `patient_name` varbinary(512) DEFAULT NULL,
  `patient_email` varbinary(512) DEFAULT NULL,
  `patient_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `insurance` varbinary(1000) DEFAULT NULL,
  `reason` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `user_id` bigint DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `validation_token` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_validated` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`submission_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `lqbk_referral_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `lqbk_portal_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lqbk_referral_submissions`
--

LOCK TABLES `lqbk_referral_submissions` WRITE;
/*!40000 ALTER TABLE `lqbk_referral_submissions` DISABLE KEYS */;
INSERT INTO `lqbk_referral_submissions` VALUES (3,'Dr. Test','Test Clinic','nickyyefimov@gmail.com','+11234567890',_binary 'JX3v6dYkRLFACklscGjOVQ==',NULL,'',NULL,'Specialist Referral','IVIG',1,'2025-04-25 05:21:25',NULL,'400e576d-49f9-47cb-9577-fe993c6649b9',0),(5,'Dr. BMW2','Peter Pan BMW2','nyef40@yahoo.com','+12223334444',_binary 'DPFkBJGBbgxOwcEI1WBx5w==',_binary 'zOmUF3M+qzBiIwIwNBU8zA==','+14445556666',_binary 'fyJX4Z+ZgJuqRgR823Ftyw==','Other','IVIG3',1,'2025-04-26 05:26:32',NULL,'b8fd795c-3052-4c29-b299-f6b5d9b97f10',0),(6,'Dr. Tiger','Cat Tiger Clinic','nickyyefimov@gmail.com','+12223334455',_binary 'UNIhAZ588KOl/vOqcGQZwQ==',_binary '1M5z/O9Uoe46imitLQ44Dw==','+14445556677',_binary 'qwdIoKUcpm+KJoUIIB3+2g==','Specialist Referral','HIT Patient',1,'2025-04-26 06:39:02',NULL,'f2b2c4be-f85a-4448-9b99-98a7d6ff1647',0),(7,'Dr. Lion','Lion Clinic','nickyyefimov@gmail.com','+12223334477',_binary 'TuuAJ1Iveu99msYNcapEig==',_binary 'X++x6dES8C4XriPxCNFi1g==','+14445556677',_binary 'j1P+f5mS22+4GnMDIBppIw==','Specialist Referral','HIT Patient',1,'2025-04-26 08:09:37',NULL,'9e4e19f7-c978-4885-a887-af86cf3c061b',0),(8,'Dr. Bear','Bear Clinic','nickyyefimov@gmail.com','+13334445555',_binary 'WZyacaCcRAlFE4qsrJIm5A==',_binary 'zsB7eUUvmEL55RKfGsLd2Q==','+12224444455',_binary '79VdkxCdS1y6E050rZ7R9w==','Specialist Referral','hizentra',1,'2025-04-27 01:41:28',NULL,'92c8835d-bb15-4bb9-8e91-1eeec1161916',0),(9,'Dr. Wolf','Wolf Clinic','nickyyefimov@gmail.com','+13334442222',_binary 'wVYp+cBG9VGQpB0QPq9+nQ==',_binary 'MFywTdM0thXj7bPmyWM5rg==','+15554447788',_binary '79VdkxCdS1y6E050rZ7R9w==','Specialist Referral','HIT Patient',1,'2025-04-27 03:43:47',NULL,'77f802fa-1477-41ad-aa53-0312a54b8d46',0),(10,'Dr.Wolf','Wolf Clinic','nickyyefimov@gmail.com','333-444-6666',_binary 'RXPL2KBuH+UdYtbwZswIvw==',_binary 'Pgn27yhqboI9/FnsN4Q0SA==','222-555-8888',_binary 'JUbVi6o6lTDmmvc8b9r+TQ==','Other','hizentra',1,'2025-04-27 04:34:35',NULL,'f9fc131f-c3c1-40ae-9987-434f788c2bc0',0),(11,'Dr.Fox','Fox Clinic','nickyyefimov@gmail.com','222-111-3333',_binary '58NQHjycvLFrcJWbvYGrQA==',_binary '/uIsPQTXxLDs+HKym8j9Sw==','555-000-6666',_binary 'j1P+f5mS22+4GnMDIBppIw==','Specialist Referral','hizentra',1,'2025-04-27 07:07:15',NULL,'ab843896-f5e7-471d-bc8c-241c3db12ad1',0),(12,'Dr. Dog','Dog Clinic','nickyyefimov@gmail.com','333-000-9999',_binary '8vj2awm4BUdYodDfI4MaHA==',_binary 'fcRf4jqEuYsI8OsWeV8J6g==','111-888-0000',_binary 'j1P+f5mS22+4GnMDIBppIw==','General Consultation','hizentra',1,'2025-04-27 07:39:06',NULL,'d158b83c-2fd5-45d3-9838-3eddfbcdf95b',0),(15,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','333-444-5555',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-04-30 10:56:27',NULL,'bfc18efb-7ac6-48cd-af51-8fa20406ce13',0),(16,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','444-555-6666',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-04-30 11:52:39',NULL,'4bc77409-c6e6-46e7-a097-16282da30a3e',0),(17,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-111-3333',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','hizentra',NULL,'2025-04-30 12:37:11',NULL,'eed2bf5e-e595-4a42-916f-3aed24d607f7',0),(18,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-04-30 14:01:54',NULL,'75f1afe9-d491-4739-afe6-fcc306cf91a5',0),(19,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-04-30 14:18:27',NULL,'31666090-18cd-40a0-a47e-afaa0a0f7a33',0),(20,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-555-8888',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-04-30 15:04:12',NULL,'525b263b-983f-463f-97ee-9b42e1aa9fec',0),(21,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'HynNPC+oQdWAwNqeXN7k53JUJn+LIhXR6TMtWTsO+IE=','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','Urgent Care','HIT',NULL,'2025-05-01 10:16:03',NULL,'da85eaa7-bbf5-44a8-a87c-8a46e413ae25',0),(22,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'sm6mtsVboG9nsEwey/whHHJUJn+LIhXR6TMtWTsO+IE=','222-111-3333',_binary '79VdkxCdS1y6E050rZ7R9w==','Urgent Care','hizentra',NULL,'2025-05-01 14:48:55',NULL,'86c243b1-0e69-44d1-9010-32865496e3db',0),(23,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'sm6mtsVboG9nsEwey/whHHJUJn+LIhXR6TMtWTsO+IE=','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-05-01 17:07:33',NULL,'575e1ec2-6860-4b88-8f95-1c9bf0062b3b',0),(24,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary 'axHzmlpwgI1HG2DG0Tyf9A==','General Consultation','HIT patient',NULL,'2025-05-02 17:26:31',NULL,'ec8a9031-cceb-4374-8357-d66db77c52b1',0),(25,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'HynNPC+oQdWAwNqeXN7k53JUJn+LIhXR6TMtWTsO+IE=','222-111-3344',_binary 'axHzmlpwgI1HG2DG0Tyf9A==','General Consultation','HIT',NULL,'2025-05-03 03:27:57',NULL,'310acdea-dd9f-4aa6-819b-4cf7fe353d43',0),(26,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-05-03 06:32:52',NULL,'aa17ab22-d542-4ef2-aa51-2297736b1259',0),(27,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-05-03 06:40:26',NULL,'81b93504-bd35-4d6d-898e-db7ca1988894',0),(28,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','222-333-4444',_binary 'bncBokJQsDKzmGi0+7FVfw==',_binary 'aPlnKKhqoAPWlJPRfeXtYQ==','222-111-3344',_binary '79VdkxCdS1y6E050rZ7R9w==','General Consultation','HIT',NULL,'2025-05-03 07:16:49',NULL,'fd1cc9d9-d05c-4125-9630-0ecdc8f0c6fe',0),(29,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-03 21:22:11',NULL,'ce4a5715-d145-45c2-b3c2-dc4d6c28a58f',0),(30,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-04 21:59:59',NULL,'6bf1ea3e-6987-40b3-b16b-70de00f78fb0',0),(31,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-05 01:26:58',NULL,'b1e1cccc-3579-4cdd-a26a-8b8146ba0aab',0),(32,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-05 01:43:26',NULL,'700f0758-ce6b-42b5-81a9-55896ff3e7ff',0),(33,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-05 01:51:07',NULL,'ad9d6b8c-f896-4184-9785-4f7727d06f47',0),(34,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-05 02:04:07',NULL,'34ed24a9-ab00-4474-b7b8-ea0416249ad1',0),(35,'RWloOVdUbS9jSWFyR0FVRWhGalgvZz09','','bU8yK0M1NVRSSFBWWTFYaVM0dFBiKzZ4cDZlSURsaWJYZVVxTHpUVXprRT0=','','','','','','','RGdlWUFyL1A5emppRElxZ1dCdms3Zz09',NULL,'2025-05-05 13:33:40',NULL,'5ffad9cb-b908-4727-b409-bed9867a0285',0),(36,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','(222) 111-4444',_binary 'NzhLYU9WQkFrMFBxbkh2VEF0N09KZz09',_binary 'aS9Fd1RvWmxHRndzcDcrR0NRY3dRQT09','(222) 555-1111',_binary 'cUJEU2g0MmVJZzNIV0hUTFFaa1g4UT09','General Consultation','YY',NULL,'2025-05-08 21:26:19',NULL,'15fbf4f5-5911-4a26-91c5-d73ab7e60b10',1),(37,'Dr. Raven','Raven Clinic','nickyyefimov@gmail.com','(222) 111-4444',_binary 'NzhLYU9WQkFrMFBxbkh2VEF0N09KZz09',_binary 'aS9Fd1RvWmxHRndzcDcrR0NRY3dRQT09','(222) 444-5555',_binary 'NU4ySWJidStyZXd3SVRsamlZbXJFdz09','Urgent Care','Y',NULL,'2025-05-09 00:45:16',NULL,'6ab4d006-e23b-44a4-bcd8-6f2b6e072407',1),(38,'Dr. Lion','Lion Clinic','nyef40@yahoo.com','(222) 555-7777',_binary 'K0xDQnMyZXJkWnU3cU1GdFdINmRRUT09',_binary 'cDlweitHWklXcVByNURhZjVNbkhkZz09','(222) 111-5577',_binary 'UFoxcW14S08rWndlYlA2VFY0ZCtEZz09','Urgent Care','Cat',NULL,'2025-05-09 02:11:25',NULL,'389e390c-e6ae-486c-82c8-2970e7bc2faf',1),(39,'Dr. Bear','Bear Clinic','nyef40mmla@gmail.com','(333) 111-2222',_binary 'RkFHUHg1SmtOa3k3SmNEU1pGV1I0Zz09',_binary 'cHZydEdQbkVFWXpZd1pPL29OclRqZz09','(333) 222-1111',_binary 'UE1kZVJmK25qYWM1OEZhZWhOVnV6dz09','Urgent Care','ZYX',NULL,'2025-05-13 05:32:14',NULL,'b5ec5c0c-9d2c-4316-9269-a9b06d71a7d0',0);
/*!40000 ALTER TABLE `lqbk_referral_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lqbk_portal_users`
--

DROP TABLE IF EXISTS `lqbk_portal_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lqbk_portal_users` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `wp_user_id` bigint unsigned NOT NULL,
  `specialty` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wp_user_id` (`wp_user_id`),
  CONSTRAINT `lqbk_portal_users_ibfk_1` FOREIGN KEY (`wp_user_id`) REFERENCES `lqbk_users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lqbk_portal_users`
--

LOCK TABLES `lqbk_portal_users` WRITE;
/*!40000 ALTER TABLE `lqbk_portal_users` DISABLE KEYS */;
INSERT INTO `lqbk_portal_users` VALUES (1,47,'','',0,'2025-03-20 09:55:00'),(2,48,'LVN','12345',1,'2025-03-21 13:31:31'),(3,49,'LVN','12345',1,'2025-03-21 14:52:12');
/*!40000 ALTER TABLE `lqbk_portal_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lqbk_portal_resources`
--

DROP TABLE IF EXISTS `lqbk_portal_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lqbk_portal_resources` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lqbk_portal_resources`
--

LOCK TABLES `lqbk_portal_resources` WRITE;
/*!40000 ALTER TABLE `lqbk_portal_resources` DISABLE KEYS */;
INSERT INTO `lqbk_portal_resources` VALUES (1,'Referral Form','Refer a Patient form submissions',NULL,NULL,'2025-04-25 11:37:14');
/*!40000 ALTER TABLE `lqbk_portal_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lqbk_portal_access_logs`
--

DROP TABLE IF EXISTS `lqbk_portal_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lqbk_portal_access_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint DEFAULT NULL,
  `resource_id` bigint NOT NULL,
  `accessed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `details` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `resource_id` (`resource_id`),
  KEY `idx_resource_access` (`user_id`,`resource_id`),
  CONSTRAINT `lqbk_portal_access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `lqbk_portal_users` (`id`),
  CONSTRAINT `lqbk_portal_access_logs_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `lqbk_portal_resources` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lqbk_portal_access_logs`
--

LOCK TABLES `lqbk_portal_access_logs` WRITE;
/*!40000 ALTER TABLE `lqbk_portal_access_logs` DISABLE KEYS */;
INSERT INTO `lqbk_portal_access_logs` VALUES (2,1,1,'2025-04-25 19:21:25','referral_submission','Submitted referral for Test Patient'),(3,1,1,'2025-04-26 18:51:55','referral_submission','Submitted referral for Nick Small'),(4,1,1,'2025-04-26 19:26:32','referral_submission','Submitted referral for Nick Small2'),(5,1,1,'2025-04-27 03:39:02','referral_submission','Submitted referral for Nick Big'),(6,1,1,'2025-04-27 05:09:37','referral_submission','Submitted referral for Nick Large'),(7,1,1,'2025-04-27 22:41:28','referral_submission','Submitted referral for Nick Huge'),(8,1,1,'2025-04-28 00:43:47','referral_submission','Submitted referral for Nick Giant'),(9,1,1,'2025-04-28 01:34:35','referral_submission','Submitted referral for Nick Red'),(10,1,1,'2025-04-28 04:07:15','referral_submission','Submitted referral for Nick Blue'),(11,1,1,'2025-04-28 04:39:06','referral_submission','Submitted referral for Nick White');
/*!40000 ALTER TABLE `lqbk_portal_access_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-13 22:30:35
