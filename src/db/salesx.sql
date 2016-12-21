CREATE DATABASE  IF NOT EXISTS `salesx` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `salesx`;
-- MySQL dump 10.13  Distrib 5.7.14, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: salesx
-- ------------------------------------------------------
-- Server version	5.7.14

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Activity`
--

DROP TABLE IF EXISTS `Activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Activity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `category` enum('CALL','EMAIL','TASK','FFMEETING','WEBMEETING') NOT NULL,
  `lead_id` bigint(20) NOT NULL,
  `date` datetime NOT NULL,
  `reminder_time` int(5) NOT NULL,
  `owner_id` bigint(20) NOT NULL,
  `duration` int(5) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `category_id` varchar(45) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `status_change_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Activity_2_idx` (`owner_id`),
  KEY `fk_Activity_1_idx` (`lead_id`),
  CONSTRAINT `fk_Activity_1` FOREIGN KEY (`lead_id`) REFERENCES `Lead` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Activity_2` FOREIGN KEY (`owner_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Activity_Assigned_Mapping`
--

DROP TABLE IF EXISTS `Activity_Assigned_Mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Activity_Assigned_Mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Activity_Assigned_Mapping_1_idx` (`activity_id`),
  KEY `fk_Activity_Assigned_Mapping_2_idx` (`user_id`),
  CONSTRAINT `fk_Activity_Assigned_Mapping_1` FOREIGN KEY (`activity_id`) REFERENCES `Activity` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Activity_Assigned_Mapping_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Activity_Contact_Mapping`
--

DROP TABLE IF EXISTS `Activity_Contact_Mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Activity_Contact_Mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL,
  `contact_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Activity_Contact_Mapping_1_idx` (`activity_id`),
  KEY `fk_Activity_Contact_Mapping_2_idx` (`contact_id`),
  CONSTRAINT `fk_Activity_Contact_Mapping_1` FOREIGN KEY (`activity_id`) REFERENCES `Activity` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Activity_Contact_Mapping_2` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Activity_Copy`
--

DROP TABLE IF EXISTS `Activity_Copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Activity_Copy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Activity_Copy_1_idx` (`activity_id`),
  KEY `fk_Activity_Copy_2_idx` (`user_id`),
  CONSTRAINT `fk_Activity_Copy_1` FOREIGN KEY (`activity_id`) REFERENCES `Activity` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Activity_Copy_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Contact`
--

DROP TABLE IF EXISTS `Contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contact` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ph_id` varchar(100) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `designation` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `company_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `phone_number_array` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `email_id` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `social_data_id` bigint(20) DEFAULT NULL,
  `last_contacted_timestamp` varchar(45) DEFAULT NULL,
  `is_deleted` smallint(1) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  `contact_photo` varchar(250) DEFAULT NULL,
  `lead_id` bigint(20) DEFAULT NULL,
  `organization_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Contact_fk0` (`social_data_id`),
  KEY `fk_Contact_1_idx` (`updated_by`),
  KEY `fk_Contact_2_idx` (`lead_id`),
  KEY `fk_Contact_3_idx` (`organization_id`),
  CONSTRAINT `fk_Contact_1` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contact_2` FOREIGN KEY (`lead_id`) REFERENCES `Lead` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contact_3` FOREIGN KEY (`organization_id`) REFERENCES `Organization` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `After_Contact_Update` AFTER update ON `Contact`
