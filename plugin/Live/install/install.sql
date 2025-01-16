-- MySQL Workbench Synchronization
-- Generated: 2017-09-12 22:18
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';



-- -----------------------------------------------------
-- Table `live_transmitions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `live_transmitions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `public` TINYINT(1) NULL DEFAULT 1,
  `saveTransmition` TINYINT(1) NULL DEFAULT 0,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `key` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `users_id` INT(11) NOT NULL,
  `categories_id` INT(11) NOT NULL,
  `showOnTV` TINYINT NULL,
  `password` VARCHAR(255) NOT NULL,
  `isRebroadcast` TINYINT(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmitions_users1_idx` (`users_id` ASC),
  INDEX `fk_live_transmitions_categories1_idx` (`categories_id` ASC),
  INDEX `showOnTVLiveindex3` (`showOnTV` ASC),
  CONSTRAINT `fk_live_transmitions_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_live_transmitions_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `live_transmitions_has_users_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `live_transmitions_has_users_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `live_transmitions_id` INT NOT NULL,
  `users_groups_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmitions_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_live_transmitions_has_users_groups_live_transmitions1_idx` (`live_transmitions_id` ASC),
  CONSTRAINT `fk_live_transmitions_has_users_groups_live_transmitions1`
    FOREIGN KEY (`live_transmitions_id`)
    REFERENCES `live_transmitions` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_transmitions_has_users_groups_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `live_transmitions_history` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `key` VARCHAR(255) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  `users_id` INT(11) NOT NULL,
  `live_servers_id` INT(11) NULL DEFAULT NULL,
  `finished` DATETIME NULL DEFAULT NULL,
  `domain` VARCHAR(255) NULL DEFAULT NULL,
  `json` TEXT NULL DEFAULT NULL,
  `max_viewers_sametime` INT(10) UNSIGNED NULL DEFAULT NULL,
  `total_viewers` INT(10) UNSIGNED NULL DEFAULT NULL,
  `users_id_company` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmitions_history_users1_idx` (`users_id_company` ASC),
  INDEX `fk_live_transmitions_history_users_idx` (`users_id` ASC),
  INDEX `fk_live_transmitions_history_live_servers1_idx` (`live_servers_id` ASC),
  CONSTRAINT `fk_live_transmitions_history_users`
    FOREIGN KEY (`users_id`)
    REFERENCES  `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_transmitions_history_live_servers1`
    FOREIGN KEY (`live_servers_id`)
    REFERENCES `live_servers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_transmitions_history_users1`
    FOREIGN KEY (`users_id_company`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `live_transmition_history_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `live_transmitions_history_id` INT NOT NULL,
  `users_id` INT(11) NULL,
  `session_id` VARCHAR(45) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmition_history_log_live_transmitions_history1_idx` (`live_transmitions_history_id` ASC),
  CONSTRAINT `fk_live_transmition_history_log_live_transmitions_history1`
    FOREIGN KEY (`live_transmitions_history_id`)
    REFERENCES `live_transmitions_history` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `live_servers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL DEFAULT NULL,
  `url` VARCHAR(255) NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `rtmp_server` VARCHAR(255) NULL DEFAULT NULL,
  `playerServer` VARCHAR(255) NULL DEFAULT NULL,
  `stats_url` VARCHAR(255) NULL DEFAULT NULL,
  `disableDVR` TINYINT(1) NULL DEFAULT NULL,
  `disableGifThumbs` TINYINT(1) NULL DEFAULT NULL,
  `useAadaptiveMode` TINYINT(1) NULL DEFAULT NULL,
  `protectLive` TINYINT(1) NULL DEFAULT NULL,
  `getRemoteFile` VARCHAR(255) NULL DEFAULT NULL,
  `restreamerURL` VARCHAR(255) NULL DEFAULT NULL,
  `controlURL` VARCHAR(255) NULL DEFAULT NULL,
  `webRTC_server` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `live_serversindex2` (`status` ASC),
  INDEX `live_servers` (`url` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `live_restreams` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(500) NOT NULL,
  `stream_url` VARCHAR(500) NOT NULL,
  `stream_key` VARCHAR(500) NOT NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `parameters` TEXT NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_restreams_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_live_restreams_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `live_schedule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `key` VARCHAR(255) NULL,
  `users_id` INT(11) NOT NULL,
  `live_servers_id` INT(11) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `scheduled_time` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  `poster` VARCHAR(255) NULL,
  `public` TINYINT(1) NULL,
  `saveTransmition` TINYINT(1) NULL,
  `showOnTV` TINYINT(4) NULL,
  `scheduled_password` VARCHAR(255) NULL,
  `users_id_company` INT(11) NULL DEFAULT NULL,
  `json` TEXT NULL DEFAULT NULL,
  `scheduled_php_time` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_schedule_users2_idx` (`users_id_company` ASC),
  INDEX `fk_live_schedule_users1_idx` (`users_id` ASC),
  INDEX `fk_live_schedule_live_servers1_idx` (`live_servers_id` ASC),
  CONSTRAINT `fk_live_schedule_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_schedule_live_servers1`
    FOREIGN KEY (`live_servers_id`)
    REFERENCES `live_servers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_schedule_users2`
    FOREIGN KEY (`users_id_company`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `live_restreams_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `restreamer` VARCHAR(255) NOT NULL,
  `m3u8` VARCHAR(400) NULL,
  `logFile` VARCHAR(255) NULL,
  `json` TEXT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `live_transmitions_history_id` INT(11) NOT NULL,
  `live_restreams_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_restreams_logs_live_transmitions_history1_idx` (`live_transmitions_history_id` ASC),
  INDEX `fk_live_restreams_logs_live_restreams1_idx` (`live_restreams_id` ASC),
  CONSTRAINT `fk_live_restreams_logs_live_transmitions_history1`
    FOREIGN KEY (`live_transmitions_history_id`)
    REFERENCES `live_transmitions_history` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_restreams_logs_live_restreams1`
    FOREIGN KEY (`live_restreams_id`)
    REFERENCES `live_restreams` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
