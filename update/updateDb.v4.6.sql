-- MySQL Workbench Synchronization
-- Generated: 2018-01-27 21:59
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `comments` 
ADD COLUMN `comments_id_pai` INT(11) NULL DEFAULT NULL AFTER `modified`,
ADD COLUMN `pin` INT(1) NOT NULL DEFAULT 0 COMMENT 'If = 1 will be on the top' AFTER `comments_id_pai`,
ADD INDEX `fk_comments_comments1_idx` (`comments_id_pai` ASC);

CREATE TABLE IF NOT EXISTS `comments_likes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `like` INT(1) NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `comments_likescol` VARCHAR(45) NULL DEFAULT NULL,
  `users_id` INT(11) NOT NULL,
  `comments_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_comments_likes_users1_idx` (`users_id` ASC),
  INDEX `fk_comments_likes_comments1_idx` (`comments_id` ASC),
  CONSTRAINT `fk_comments_likes_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_likes_comments1`
    FOREIGN KEY (`comments_id`)
    REFERENCES `comments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `comments` 
ADD CONSTRAINT `fk_comments_comments1`
  FOREIGN KEY (`comments_id_pai`)
  REFERENCES `comments` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

UPDATE configurations SET  version = '4.6', modified = now() WHERE id = 1;