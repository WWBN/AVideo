-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `campaign_locations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `country_name` VARCHAR(45) NULL,
  `region_name` VARCHAR(45) NULL,
  `city_name` VARCHAR(45) NULL,
  `vast_campaigns_id` INT NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  INDEX `fk_campaign_locations_vast_campaigns_idx` (`vast_campaigns_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_campaign_locations_vast_campaigns`
    FOREIGN KEY (`vast_campaigns_id`)
    REFERENCES `vast_campaigns` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
