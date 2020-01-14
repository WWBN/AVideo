-- MySQL Workbench Synchronization
-- Generated: 2017-06-12 12:48
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `configurations` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
ADD COLUMN `theme` VARCHAR(45) NULL DEFAULT 'default' AFTER `autoplay`,
ADD COLUMN `doNotShowVideoAndAudioLinks` TINYINT(1) NULL DEFAULT NULL AFTER `theme`,
ADD COLUMN `doNotShowCategories` TINYINT(1) NULL DEFAULT NULL AFTER `doNotShowVideoAndAudioLinks`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

UPDATE configurations SET  version = '3.1', modified = now() WHERE id = 1;