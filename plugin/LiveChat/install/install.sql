-- MySQL Workbench Synchronization
-- Generated: 2017-09-12 22:18
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


CREATE TABLE IF NOT EXISTS `LiveChat` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  `users_id` INT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` ENUM('a', 'i', 'b') NULL COMMENT 'a = active\ni = inactive\nb = baned',
  `live_stream_code` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_LiveChat_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_LiveChat_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
