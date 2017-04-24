-- MySQL Workbench Synchronization
-- Generated: 2017-04-24 10:51
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `videos` 
CHANGE COLUMN `status` `status` ENUM('a', 'i', 'e', 'x', 'd', 'xmp4', 'xwebm', 'xmp3', 'xogg', 'ximg') NOT NULL DEFAULT 'e' COMMENT 'a = active\ni = inactive\ne = encoding\nx = encoding error\nd = downloading\nxmp4 = encoding mp4 error \nxwebm = encoding webm error \nxmp3 = encoding mp3 error \nxogg = encoding ogg error \nximg = get image error' ,
ADD COLUMN `videoDownloadedLink` VARCHAR(255) NULL DEFAULT NULL AFTER `type`;

ALTER TABLE `categories` 
CHANGE COLUMN `created` `created` DATETIME NOT NULL ,
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL ,
ADD COLUMN `iconClass` VARCHAR(45) NOT NULL DEFAULT 'fa fa-folder' AFTER `modified`;

ALTER TABLE `configurations` 
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL ,
CHANGE COLUMN `created` `created` DATETIME NOT NULL ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