FOR EACH ROW 
BEGIN
    IF NEW.name <> OLD.name THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'name',
         NEW.name,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.name, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
    IF NEW.designation <> OLD.designation THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'designation',
         NEW.designation,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.designation, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
    IF NEW.company_name <> OLD.company_name THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'company_name',
         NEW.company_name,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.company_name, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
    IF NEW.phone_number_array <> OLD.phone_number_array THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'phone_number_array',
         NEW.phone_number_array,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.phone_number_array, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
    IF NEW.email_id <> OLD.email_id THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'email_id',
         NEW.email_id,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.email_id, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
    IF NEW.social_data_id <> OLD.social_data_id THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'social_data_id',
         NEW.social_data_id,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.social_data_id, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
    IF NEW.last_contacted_timestamp <> OLD.last_contacted_timestamp THEN  
    INSERT INTO `salesx`.`Contact_Update_Log`
		(`contact_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'last_contacted_timestamp',
         NEW.last_contacted_timestamp,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.last_contacted_timestamp, 
        `updated_timestamp`=NEW.updated_date;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Contact_Interaction_History`
--

DROP TABLE IF EXISTS `Contact_Interaction_History`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contact_Interaction_History` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) NOT NULL,
  `interaction_count` int(20) DEFAULT NULL,
  `last_contacted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Contact_Interaction_History_1` (`contact_id`),
  CONSTRAINT `fk_Contact_Interaction_History_1` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Contact_Social_Data`
--

DROP TABLE IF EXISTS `Contact_Social_Data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contact_Social_Data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `data` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Contact_Update_Log`
--

DROP TABLE IF EXISTS `Contact_Update_Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contact_Update_Log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) NOT NULL,
  `updated_field` varchar(100) NOT NULL,
  `updated_value` varchar(500) NOT NULL,
  `updated_timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UniqueKey` (`contact_id`,`updated_field`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Deal`
--

DROP TABLE IF EXISTS `Deal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Deal` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `value` int(50) NOT NULL,
  `payment_type` enum('MONTHLY','YEARLY') CHARACTER SET utf8 NOT NULL,
  `estimated_closure_date` datetime NOT NULL,
  `owner_id` bigint(20) NOT NULL,
  `confidence_index` int(5) NOT NULL DEFAULT '50',
  `description` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lead_id` bigint(20) NOT NULL,
  `pipeline_stage` bigint(20) NOT NULL,
  `pipeline_id` bigint(20) NOT NULL,
  `rotten_flag` tinyint(1) NOT NULL,
  `status` enum('LOST','ACTIVE','WON') CHARACTER SET utf8 NOT NULL,
  `is_deleted` smallint(1) NOT NULL DEFAULT '0',
  `updated_by` bigint(20) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status_update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `Deal_fk4` (`pipeline_id`),
  KEY `fk_Deal_1_idx` (`pipeline_stage`),
  KEY `fk_Deal_2_idx` (`updated_by`),
  CONSTRAINT `Deal_fk4` FOREIGN KEY (`pipeline_id`) REFERENCES `Pipeline` (`id`),
  CONSTRAINT `fk_Deal_1` FOREIGN KEY (`pipeline_stage`) REFERENCES `Pipeline_Stages` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Deal_2` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Deal_Contacts_Mapping`
--

DROP TABLE IF EXISTS `Deal_Contacts_Mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Deal_Contacts_Mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `deal_id` bigint(20) NOT NULL,
  `contact_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Deal_Contacts_Mapping_fk0` (`deal_id`),
  CONSTRAINT `Deal_Contacts_Mapping_fk0` FOREIGN KEY (`deal_id`) REFERENCES `Deal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Email_Account_Settings`
--

DROP TABLE IF EXISTS `Email_Account_Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Email_Account_Settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `type` enum('GMAIL','OTHER') NOT NULL,
  `type_id` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Imap_Settings_1_idx` (`user_id`),
  CONSTRAINT `fk_Imap_Settings_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Email_Reminders`
--

DROP TABLE IF EXISTS `Email_Reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Email_Reminders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `message_id` varchar(500) NOT NULL,
  `sxuid` varchar(45) NOT NULL,
  `email_settings_id` bigint(20) DEFAULT NULL,
  `date` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Email_Reminders_1_idx` (`user_id`),
  KEY `fk_Email_Reminders_2_idx` (`email_settings_id`),
  CONSTRAINT `fk_Email_Reminders_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Email_Reminders_2` FOREIGN KEY (`email_settings_id`) REFERENCES `Email_Account_Settings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Email_Settings`
--

DROP TABLE IF EXISTS `Email_Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Email_Settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `email_bcc` varchar(450) DEFAULT NULL,
  `email_signature` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Encryption_Keymap`
--

DROP TABLE IF EXISTS `Encryption_Keymap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Encryption_Keymap` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `encryption_key` varchar(15) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `encryption_key_UNIQUE` (`encryption_key`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Features`
--

DROP TABLE IF EXISTS `Features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Features` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Followup_Assignees`
--

DROP TABLE IF EXISTS `Followup_Assignees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Followup_Assignees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `followup_id` bigint(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Followup_Assignees_fk0` (`followup_id`),
  CONSTRAINT `Followup_Assignees_fk0` FOREIGN KEY (`followup_id`) REFERENCES `Followups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Followup_Watchers`
--

DROP TABLE IF EXISTS `Followup_Watchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Followup_Watchers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `followup_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Followup_Watchers_fk0` (`followup_id`),
  CONSTRAINT `Followup_Watchers_fk0` FOREIGN KEY (`followup_id`) REFERENCES `Followups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Followups`
--

DROP TABLE IF EXISTS `Followups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Followups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `category` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Followups_fk0` (`user_id`),
  CONSTRAINT `Followups_fk0` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Google_Access_Tokens`
--

DROP TABLE IF EXISTS `Google_Access_Tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Google_Access_Tokens` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `google_access_token` varchar(450) NOT NULL,
  `email_id` varchar(45) NOT NULL,
  `primary_id` tinyint(1) NOT NULL DEFAULT '0',
  `fetch_lock` tinyint(1) NOT NULL DEFAULT '0',
  `lock_update_time` datetime DEFAULT NULL,
  `initial_fetch` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_Google_Access_Tokens_1_idx` (`user_id`),
  CONSTRAINT `fk_Google_Access_Tokens_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Huddle_Goals`
--

DROP TABLE IF EXISTS `Huddle_Goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Huddle_Goals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `goal` varchar(450) NOT NULL,
  `date` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_Huddle_Goals_1_idx` (`user_id`),
  CONSTRAINT `fk_Huddle_Goals_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Imap_Smtp_Credentials`
--

DROP TABLE IF EXISTS `Imap_Smtp_Credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Imap_Smtp_Credentials` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `imap_port` varchar(50) NOT NULL,
  `imap_host` varchar(100) NOT NULL,
  `imap_encryption` varchar(3) DEFAULT NULL,
  `smtp_port` varchar(50) NOT NULL,
  `smtp_host` varchar(100) NOT NULL,
  `smtp_encryption` varchar(3) DEFAULT NULL,
  `primary_id` tinyint(1) NOT NULL DEFAULT '0',
  `fetch_lock` tinyint(1) NOT NULL DEFAULT '0',
  `lock_update_time` datetime DEFAULT NULL,
  `initial_fetch` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_Imap_Credentials_1_idx` (`user_id`),
  CONSTRAINT `fk_Imap_Credentials_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead`
--

DROP TABLE IF EXISTS `Lead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `organisation_id` bigint(20) NOT NULL,
  `phone_number` varchar(45) DEFAULT NULL,
  `city` varchar(45) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  `is_deleted` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `Lead_fk0` (`organisation_id`),
  KEY `fk_Lead_1_idx` (`owner_id`),
  KEY `fk_Lead_2_idx` (`updated_by`),
  CONSTRAINT `Lead_fk0` FOREIGN KEY (`organisation_id`) REFERENCES `Organization` (`id`),
  CONSTRAINT `fk_Lead_1` FOREIGN KEY (`owner_id`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Lead_2` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `After_Lead_Update` AFTER update ON `Lead`
FOR EACH ROW 
BEGIN
    IF NEW.name <> OLD.name THEN  
    INSERT INTO `salesx`.`Lead_Update_Log`
		(`lead_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'name',
         NEW.name,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.name, 
        `updated_timestamp`=NEW.updated_date;
	END IF;
    IF NEW.status <> OLD.status THEN  
    INSERT INTO `salesx`.`Lead_Update_Log`
		(`lead_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'status',
         NEW.status,
         NEW.updated_date)
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.status, 
        `updated_timestamp`=NEW.updated_date;
	END IF;
    IF NEW.url <> OLD.url THEN  
    INSERT INTO `salesx`.`Lead_Update_Log`
		(`lead_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'url',
         NEW.url,
         NEW.updated_date)
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.url, 
        `updated_timestamp`=NEW.updated_date;
	END IF;
    IF NEW.phone_number <> OLD.phone_number THEN  
    INSERT INTO `salesx`.`Lead_Update_Log`
		(`lead_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'phone_number',
         NEW.phone_number,
         NEW.updated_date)
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.phone_number, 
        `updated_timestamp`=NEW.updated_date;
	END IF;
    IF NEW.city <> OLD.city THEN  
    INSERT INTO `salesx`.`Lead_Update_Log`
		(`lead_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'city',
         NEW.city,
         NEW.updated_date) 
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.city, 
        `updated_timestamp`=NEW.updated_date;
	END IF;
    IF NEW.address <> OLD.address THEN  
    INSERT INTO `salesx`.`Lead_Update_Log`
		(`lead_id`,
		 `updated_field`,
         `updated_value`,
		 `updated_timestamp`)
	VALUES
		(NEW.id,
		 'address',
         NEW.address,
         NEW.updated_date)
	ON DUPLICATE KEY UPDATE    
		`updated_value`=NEW.address, 
        `updated_timestamp`=NEW.updated_date;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Lead_Contact_Mapping`
--

DROP TABLE IF EXISTS `Lead_Contact_Mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Contact_Mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) NOT NULL,
  `contact_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`lead_id`,`contact_id`),
  KEY `Lead_Contact_Mapping_fk1` (`contact_id`),
  KEY `fk_Lead_Contact_Mapping_1_idx` (`lead_id`),
  CONSTRAINT `Lead_Contact_Mapping_fk1` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`id`),
  CONSTRAINT `fk_Lead_Contact_Mapping_1` FOREIGN KEY (`lead_id`) REFERENCES `Lead` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead_Custom_Field_Settings`
--

DROP TABLE IF EXISTS `Lead_Custom_Field_Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Custom_Field_Settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) NOT NULL,
  `field_name` varchar(45) NOT NULL,
  `options` varchar(450) DEFAULT NULL,
  `type` enum('TEXT','OPTIONS','DATE') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead_Custom_Fields`
--

DROP TABLE IF EXISTS `Lead_Custom_Fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Custom_Fields` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) NOT NULL,
  `custom_settings_id` bigint(20) NOT NULL,
  `value` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Lead_Custom_Fields_1_idx` (`custom_settings_id`),
  CONSTRAINT `fk_Lead_Custom_Fields_1` FOREIGN KEY (`custom_settings_id`) REFERENCES `Lead_Custom_Field_Settings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead_Custom_Status`
