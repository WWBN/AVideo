-- MySQL Workbench Synchronization
-- Generated: 2017-06-27 10:14
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER SCHEMA `youPHPTube`  DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci ;

ALTER TABLE `users` 
ADD COLUMN `backgroundURL` VARCHAR(255) NULL DEFAULT NULL AFTER `recoverPass`;

ALTER TABLE `configurations` 
CHANGE COLUMN `encode_mp3spectrum` `encode_mp3spectrum` TINYINT(1) NULL DEFAULT 1 AFTER `encode_webm`,
CHANGE COLUMN `mode` `mode` ENUM('Youtube', 'Gallery') NULL DEFAULT 'Youtube' ,
CHANGE COLUMN `autoplay` `autoplay` TINYINT(1) NULL DEFAULT NULL ;

ALTER TABLE `subscribes` 
ADD COLUMN `users_id` INT(11) NOT NULL DEFAULT 1 COMMENT 'subscribes to user channel' AFTER `ip`,
ADD INDEX `fk_subscribes_users1_idx` (`users_id` ASC),
DROP INDEX `email_UNIQUE` ;

CREATE TABLE IF NOT EXISTS `playlists` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `users_id` INT(11) NOT NULL,
  `status` ENUM('public', 'private') NOT NULL DEFAULT 'public',
  PRIMARY KEY (`id`),
  INDEX `fk_playlists_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_playlists_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `playlists_has_videos` (
  `playlists_id` INT(11) NOT NULL,
  `videos_id` INT(11) NOT NULL,
  PRIMARY KEY (`playlists_id`, `videos_id`),
  INDEX `fk_playlists_has_videos_videos1_idx` (`videos_id` ASC),
  INDEX `fk_playlists_has_videos_playlists1_idx` (`playlists_id` ASC),
  CONSTRAINT `fk_playlists_has_videos_playlists1`
    FOREIGN KEY (`playlists_id`)
    REFERENCES `playlists` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_playlists_has_videos_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

UPDATE configurations SET  version = '3.2', modified = now() WHERE id = 1;