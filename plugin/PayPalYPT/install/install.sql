CREATE TABLE IF NOT EXISTS `PayPalYPT_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `agreement_id` VARCHAR(45) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `users_id` INT(11) NOT NULL,
  `json` TEXT NULL,
  `recurring_payment_id` VARCHAR(45) NULL,
  `value` FLOAT(10,2) NULL,
  `token` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_PayPalYPT_log_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_PayPalYPT_log_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;