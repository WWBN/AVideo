SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER SCHEMA `youPHPTube`  DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci ;

ALTER TABLE `users` 
CHANGE COLUMN `user` `user` VARCHAR(45) NOT NULL ,
CHANGE COLUMN `name` `name` VARCHAR(45) NULL DEFAULT NULL ,
CHANGE COLUMN `email` `email` VARCHAR(45) NULL DEFAULT NULL ,
CHANGE COLUMN `password` `password` VARCHAR(45) NOT NULL ;

ALTER TABLE `videos` 
CHANGE COLUMN `title` `title` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `clean_title` `clean_title` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `status` `status` ENUM('a', 'i', 'e', 'x', 'd') NOT NULL DEFAULT 'e' COMMENT 'a = active\ni = inactive\ne = encoding\nx = encoding error\nd = downloading' ,
CHANGE COLUMN `duration` `duration` VARCHAR(15) NOT NULL ,
CHANGE COLUMN `type` `type` ENUM('audio', 'video') NOT NULL DEFAULT 'video' ;

ALTER TABLE `comments` 
CHANGE COLUMN `comment` `comment` VARCHAR(255) NOT NULL ;

ALTER TABLE `categories` 
CHANGE COLUMN `name` `name` VARCHAR(45) NOT NULL ,
CHANGE COLUMN `clean_name` `clean_name` VARCHAR(45) NOT NULL ,
CHANGE COLUMN `created` `created` DATETIME NOT NULL DEFAULT now() ,
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL DEFAULT now() ;

ALTER TABLE `configurations` 
CHANGE COLUMN `video_resolution` `video_resolution` VARCHAR(12) NOT NULL ,
CHANGE COLUMN `version` `version` VARCHAR(10) NOT NULL ,
CHANGE COLUMN `webSiteTitle` `webSiteTitle` VARCHAR(45) NOT NULL DEFAULT 'YouPHPTube' ,
CHANGE COLUMN `language` `language` VARCHAR(6) NOT NULL DEFAULT 'en' ,
CHANGE COLUMN `contactEmail` `contactEmail` VARCHAR(45) NOT NULL ,
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL DEFAULT now() ,
CHANGE COLUMN `created` `created` DATETIME NOT NULL DEFAULT now() ,
ADD COLUMN `authGoogle_id` VARCHAR(255) NULL DEFAULT NULL AFTER `created`,
ADD COLUMN `authGoogle_key` VARCHAR(255) NULL DEFAULT NULL AFTER `authGoogle_id`,
ADD COLUMN `authGoogle_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `authGoogle_key`,
ADD COLUMN `authFacebook_id` VARCHAR(255) NULL DEFAULT NULL AFTER `authGoogle_enabled`,
ADD COLUMN `authFacebook_key` VARCHAR(255) NULL DEFAULT NULL AFTER `authFacebook_id`,
ADD COLUMN `authFacebook_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `authFacebook_key`,
ADD COLUMN `authCanUploadVideos` TINYINT(1) NOT NULL DEFAULT 0 AFTER `authFacebook_enabled`,
ADD COLUMN `authCanComment` TINYINT(1) NOT NULL DEFAULT 1 AFTER `authCanUploadVideos`;

ALTER TABLE `users` 
ADD COLUMN `status` ENUM('a', 'i') NOT NULL DEFAULT 'a' AFTER `isAdmin`,
ADD COLUMN `photoURL` VARCHAR(255) NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `lastLogin` DATETIME NULL DEFAULT NULL AFTER `photoURL` ,
ADD COLUMN `recoverPass` VARCHAR(255) NULL DEFAULT NULL AFTER `lastLogin`;



UPDATE configurations SET  version = '1.4', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