--

DROP TABLE IF EXISTS `Lead_Custom_Status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Custom_Status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) NOT NULL,
  `custom_status_id` bigint(20) NOT NULL,
  `value` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Lead_Custom_Status_1_idx` (`custom_status_id`),
  CONSTRAINT `fk_Lead_Custom_Status_1` FOREIGN KEY (`custom_status_id`) REFERENCES `Lead_Custom_Status_Settings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead_Custom_Status_Settings`
--

DROP TABLE IF EXISTS `Lead_Custom_Status_Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Custom_Status_Settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) NOT NULL,
  `status` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead_Interaction_History`
--

DROP TABLE IF EXISTS `Lead_Interaction_History`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Interaction_History` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) NOT NULL,
  `interaction_count` int(20) DEFAULT NULL,
  `last_contacted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Lead_Interaction_History_1` (`lead_id`),
  CONSTRAINT `fk_Lead_Interaction_History_1` FOREIGN KEY (`lead_id`) REFERENCES `Lead` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lead_Update_Log`
--

DROP TABLE IF EXISTS `Lead_Update_Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lead_Update_Log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) NOT NULL,
  `updated_field` varchar(100) NOT NULL,
  `updated_value` varchar(500) NOT NULL,
  `updated_timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UniqueKey` (`lead_id`,`updated_field`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Login_History`
