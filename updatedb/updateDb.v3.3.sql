-- MySQL Workbench Synchronization
-- Generated: 2017-07-04 10:03
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `configurations` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
CHANGE COLUMN `mode` `mode` ENUM('Youtube', 'Gallery') NULL DEFAULT 'Youtube' ,
ADD COLUMN `smtp` TINYINT(1) NULL DEFAULT NULL AFTER `doNotShowCategories`,
ADD COLUMN `smtpAuth` TINYINT(1) NULL DEFAULT NULL AFTER `smtp`,
ADD COLUMN `smtpSecure` VARCHAR(45) NULL DEFAULT NULL COMMENT '\'ssl\'; // secure transfer enabled REQUIRED for Gmail' AFTER `smtpAuth`,
ADD COLUMN `smtpHost` VARCHAR(100) NULL DEFAULT NULL COMMENT '\"smtp.gmail.com\"' AFTER `smtpSecure`,
ADD COLUMN `smtpUsername` VARCHAR(45) NULL DEFAULT NULL COMMENT '\"email@gmail.com\"' AFTER `smtpHost`,
ADD COLUMN `smtpPassword` VARCHAR(45) NULL DEFAULT NULL AFTER `smtpUsername`,
ADD COLUMN `smtpPort` INT(11) NULL DEFAULT NULL AFTER `smtpPassword`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


UPDATE configurations SET  version = '3.3', modified = now() WHERE id = 1;