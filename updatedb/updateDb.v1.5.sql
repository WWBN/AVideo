-- MySQL Workbench Synchronization
-- Generated: 2017-04-21 16:40
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `categories` 
CHANGE COLUMN `created` `created` DATETIME NOT NULL DEFAULT now() ,
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL DEFAULT now() ,
ADD COLUMN `iconClass` VARCHAR(45) NOT NULL DEFAULT 'fa fa-folder' AFTER `modified`;

ALTER TABLE `configurations` 
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL DEFAULT now() ,
CHANGE COLUMN `created` `created` DATETIME NOT NULL DEFAULT now() ;

UPDATE configurations SET  version = '1.5', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
