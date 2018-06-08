-- MySQL Workbench Synchronization
-- Generated: 2017-05-11 23:33
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `video_ads` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ad_title` VARCHAR(255) NOT NULL,
  `starts` DATETIME NOT NULL,
  `finish` DATETIME NULL DEFAULT NULL,
  `skip_after_seconds` INT(4) NULL DEFAULT NULL,
  `redirect` VARCHAR(300) NULL DEFAULT NULL,
  `finish_max_clicks` INT(11) NULL DEFAULT NULL,
  `finish_max_prints` INT(11) NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `videos_id` INT(11) NOT NULL,
  `categories_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_video_ads_videos1_idx` (`videos_id` ASC),
  INDEX `fk_video_ads_categories1_idx` (`categories_id` ASC),
  CONSTRAINT `fk_video_ads_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_video_ads_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `video_ads_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `datetime` DATETIME NOT NULL,
  `clicked` TINYINT(1) NOT NULL DEFAULT 0,
  `ip` VARCHAR(45) NOT NULL,
  `video_ads_id` INT(11) NOT NULL,
  `users_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_video_ads_logs_users1_idx` (`users_id` ASC),
  INDEX `fk_video_ads_logs_video_ads1_idx` (`video_ads_id` ASC),
  CONSTRAINT `fk_video_ads_logs_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_video_ads_logs_video_ads1`
    FOREIGN KEY (`video_ads_id`)
    REFERENCES `video_ads` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `configurations` 
ADD COLUMN `mode` ENUM('Youtube', 'Gallery') NULL DEFAULT 'Youtube' AFTER `adsense`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


UPDATE configurations SET  version = '2.7', modified = now() WHERE id = 1;
