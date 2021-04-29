-- MySQL Workbench Synchronization
-- Generated: 2017-09-12 22:18
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


CREATE TABLE IF NOT EXISTS `LiveLinks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `link` TEXT NOT NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `type` ENUM('public', 'unlisted', 'logged_only') NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` ENUM('a', 'i') NULL DEFAULT 'a',
  `users_id` INT NOT NULL,
  `categories_id` INT NULL ,
  PRIMARY KEY (`id`),
  INDEX `fk_LiveLinks_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_LiveLinks_users2`
      FOREIGN KEY (`users_id`)
      REFERENCES `users` (`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `fk_livelinks_categories1`
      FOREIGN KEY (`categories_id`)
      REFERENCES `categories` (`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `livelinks_has_users_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `livelinks_id` INT(11) NOT NULL,
  `users_groups_id` INT(11) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_livelinks_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_livelinks_has_users_groups_livelinks1_idx` (`livelinks_id` ASC),
  CONSTRAINT `fk_livelinks_has_users_groups_livelinks1`
    FOREIGN KEY (`livelinks_id`)
    REFERENCES `livelinks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_livelinks_has_users_groups_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
