-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `topMenu` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `menuName` VARCHAR(255) NOT NULL,
  `categories_id` INT NULL,
  `users_groups_id` INT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `menu_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `position` ENUM('left', 'right', 'center', 'bottom') NOT NULL DEFAULT 'right',
  `type` INT NOT NULL DEFAULT 1 COMMENT '1 = default\n',
  `icon` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `topMenu_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NULL,
  `url` VARCHAR(255) NULL,
  `class` VARCHAR(255) NULL,
  `style` VARCHAR(255) NULL,
  `item_order` INT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `topMenu_id` INT NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `text` TEXT NULL,
  `icon` VARCHAR(255) NULL,
  `clean_url` VARCHAR(255) NULL,
  `menuSeoUrlItem` VARCHAR(255) DEFAULT '',  
  PRIMARY KEY (`id`),
  INDEX `fk_topMenu_items_topMenu_idx` (`topMenu_id` ASC),
  CONSTRAINT `fk_topMenu_items_topMenu`
    FOREIGN KEY (`topMenu_id`)
    REFERENCES `topMenu` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