--

DROP TABLE IF EXISTS `Login_History`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Login_History` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `Login_History_fk0` (`user_id`),
  CONSTRAINT `Login_History_fk0` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Meeting`
--

DROP TABLE IF EXISTS `Meeting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Meeting` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Meeting_fk0` (`user_id`),
  CONSTRAINT `Meeting_fk0` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Notes`
--

DROP TABLE IF EXISTS `Notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Notes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `note_category` enum('LEAD','CONTACT','DEAL','ACTIVITY') CHARACTER SET utf8 NOT NULL,
  `category_id` bigint(20) NOT NULL,
  `value` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Numbers`
--

DROP TABLE IF EXISTS `Numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Numbers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `number` bigint(50) NOT NULL,
  `number_name` varchar(45) DEFAULT NULL,
  `country_code` int(20) DEFAULT NULL,
  `area_code` int(20) DEFAULT NULL,
  `in_use` tinyint(1) NOT NULL,
  `purchase_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL,
  `incoming_call_rate_getting_price` varchar(45) NOT NULL,
  `incoming_call_rate_selling_price` varchar(45) NOT NULL,
  `outgoing_call_rate_getting_price` varchar(45) NOT NULL,
  `outgoing_call_rate_selling_price` varchar(45) NOT NULL,
  `monthly_rent_getting_price` varchar(45) NOT NULL,
  `monthly_rent_selling_price` varchar(45) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  `did_activation_fee` int(20) DEFAULT NULL,
  `vendor` varchar(45) NOT NULL,
  `number_sid` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='latin1_swedish_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Opportunity_Stages`
