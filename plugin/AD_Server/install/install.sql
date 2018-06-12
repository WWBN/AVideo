-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `vast_campaigns`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `vast_campaigns` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `type` ENUM('Remnant', 'Contract', 'Override') NOT NULL DEFAULT 'Contract',
  `status` ENUM('a', 'i') NOT NULL DEFAULT 'a',
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `pricing_model` ENUM('CPM', 'CPC') NOT NULL DEFAULT 'CPC',
  `priority` INT NOT NULL DEFAULT 1,
  `users_id` INT NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `visibility` ENUM('listed', 'unlisted') NULL,
  `cpc_budget_type` ENUM('Daily', 'Campaign Total') NULL,
  `cpc_total_budget` FLOAT(20,10) NULL,
  `cpc_max_price_per_click` FLOAT(20,10) NULL,
  `cpm_max_prints` INT UNSIGNED NULL,
  `cpm_current_prints` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_vast_campains_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_vast_campains_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `vast_campaigns_has_videos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `vast_campaigns_has_videos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vast_campaigns_id` INT NOT NULL,
  `videos_id` INT NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` ENUM('a', 'i') NULL,
  `link` VARCHAR(255) NULL,
  `ad_title` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_vast_campaigns_has_videos_videos1_idx` (`videos_id` ASC),
  INDEX `fk_vast_campaigns_has_videos_vast_campaigns1_idx` (`vast_campaigns_id` ASC),
  CONSTRAINT `fk_vast_campaigns_has_videos_vast_campaigns1`
    FOREIGN KEY (`vast_campaigns_id`)
    REFERENCES `vast_campaigns` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vast_campaigns_has_videos_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `vast_campaigns_logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `vast_campaigns_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT NULL,
  `type` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `vast_campaigns_has_videos_id` INT NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_vast_campains_logs_users1_idx` (`users_id` ASC),
  INDEX `fk_vast_campaigns_logs_vast_campaigns_has_videos1_idx` (`vast_campaigns_has_videos_id` ASC),
  CONSTRAINT `fk_vast_campains_logs_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_vast_campaigns_logs_vast_campaigns_has_videos1`
    FOREIGN KEY (`vast_campaigns_has_videos_id`)
    REFERENCES `vast_campaigns_has_videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
