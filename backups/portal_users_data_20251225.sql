/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: db    Database: wordpress
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
mysqldump: Error: 'Access denied; you need (at least one of) the PROCESS privilege(s) for this operation' when trying to dump tablespaces

--
-- Dumping data for table `lqbk_portal_users`
--

LOCK TABLES `lqbk_portal_users` WRITE;
/*!40000 ALTER TABLE `lqbk_portal_users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `lqbk_portal_users` VALUES
(5,1,'mmla2024','nyef40mmla@gmail.com','Nick','Stockton','(213) 444-5555','','','','','',1,'','','','2025-06-19 06:40:57','2025-12-25 16:57:38',NULL,'2025-12-25 16:57:38'),
(13,63,'admin1','nickyyefimov@gmail.com','Nick','Small','(310) 346-5555','Mobile Medical LA','1040 Dove LN','FOSTER CITY','CA','94404',1,'Nursing','CA2026','HHA','0000-00-00 00:00:00','2025-12-13 14:13:32','0a2bf516e3b331eb919bd82953402afe64f6ac402eae8ddaae85a75cce7fb99d',NULL),
(14,64,'nbig','big@big.com','Nick','Big','(310) 346-7777','Mobile Medical LA','1020 Dove LN','FOSTER CITY','CA','94404',1,'Nursing','CA2027','OT','0000-00-00 00:00:00','2025-12-17 17:30:27','b5187d846245d658ed5937a9e31e6d2601b8013f8e6e16ace498296bf099779f',NULL);
/*!40000 ALTER TABLE `lqbk_portal_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-12-25 21:35:04
