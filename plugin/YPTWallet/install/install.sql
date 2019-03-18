-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `documents`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `is_public` TINYINT(1) NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `videos_id` INT(11) NOT NULL,
  `status` ENUM('a', 'i') NULL DEFAULT 'a',
  `data` LONGBLOB NULL DEFAULT NULL,
  `link` VARCHAR(255) NULL DEFAULT NULL,
  `price` DOUBLE(20,10) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_documents_videos_idx` (`videos_id` ASC),
  CONSTRAINT `fk_documents_videos`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `users_has_documents`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_has_documents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `documents_id` INT(11) NOT NULL,
  `price_paid` DOUBLE(20,10) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `observation` TEXT NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  PRIMARY KEY (`id`),
  INDEX `fk_users_has_documents_documents1_idx` (`documents_id` ASC),
  INDEX `fk_users_has_documents_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_users_has_documents_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_documents_documents1`
    FOREIGN KEY (`documents_id`)
    REFERENCES `documents` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

CREATE TABLE IF NOT EXISTS `paypal_subscription` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `wallet_id` INT(11) NOT NULL,
  `token` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_paypal_subscription_wallet_idx` (`wallet_id` ASC),
  UNIQUE INDEX `token_UNIQUE` (`token` ASC),
  CONSTRAINT `fk_paypal_subscription_wallet`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `wallet` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;