--

DROP TABLE IF EXISTS `Opportunity_Stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Opportunity_Stages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_on` timestamp NOT NULL,
  `rotting_time` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Opportunity_Stages_fk0` (`created_by`),
  CONSTRAINT `Opportunity_Stages_fk0` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Organization`
--

DROP TABLE IF EXISTS `Organization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Organization` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` bigint(20) NOT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `Organization_fk0` (`owner_id`),
  KEY `Organization_fk1` (`package_id`),
  CONSTRAINT `Organization_fk0` FOREIGN KEY (`owner_id`) REFERENCES `User` (`id`),
  CONSTRAINT `Organization_fk1` FOREIGN KEY (`package_id`) REFERENCES `Package_groups` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Organization_User_Mapping`
--

DROP TABLE IF EXISTS `Organization_User_Mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Organization_User_Mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Organization_User_Mapping_fk0` (`organization_id`),
  KEY `Organization_User_Mapping_fk1` (`user_id`),
  CONSTRAINT `Organization_User_Mapping_fk0` FOREIGN KEY (`organization_id`) REFERENCES `Organization` (`id`),
  CONSTRAINT `Organization_User_Mapping_fk1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Package_groups`
--

DROP TABLE IF EXISTS `Package_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Package_groups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group_description` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Packages`
--

DROP TABLE IF EXISTS `Packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Packages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `key_value_pairs` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `feature_list` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `package_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `package_description` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Packages_fk0` (`group_id`),
  CONSTRAINT `Packages_fk0` FOREIGN KEY (`group_id`) REFERENCES `Package_groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Pipeline`
--

