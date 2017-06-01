-- MySQL Workbench Synchronization
-- Generated: 2017-06-01 09:30
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `configurations` 
ADD COLUMN `ffmpegSpectrum` VARCHAR(400) NULL DEFAULT NULL AFTER `ffmpegMp3`,
ADD COLUMN `disable_analytics` TINYINT(1) NULL DEFAULT 0 AFTER `mode`,
ADD COLUMN `session_timeout` INT(11) NULL DEFAULT 3600 AFTER `disable_analytics`,
ADD COLUMN `encode_mp4` TINYINT(1) NULL DEFAULT 1 AFTER `session_timeout`,
ADD COLUMN `encode_webm` TINYINT(1) NULL DEFAULT 1 AFTER `encode_mp4`,
ADD COLUMN `autoplay` TINYINT(1) NULL DEFAULT 1 AFTER `encode_webm`,
ADD COLUMN `encode_mp3spectrum` TINYINT(1) NULL DEFAULT 1 AFTER `autoplay`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;



UPDATE configurations SET  version = '2.8', modified = now() WHERE id = 1;
