-- MySQL Workbench Forward Engineering

CREATE TABLE IF NOT EXISTS `audit` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `method` VARCHAR(255) NULL,
  `class` VARCHAR(255) NULL,
  `statement` TEXT NULL,
  `ip` VARCHAR(45) NULL,
  `users_id` INT NULL,
  `formats` VARCHAR(45) NULL,
  `values` TEXT NULL,
  `created` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_audit_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_audit_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;