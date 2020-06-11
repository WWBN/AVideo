-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `combine_sites`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `combine_sites` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `site_url` VARCHAR(255) NOT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `channels_label` VARCHAR(255) NULL,
  `playlists_label` VARCHAR(255) NULL,
  `categories_label` VARCHAR(255) NULL,
  `site_label` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `get_token` VARCHAR(255) NULL,
  `give_token` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `combinesiteURL_index` (`site_url` ASC),
  UNIQUE INDEX `get_token_UNIQUE` (`get_token` ASC),
  UNIQUE INDEX `give_token_UNIQUE` (`give_token` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `combine_sites_get_elements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `combine_sites_get_elements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `combine_sites_id` INT NOT NULL,
  `users_id` INT NULL,
  `categories_id` INT NULL,
  `playlists_id` INT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  PRIMARY KEY (`id`),
  INDEX `fk_combine_sites_elements_combine_sites_idx` (`combine_sites_id` ASC),
  INDEX `indexcsget` (`status` ASC),
  CONSTRAINT `fk_combine_sites_elements_combine_sites`
    FOREIGN KEY (`combine_sites_id`)
    REFERENCES `combine_sites` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `combine_sites_give_elements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `combine_sites_give_elements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `combine_sites_id` INT NOT NULL,
  `users_id` INT NULL,
  `categories_id` INT NULL,
  `playlists_id` INT NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_combine_sites_give_elements_combine_sites1_idx` (`combine_sites_id` ASC),
  INDEX `fk_combine_sites_give_elements_users1_idx` (`users_id` ASC),
  INDEX `fk_combine_sites_give_elements_categories1_idx` (`categories_id` ASC),
  INDEX `fk_combine_sites_give_elements_playlists1_idx` (`playlists_id` ASC),
  INDEX `indexcsgive` (`status` ASC),
  CONSTRAINT `fk_combine_sites_give_elements_combine_sites1`
    FOREIGN KEY (`combine_sites_id`)
    REFERENCES `combine_sites` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_combine_sites_give_elements_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_combine_sites_give_elements_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_combine_sites_give_elements_playlists1`
    FOREIGN KEY (`playlists_id`)
    REFERENCES `playlists` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
