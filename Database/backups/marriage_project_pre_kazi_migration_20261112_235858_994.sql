-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: marriage_project
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Current Database: `marriage_project`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `marriage_project` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `marriage_project`;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(30) DEFAULT 'SuperAdmin',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$XT6.KbuHoeQcsa4kd0PLnuYBg898zLsqc5TXmM.UKrhzfoJVS8wkS','Academic System Administrator','admin@example.com','SuperAdmin','Active','2026-10-23 03:03:59');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authorized_licenses`
--

DROP TABLE IF EXISTS `authorized_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorized_licenses` (
  `license_id` int(11) NOT NULL AUTO_INCREMENT,
  `license_no` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`license_id`),
  UNIQUE KEY `license_no` (`license_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authorized_licenses`
--

LOCK TABLES `authorized_licenses` WRITE;
/*!40000 ALTER TABLE `authorized_licenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `authorized_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizens`
--

DROP TABLE IF EXISTS `citizens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizens` (
  `citizen_id` int(11) NOT NULL AUTO_INCREMENT,
  `citizen_type` enum('Bangladeshi','Foreign') NOT NULL DEFAULT 'Bangladeshi',
  `full_name` varchar(150) NOT NULL,
  `father_name` varchar(150) NOT NULL,
  `mother_name` varchar(150) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `religion` varchar(30) NOT NULL DEFAULT 'Muslim',
  `national_id` varchar(30) NOT NULL,
  `passport_no` varchar(30) DEFAULT NULL,
  `nationality` varchar(50) NOT NULL DEFAULT 'Bangladeshi',
  `mobile_no` varchar(20) NOT NULL,
  `division` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `upazila` varchar(50) DEFAULT NULL,
  `full_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL,
  `created_by_kazi_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`citizen_id`),
  UNIQUE KEY `national_id` (`national_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizens`
--

LOCK TABLES `citizens` WRITE;
/*!40000 ALTER TABLE `citizens` DISABLE KEYS */;
INSERT INTO `citizens` VALUES (1,'Bangladeshi','Rahim Ahmed','Abdul Ahmed','Rahima Khatun','1995-04-10','Male','Muslim','19951234567890123',NULL,'Bangladeshi','01811223344','Dhaka','Dhaka','Dhanmondi','Dhaka Address','2026-11-01 15:53:06',NULL,1),(2,'Bangladeshi','Fatema Begum','Kabir Hossain','Nasima Begum','2000-08-15','Female','Muslim','20001234567890123',NULL,'Bangladeshi','01911223344','Dhaka','Dhaka','Dhanmondi','Dhaka Address','2026-11-01 15:53:06',NULL,1),(3,'Bangladeshi','Akash Chu.....','mataleb chu...','mst. nasrin begum','1998-06-10','Male','Muslim','12345679890',NULL,'Bangladeshi','01565824655','Rangpur','Dinajpur','Dinajpur Sadar','dinajpor-7500','2026-11-01 17:13:05','uploads/citizens/cit_7b3c76a40bc5caf71af4.jpg',2),(4,'Bangladeshi','Rakibul Molla','abc','abdd','2000-10-06','Male','Muslim','6326545646546574514',NULL,'Bangladeshi','01565824655','Rajshahi','Rajshahi','Paba','PABA-7500','2026-11-06 15:39:00',NULL,2),(5,'Bangladeshi','Arifa Khanm','asf khan','asfl khanm','2002-11-14','Female','Muslim','2556655615',NULL,'Bangladeshi','01584856216','Khulna','Chuadanga','Chuadanga Sadar','chuadanga-7200','2026-11-07 15:34:50',NULL,2),(6,'Bangladeshi','Azad Khan','kjh khan','erina khan','2003-02-05','Male','Muslim','4554456532',NULL,'Bangladeshi','01585465558','Khulna','Satkhira','Shyamnagar','shat, khira-7800','2026-11-07 16:08:41',NULL,2),(7,'Bangladeshi','Nasim Sarkar','delorrrr sarkar','kjpkh sarkar','1999-05-05','Male','Muslim','3452345345',NULL,'Bangladeshi','01565485664','Khulna','Narail','Narail Sadar','norail-5600','2026-11-07 17:38:35',NULL,2),(8,'Bangladeshi','abul hosen','kalam hosen','mitali begum','2010-06-06','Male','Muslim','4454621651',NULL,'Bangladeshi','01585652324','Khulna','Magura','Magura Sadar','sajiyara-7110','2026-11-08 15:33:02',NULL,2),(9,'Bangladeshi','esy khatun','assa','jhfgj','2012-05-06','Female','Muslim','5465465532',NULL,'Bangladeshi','01546541616','Khulna','Magura','Mohammadpur','mohammadpur-7310','2026-11-08 15:40:26',NULL,2);
/*!40000 ALTER TABLE `citizens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divorce_records`
--

DROP TABLE IF EXISTS `divorce_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `divorce_records` (
  `divorce_id` int(11) NOT NULL AUTO_INCREMENT,
  `case_reference` varchar(50) NOT NULL,
  `marriage_registration_number` varchar(50) NOT NULL,
  `marriage_id` int(11) NOT NULL,
  `husband_name` varchar(100) NOT NULL,
  `wife_name` varchar(100) NOT NULL,
  `applicant_party` enum('Husband','Wife','Both (Mutual)') NOT NULL,
  `process_category` varchar(80) NOT NULL,
  `notice_date` date NOT NULL,
  `effective_date` date DEFAULT NULL,
  `process_status` enum('Notice Recorded','In Reconciliation','Completed / Effective','Withdrawn') DEFAULT 'Notice Recorded',
  `comments` text DEFAULT NULL,
  `supporting_document` varchar(255) DEFAULT NULL,
  `kazi_id` int(11) NOT NULL,
  `kazi_license_no` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`divorce_id`),
  UNIQUE KEY `case_reference` (`case_reference`),
  KEY `case_reference_2` (`case_reference`),
  KEY `marriage_registration_number` (`marriage_registration_number`),
  KEY `process_status` (`process_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divorce_records`
--

LOCK TABLES `divorce_records` WRITE;
/*!40000 ALTER TABLE `divorce_records` DISABLE KEYS */;
INSERT INTO `divorce_records` VALUES (1,'DIV-2026-455319','MR-2026-181848',1,'Rahim Ahmed','Fatema Begum','Husband','Talaq-e-Ahsan','2026-11-01','2026-11-01','Completed / Effective',NULL,NULL,1,'Dha_000002','2026-11-01 15:53:06'),(2,'DIV-2026-436586','MR-2026-287667',3,'Azad Khan','Arifa Khanm','Husband','Muslim Talaq Notice (Section 7 MFLO)','2026-11-07',NULL,'Withdrawn','উভয় পক্ষকে সালিশের জন্য নোটিশ প্রদান করা হয়েছে। নির্ধারিত তারিখে উভয় পক্ষের বক্তব্য শোনা হবে এবং পারস্পরিক সমঝোতার মাধ্যমে বিরোধ নিষ্পত্তির চেষ্টা করা হবে।\n[07-11-2026 17:29] Status changed to In Reconciliation: উভয় পক্ষকে বিষয়টি নিয়ে চিন্তাভাবনা ও পারস্পরিক আলোচনার জন্য পর্যাপ্ত সময় প্রদান করা হয়েছে। নির্ধারিত সময়ের মধ্যে তাদের সিদ্ধান্ত জানাতে বলা হয়েছে। পরবর্তী সিদ্ধান্তের ভিত্তিতে প্রয়োজনীয় ব্যবস্থা গ্রহণ করা হবে।\n[07-11-2026 17:31] Status changed to Withdrawn: উভয় পক্ষের আলোচনার পর চূড়ান্ত সিদ্ধান্ত অনুযায়ী বিবাহবিচ্ছেদ সম্পন্ন হয়েছে। প্রয়োজনীয় আনুষ্ঠানিকতা ও নথিপত্র সংরক্ষণের কার্যক্রম সম্পন্ন করা হয়েছে।',NULL,2,'Meh_000001','2026-11-07 16:26:16'),(3,'DIV-2026-729968','MR-2026-287667',3,'Azad Khan','Arifa Khanm','Husband','Muslim Talaq Notice (Section 7 MFLO)','2026-11-07','2026-11-07','Completed / Effective','উভয় পক্ষের আলোচনার পর চূড়ান্ত সিদ্ধান্ত অনুযায়ী বিবাহবিচ্ছেদ সম্পন্ন হয়েছে। প্রয়োজনীয় আনুষ্ঠানিকতা ও নথিপত্র সংরক্ষণের কার্যক্রম সম্পন্ন করা হয়েছে।',NULL,2,'Meh_000001','2026-11-07 16:35:11');
/*!40000 ALTER TABLE `divorce_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kazi_account_history`
--

DROP TABLE IF EXISTS `kazi_account_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kazi_account_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `kazi_id` int(11) NOT NULL,
  `action_type` varchar(24) NOT NULL,
  `previous_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `previous_expiry` datetime DEFAULT NULL,
  `new_expiry` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action_at` datetime NOT NULL,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `kazi_id` (`kazi_id`,`action_at`),
  KEY `admin_id` (`admin_id`,`action_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kazi_account_history`
--

LOCK TABLES `kazi_account_history` WRITE;
/*!40000 ALTER TABLE `kazi_account_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `kazi_account_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kazi_license_counters`
--

DROP TABLE IF EXISTS `kazi_license_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kazi_license_counters` (
  `district_prefix` varchar(10) NOT NULL,
  `last_serial` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`district_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kazi_license_counters`
--

LOCK TABLES `kazi_license_counters` WRITE;
/*!40000 ALTER TABLE `kazi_license_counters` DISABLE KEYS */;
INSERT INTO `kazi_license_counters` VALUES ('Chu',1),('Dha',1),('Meh',1);
/*!40000 ALTER TABLE `kazi_license_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kazi_users`
--

DROP TABLE IF EXISTS `kazi_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kazi_users` (
  `kazi_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `license_no` varchar(50) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `division` varchar(50) NOT NULL DEFAULT 'Dhaka',
  `district` varchar(50) NOT NULL DEFAULT 'Dhaka',
  `upazila` varchar(50) NOT NULL DEFAULT 'Dhanmondi',
  `full_address` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`kazi_id`),
  UNIQUE KEY `license_no` (`license_no`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `idx_kazi_mobile` (`mobile_no`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kazi_users`
--

LOCK TABLES `kazi_users` WRITE;
/*!40000 ALTER TABLE `kazi_users` DISABLE KEYS */;
INSERT INTO `kazi_users` VALUES (1,'Kazi Mohammad Test','Dha_000002','01799881122','test.kazi@example.com','$2y$10$NFqtqy2hYnt5ZxO0VPxJ6O6ezX/CAJX2bJtI2/PvyfHgB4.hkzADy','Dhaka','Dhaka','Dhanmondi','Test Address','Active','2026-11-01 15:53:06'),(2,'Asif khan','Meh_000001','01512345678','abc@gmail.com','$2y$10$xAwte3ypUYN8beoxaFlbJu4jn83SgnZQ.wQCyBqFtMoLC88QkBqQi','Khulna','Meherpur','Gangni','dhankhula, gangni, -7100','Active','2026-11-01 16:47:22');
/*!40000 ALTER TABLE `kazi_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marriages`
--

DROP TABLE IF EXISTS `marriages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marriages` (
  `marriage_id` int(11) NOT NULL AUTO_INCREMENT,
  `husband_identity_no` varchar(50) NOT NULL,
  `wife_identity_no` varchar(50) NOT NULL,
  `husband_name` varchar(150) NOT NULL,
  `wife_name` varchar(150) NOT NULL,
  `kazi_license_no` varchar(50) NOT NULL,
  `marriage_date` date NOT NULL,
  `religion` varchar(30) NOT NULL DEFAULT 'Muslim',
  `registration_number` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `husband_citizen_id` int(11) DEFAULT NULL,
  `husband_identity_type` enum('NID','Passport') NOT NULL DEFAULT 'NID',
  `wife_citizen_id` int(11) DEFAULT NULL,
  `wife_identity_type` enum('NID','Passport') NOT NULL DEFAULT 'NID',
  `kazi_id` int(11) DEFAULT NULL,
  `kazi_name` varchar(100) DEFAULT NULL,
  `witness1_name` varchar(100) NOT NULL DEFAULT '',
  `witness1_identity_type` enum('NID','Passport') NOT NULL DEFAULT 'NID',
  `witness1_identity_no` varchar(50) NOT NULL DEFAULT '',
  `witness1_mobile` varchar(20) DEFAULT NULL,
  `witness2_name` varchar(100) NOT NULL DEFAULT '',
  `witness2_identity_type` enum('NID','Passport') NOT NULL DEFAULT 'NID',
  `witness2_identity_no` varchar(50) NOT NULL DEFAULT '',
  `witness2_mobile` varchar(20) DEFAULT NULL,
  `denmohor_amount` decimal(12,2) DEFAULT 0.00,
  `registration_fee` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`marriage_id`),
  UNIQUE KEY `registration_number` (`registration_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marriages`
--

LOCK TABLES `marriages` WRITE;
/*!40000 ALTER TABLE `marriages` DISABLE KEYS */;
INSERT INTO `marriages` VALUES (1,'19951234567890123','20001234567890123','Rahim Ahmed','Fatema Begum','0','2026-11-01','Muslim','MR-2026-181848','Divorced','2026-11-01 15:53:06',1,'NID',2,'NID',1,'Kazi Mohammad Test','Witness One','NID','19801122334455667','01700112233','Witness Two','NID','19821122334455667','01700112244',500000.00,7000.00),(2,'12345679890','20001234567890123','Akash Chu.....','Fatema Begum','Meh_000001','2026-11-01','Muslim','MR-2026-906677','Active','2026-11-01 17:40:34',3,'NID',2,'NID',2,'Asif khan','abc','NID','1556482452','01654417567','acd','NID','46833045461','01655575786',500000.00,7000.00),(3,'4554456532','2556655615','Azad Khan','Arifa Khanm','Meh_000001','2026-11-07','Muslim','MR-2026-287667','Divorced','2026-11-07 16:12:22',6,'NID',5,'NID',2,'Asif khan','gsdd','NID','5644655845','','ccdd','NID','8576548654','',400000.00,5600.00),(4,'3452345345','2556655615','Nasim Sarkar','Arifa Khanm','Meh_000001','2026-11-07','Muslim','MR-2026-520456','Active','2026-11-07 17:40:29',7,'NID',5,'NID',2,'Asif khan','dfed','NID','5455645646','','sadfxc','NID','5656546543','',75000.00,1050.00);
/*!40000 ALTER TABLE `marriages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_no` varchar(50) NOT NULL,
  `service_category` enum('Muslim Marriage','Hindu Marriage','Other Marriage','Divorce Record') NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_status` enum('Paid','Pending') DEFAULT 'Paid',
  `kazi_license_no` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `receipt_no` (`receipt_no`),
  KEY `receipt_no_2` (`receipt_no`),
  KEY `reference_no` (`reference_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'RCT-2026-906677','Muslim Marriage','MR-2026-906677',7000.00,0.00,7000.00,'2026-11-01','Paid','Meh_000001','2026-11-01 17:40:34'),(2,'RCT-2026-287667','Muslim Marriage','MR-2026-287667',5600.00,0.00,5600.00,'2026-11-07','Paid','Meh_000001','2026-11-07 16:12:22'),(3,'RCT-DIV-2026-436586','Divorce Record','DIV-2026-436586',1000.00,0.00,1000.00,'2026-11-07','Paid','Meh_000001','2026-11-07 16:26:16'),(4,'RCT-DIV-2026-729968','Divorce Record','DIV-2026-729968',1000.00,0.00,1000.00,'2026-11-07','Paid','Meh_000001','2026-11-07 16:35:11'),(5,'RCT-2026-520456','Muslim Marriage','MR-2026-520456',1050.00,0.00,1050.00,'2026-11-07','Paid','Meh_000001','2026-11-07 17:40:29');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'General Notice',
  `content` text NOT NULL,
  `status` enum('Published','Draft') NOT NULL DEFAULT 'Published',
  `author_admin_id` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`post_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'বিবাহ নিবন্ধনের আইনি বাধ্যবাধকতা ও প্রয়োজনীয় নির্দেশিকা','বিবাহ আইন','মুসলিম বিবাহ ও তালাক (নিবন্ধন) আইন ১৯৭৪ অনুসারে বাংলাদেশে প্রতিটি বিবাহ যথাসময়ে নিবন্ধন করা বাধ্যতামূলক। বিবাহ নিবন্ধনের ফলে স্বামী ও স্ত্রী উভয়ের আইনগত অধিকার এবং দেনমোহরের দাবি সুপ্রতিষ্ঠিত হয়।','Published',1,'2026-02-15 04:00:00','2026-11-01 15:47:07'),(2,'দেনমোহর নির্ধারণ ও সরকারি নিবন্ধন ফি এর সঠিক হিসাব','ফি ও বিধিমালা','দেনমোহরের পরিমাণের ওপর ভিত্তি করে নির্ধারিত সরকারি ফি পরিশোধ করে কাজী কার্যালয় থেকে তাৎক্ষণিক মানি রিসিপ্ট সংগ্রহ করতে হবে। কোনো অননুমোদিত অতিরিক্ত ফি প্রদান আইনত দণ্ডনীয়।','Published',1,'2026-02-20 05:30:00','2026-11-01 15:47:07'),(3,'তালাক নোটিশ প্রদান ও ৯০ দিনের সমঝোতা বিধানাবলী','তালাক সংক্রান্ত','মুসলিম পারিবারিক আইন অধ্যাদেশ ১৯৬১ এর ধারা ৭ অনুযায়ী যেকোনো পক্ষ থেকে তালাক প্রদান করতে হলে স্থানীয় ইউপি চেয়ারম্যান বা সিটি মেয়র এবং অপর পক্ষকে লিখিত নোটিশ রেজিস্ট্রি ডাকযোগে প্রেরণ করতে হবে। নোটিশ প্রেরণের তারিখ থেকে ৯০ দিন অতিবাহিত হওয়ার পর তালাক কার্যকর হয়।','Published',1,'2026-02-25 08:15:00','2026-11-01 15:47:07'),(4,'অনলাইন বিবাহ নিবন্ধন সেবা সম্পর্কে গুরুত্বপূর্ণ বিজ্ঞপ্তি','সাধারণ নোটিশ','সম্মানিত ব্যবহারকারীগণ,\r\n\r\nআমাদের অনলাইন বিবাহ নিবন্ধন ও যাচাই ব্যবস্থার মাধ্যমে সেবা গ্রহণ করার জন্য আপনাদের ধন্যবাদ।\r\n\r\nসিস্টেমের কার্যকারিতা উন্নত করা এবং ব্যবহারকারীদের আরও সহজ ও নির্ভরযোগ্য সেবা প্রদানের লক্ষ্যে নিয়মিতভাবে সিস্টেমের রক্ষণাবেক্ষণ ও উন্নয়ন কার্যক্রম পরিচালনা করা হচ্ছে।\r\n\r\nবিবাহ নিবন্ধন সংক্রান্ত যেকোনো তথ্য বা সহায়তার জন্য সংশ্লিষ্ট কাজী অফিসের সঙ্গে যোগাযোগ করার অনুরোধ করা হলো।\r\n\r\nআপনাদের সহযোগিতা ও আস্থার জন্য ধন্যবাদ।\r\n\r\nপ্রকাশক: সিস্টেম প্রশাসক \r\nMd Shojib Ahmed','Published',1,'2026-11-03 13:40:27','2026-11-03 13:40:27');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'marriage_project'
--

--
-- Dumping routines for database 'marriage_project'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12 23:58:59