DROP TABLE IF EXISTS `Pipeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Pipeline` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` bigint(20) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `organization_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Pipeline_fk0` (`created_by`),
  KEY `Pipeline_fk1` (`organization_id`),
  CONSTRAINT `Pipeline_fk0` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`),
  CONSTRAINT `Pipeline_fk1` FOREIGN KEY (`organization_id`) REFERENCES `Organization` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Pipeline_Stages`
--

DROP TABLE IF EXISTS `Pipeline_Stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Pipeline_Stages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pipeline_id` bigint(20) NOT NULL,
  `sequence_number` int(11) NOT NULL,
  `stage` varchar(45) NOT NULL,
  `expiry_period` int(11) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_Pipeline_Stages_1_idx` (`pipeline_id`),
  CONSTRAINT `fk_Pipeline_Stages_1` FOREIGN KEY (`pipeline_id`) REFERENCES `Pipeline` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Rate_Sheet_Default`
--

DROP TABLE IF EXISTS `Rate_Sheet_Default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Rate_Sheet_Default` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `prefix` int(10) NOT NULL,
  `country` varchar(100) NOT NULL,
  `country_code` int(5) NOT NULL,
  `city` varchar(100) NOT NULL,
  `city_code` int(5) NOT NULL,
  `outgoing_call_rate` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Search_Query`
--

DROP TABLE IF EXISTS `Search_Query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Search_Query` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `search` varchar(600) CHARACTER SET dec8 DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `query_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Timeline`
--

DROP TABLE IF EXISTS `Timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Timeline` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `lead_id` bigint(20) DEFAULT NULL,
  `lead_name` varchar(50) DEFAULT NULL,
  `contact_id` bigint(20) DEFAULT NULL,
  `contact_name` varchar(50) DEFAULT NULL,
  `type` enum('CALL','MAIL','ACTIVITY') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type_id` varchar(50) NOT NULL,
  `header` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(300) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Timeline_fk2` (`contact_id`),
  KEY `Timeline_fk0` (`user_id`),
  CONSTRAINT `Timeline_fk0` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`),
  CONSTRAINT `Timeline_fk2` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Twilio_Monthly_Price`
--

DROP TABLE IF EXISTS `Twilio_Monthly_Price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Twilio_Monthly_Price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(10) NOT NULL,
  `type` varchar(45) NOT NULL,
  `price` int(20) NOT NULL,
  `price_unit` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Twilio_Numbers`
--

DROP TABLE IF EXISTS `Twilio_Numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Twilio_Numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numbers_id` bigint(20) NOT NULL,
  `friendly_name` varchar(45) DEFAULT NULL,
  `phone_number` varchar(45) NOT NULL,
  `lata` varchar(45) DEFAULT NULL,
  `rate_center` varchar(45) DEFAULT NULL,
  `latitude` varchar(45) DEFAULT NULL,
  `longitude` varchar(45) DEFAULT NULL,
  `region` varchar(45) DEFAULT NULL,
  `postal_code` varchar(45) DEFAULT NULL,
  `iso_country` varchar(45) NOT NULL,
  `address_requirements` varchar(45) DEFAULT NULL,
  `beta` varchar(45) DEFAULT NULL,
  `capabilities` varchar(150) DEFAULT NULL,
  `type` bigint(20) NOT NULL,
  PRIMARY KEY (`id`,`iso_country`),
  KEY `fk_Twilio_Numbers_1_idx` (`numbers_id`),
  CONSTRAINT `fk_Twilio_Numbers_1` FOREIGN KEY (`numbers_id`) REFERENCES `Numbers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `role_id` int(20) NOT NULL,
  `password` varchar(100) CHARACTER SET utf8 NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name_UNIQUE` (`user_name`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `User_Profile`
--

DROP TABLE IF EXISTS `User_Profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User_Profile` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `middle_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `profile_photo` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `designation` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `mail_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `contact_numbers` varchar(450) DEFAULT NULL,
  `salesx_number` bigint(50) DEFAULT NULL,
  `record_calls` tinyint(1) NOT NULL DEFAULT '1',
  `number_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `organization_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `User_Profile_fk0` (`user_id`),
  CONSTRAINT `User_Profile_fk0` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `User_Roles`
--

DROP TABLE IF EXISTS `User_Roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User_Roles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `role` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'salesx'
--

--
-- Dumping routines for database 'salesx'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-16 14:24:42
