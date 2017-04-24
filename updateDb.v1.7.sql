-- MySQL Workbench Synchronization
-- Generated: 2017-04-24 13:00
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `videos` 
ADD COLUMN `order` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `videoDownloadedLink`,
ADD INDEX `index5` (`order` ASC);

ALTER TABLE `categories` 
CHANGE COLUMN `created` `created` DATETIME NOT NULL ,
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL ;

ALTER TABLE `configurations` 
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL ,
CHANGE COLUMN `created` `created` DATETIME NOT NULL ;

CREATE TABLE IF NOT EXISTS `videos_statistics` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `when` DATETIME NOT NULL,
  `ip` VARCHAR(45) NULL DEFAULT NULL,
  `users_id` INT(11) NULL DEFAULT NULL,
  `videos_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_videos_statistics_users1_idx` (`users_id` ASC),
  INDEX `fk_videos_statistics_videos1_idx` (`videos_id` ASC),
  CONSTRAINT `fk_videos_statistics_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_videos_statistics_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
