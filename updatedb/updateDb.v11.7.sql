-- MySQL Workbench Synchronization
-- Generated: 2022-03-01 11:10
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: msn

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `users` 
ADD COLUMN `is_company` TINYINT(4) NULL DEFAULT NULL AFTER `phone`;

ALTER TABLE `videos` 
ADD COLUMN `users_id_company` INT(11) NULL DEFAULT NULL AFTER `dislikes`,
ADD INDEX `fk_videos_users1_idx` (`users_id_company` ASC);

CREATE TABLE IF NOT EXISTS `users_affiliations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `users_id_company` INT(11) NOT NULL,
  `users_id_affiliate` INT(11) NOT NULL,
  `status` CHAR(1) NULL DEFAULT NULL,
  `company_agree_date` DATETIME NULL DEFAULT NULL,
  `affiliate_agree_date` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_users_affiliations_users1_idx` (`users_id_company` ASC),
  INDEX `fk_users_affiliations_users2_idx` (`users_id_affiliate` ASC),
  CONSTRAINT `fk_users_affiliations_users1`
    FOREIGN KEY (`users_id_company`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_users_affiliations_users2`
    FOREIGN KEY (`users_id_affiliate`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

ALTER TABLE `videos` 
ADD CONSTRAINT `fk_videos_users1`
  FOREIGN KEY (`users_id_company`)
  REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE SET NULL;


UPDATE configurations SET  version = '11.7', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
