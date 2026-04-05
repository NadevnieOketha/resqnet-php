-- resqnet Database Schema
-- Run this file against your MySQL database to set up the tables.

CREATE DATABASE IF NOT EXISTS resqnet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE resqnet;

-- 1. BASE TABLES (No Foreign Key Dependencies)
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('general','volunteer','ngo','grama_niladhari','dmc') NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `donation_items_catalog` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `category` enum('Medicine','Food','Shelter') NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `uq_item_name` (`item_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `skills` (
  `skill_id` int NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(100) NOT NULL,
  PRIMARY KEY (`skill_id`),
  UNIQUE KEY `uq_skills_name` (`skill_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `volunteer_preferences` (
  `preference_id` int NOT NULL AUTO_INCREMENT,
  `preference_name` varchar(100) NOT NULL,
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `uq_preferences_name` (`preference_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `safe_locations` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) NOT NULL,
  `address_house_no` varchar(50) DEFAULT NULL,
  `address_street` varchar(120) DEFAULT NULL,
  `address_city` varchar(120) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `gn_division` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `max_capacity` int NOT NULL DEFAULT '0',
  `assigned_gn_user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `safe_location_occupancy` (
  `location_id` int NOT NULL,
  `toddlers` int NOT NULL DEFAULT '0',
  `children` int NOT NULL DEFAULT '0',
  `adults` int NOT NULL DEFAULT '0',
  `elderly` int NOT NULL DEFAULT '0',
  `pregnant_women` int NOT NULL DEFAULT '0',
  `updated_by_user_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- 2. USER PROFILE TABLES (Depend on 'users')
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS `general_user` (
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `house_no` varchar(50) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `gn_division` varchar(100) DEFAULT NULL,
  `sms_alert` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_general_user_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `ngos` (
  `user_id` int NOT NULL,
  `organization_name` varchar(150) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `years_of_operation` int DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_person_name` varchar(100) DEFAULT NULL,
  `contact_person_telephone` varchar(20) DEFAULT NULL,
  `contact_person_email` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_ngos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `volunteers` (
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `age` int DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `house_no` varchar(50) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `gn_division` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_volunteers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `grama_niladhari` (
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gn_division` varchar(100) DEFAULT NULL,
  `service_number` varchar(50) DEFAULT NULL,
  `gn_division_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_gn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `token` varchar(64) NOT NULL,
  `user_id` int NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  KEY `idx_prt_user_expires` (`user_id`,`expires_at`),
  CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- 3. INFRASTRUCTURE TABLES (Depend on 'ngos')
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS `collection_points` (
  `collection_point_id` int NOT NULL AUTO_INCREMENT,
  `ngo_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `location_landmark` varchar(150) DEFAULT NULL,
  `full_address` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`collection_point_id`),
  CONSTRAINT `fk_collection_point_ngo` FOREIGN KEY (`ngo_id`) REFERENCES `ngos` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- 4. CORE MODULE TABLES (Depend on Profiles and Infrastructure)
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS disaster_reports (
  report_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,

  reporter_name VARCHAR(100) NOT NULL,
  contact_number VARCHAR(20) NOT NULL,

  disaster_type ENUM('Flood','Landslide','Fire','Earthquake','Tsunami','Other') NOT NULL,
  other_disaster_type VARCHAR(100) DEFAULT NULL,

  disaster_datetime DATETIME NOT NULL,

  location VARCHAR(255),
  district VARCHAR(100) NOT NULL,
  gn_division VARCHAR(150) NOT NULL,

  proof_image_path VARCHAR(255) DEFAULT NULL,

  confirmation TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',

  description TEXT,

  submitted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  verified_at TIMESTAMP NULL DEFAULT NULL,

  PRIMARY KEY (report_id),

  CONSTRAINT fk_disaster_report_user 
  FOREIGN KEY (user_id) REFERENCES general_user(user_id) 
  ON DELETE CASCADE
) ENGINE=INNODB;


CREATE TABLE IF NOT EXISTS `donations` (
  `donation_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `collection_point_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `collection_date` date NOT NULL,
  `time_slot` enum('9am–12pm','12pm–4pm','6pm–9pm') NOT NULL,
  `special_notes` text,
  `confirmation` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('Pending','Received','Cancelled','Delivered') DEFAULT 'Pending',
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `received_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`donation_id`),
  CONSTRAINT `fk_donations_collection_point` FOREIGN KEY (`collection_point_id`) REFERENCES `collection_points` (`collection_point_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_donations_user` FOREIGN KEY (`user_id`) REFERENCES `general_user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donation_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `safe_location_id` int DEFAULT NULL,
  `assigned_gn_user_id` int DEFAULT NULL,
  `relief_center_name` varchar(150) NOT NULL,
  `status` enum('Pending','Approved') DEFAULT 'Pending',
  `processing_status` enum('requested','requirement_gathered','fulfilled') NOT NULL DEFAULT 'requested',
  `special_notes` text,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `situation` text,
  PRIMARY KEY (`request_id`),
  KEY `idx_donation_requests_safe_location` (`safe_location_id`),
  KEY `idx_donation_requests_assigned_gn` (`assigned_gn_user_id`),
  KEY `idx_donation_requests_processing_status` (`processing_status`),
  CONSTRAINT `fk_donation_request_user` FOREIGN KEY (`user_id`) REFERENCES `general_user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `volunteer_task` (
  `id` int NOT NULL AUTO_INCREMENT,
  `volunteer_id` int NOT NULL,
  `disaster_id` int NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `date_assigned` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT 'assigned',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `volunteer_field_updates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `volunteer_id` int NOT NULL,
  `stage_status` varchar(50) DEFAULT NULL,
  `update_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vfu_task` (`task_id`),
  KEY `idx_vfu_volunteer` (`volunteer_id`)
) ENGINE=InnoDB;


-- 5. MAPPING & LOG TABLES (Many-to-Many Relationships)
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int NOT NULL AUTO_INCREMENT,
  `ngo_id` int NOT NULL,
  `collection_point_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int DEFAULT '0',
  `status` enum('In Stock','Low on Stock','Out of Stock') GENERATED ALWAYS AS (
    (case when (`quantity` = 0) then _utf8mb4'Out of Stock' 
          when (`quantity` < 20) then _utf8mb4'Low on Stock' 
          else _utf8mb4'In Stock' end)
  ) STORED,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inventory_id`),
  UNIQUE KEY `uq_inventory_ngo_cp_item` (`ngo_id`,`collection_point_id`,`item_id`),
  CONSTRAINT `fk_inventory_collection_point` FOREIGN KEY (`collection_point_id`) REFERENCES `collection_points` (`collection_point_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_item` FOREIGN KEY (`item_id`) REFERENCES `donation_items_catalog` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_ngo` FOREIGN KEY (`ngo_id`) REFERENCES `ngos` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donation_inventory_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `donation_id` int NOT NULL,
  `item_id` int NOT NULL,
  `collection_point_id` int NOT NULL,
  `quantity` int NOT NULL,
  `action` enum('Received','Updated') NOT NULL,
  `logged_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  CONSTRAINT `fk_log_collection` FOREIGN KEY (`collection_point_id`) REFERENCES `collection_points` (`collection_point_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`donation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_item` FOREIGN KEY (`item_id`) REFERENCES `donation_items_catalog` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donation_items` (
  `donation_item_id` int NOT NULL AUTO_INCREMENT,
  `donation_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  PRIMARY KEY (`donation_item_id`),
  CONSTRAINT `fk_donation_items_catalog_item` FOREIGN KEY (`item_id`) REFERENCES `donation_items_catalog` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_donation_items_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`donation_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donation_request_items` (
  `request_item_id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  PRIMARY KEY (`request_item_id`),
  CONSTRAINT `fk_donation_items_catalog` FOREIGN KEY (`item_id`) REFERENCES `donation_items_catalog` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_donation_items_request` FOREIGN KEY (`request_id`) REFERENCES `donation_requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donation_request_requirements` (
  `requirement_id` int NOT NULL AUTO_INCREMENT,
  `location_id` int NOT NULL,
  `gn_user_id` int NOT NULL,
  `relief_center_name` varchar(150) NOT NULL,
  `location_label` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `situation_description` text NOT NULL,
  `special_notes` text,
  `days_count` int NOT NULL DEFAULT '1',
  `packs_toddlers` int NOT NULL DEFAULT '0',
  `packs_children` int NOT NULL DEFAULT '0',
  `packs_adults` int NOT NULL DEFAULT '0',
  `packs_elderly` int NOT NULL DEFAULT '0',
  `packs_pregnant_women` int NOT NULL DEFAULT '0',
  `status` enum('Gathered','Fulfilled') NOT NULL DEFAULT 'Gathered',
  `fulfillment_status` enum('Open','Reserved','Fulfilled') NOT NULL DEFAULT 'Open',
  `reserved_by_ngo_user_id` int DEFAULT NULL,
  `reserved_at` timestamp NULL DEFAULT NULL,
  `fulfilled_by_gn_user_id` int DEFAULT NULL,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`requirement_id`),
  KEY `idx_drr_location` (`location_id`),
  KEY `idx_drr_status` (`status`),
  KEY `idx_drr_fulfillment_status` (`fulfillment_status`),
  KEY `idx_drr_reserved_ngo` (`reserved_by_ngo_user_id`),
  KEY `idx_drr_created_at` (`created_at`),
  CONSTRAINT `fk_drr_location` FOREIGN KEY (`location_id`) REFERENCES `safe_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_drr_gn` FOREIGN KEY (`gn_user_id`) REFERENCES `grama_niladhari` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_drr_reserved_ngo` FOREIGN KEY (`reserved_by_ngo_user_id`) REFERENCES `ngos` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_drr_fulfilled_gn` FOREIGN KEY (`fulfilled_by_gn_user_id`) REFERENCES `grama_niladhari` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donation_request_requirement_items` (
  `requirement_item_id` int NOT NULL AUTO_INCREMENT,
  `requirement_id` int NOT NULL,
  `item_category` enum('Medicine','Food','Shelter') NOT NULL,
  `item_name` varchar(160) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unit` varchar(30) NOT NULL DEFAULT 'units',
  `source` enum('pack','extra') NOT NULL DEFAULT 'pack',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`requirement_item_id`),
  KEY `idx_drri_requirement` (`requirement_id`),
  KEY `idx_drri_category` (`item_category`),
  CONSTRAINT `fk_drri_requirement` FOREIGN KEY (`requirement_id`) REFERENCES `donation_request_requirements` (`requirement_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `skills_volunteers` (
  `user_id` int NOT NULL,
  `skill_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`skill_id`),
  CONSTRAINT `fk_skills_volunteers_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_skills_volunteers_volunteer` FOREIGN KEY (`user_id`) REFERENCES `volunteers` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `volunteer_preference_volunteers` (
  `user_id` int NOT NULL,
  `preference_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`preference_id`),
  CONSTRAINT `fk_vpv_preference` FOREIGN KEY (`preference_id`) REFERENCES `volunteer_preferences` (`preference_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vpv_volunteer` FOREIGN KEY (`user_id`) REFERENCES `volunteers` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;