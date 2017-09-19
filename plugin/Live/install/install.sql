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
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `public` TINYINT(1) NULL DEFAULT 1,
  `saveTransmition` TINYINT(1) NULL DEFAULT 0,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `key` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `users_id` INT NOT NULL,
  `categories_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmitions_users1_idx` (`users_id` ASC),
  INDEX `fk_live_transmitions_categories1_idx` (`categories_id` ASC),
  CONSTRAINT `fk_live_transmitions_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_live_transmitions_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
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



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
