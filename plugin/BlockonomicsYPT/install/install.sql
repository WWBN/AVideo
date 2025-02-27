-- MySQL Workbench Forward Engineering

CREATE TABLE IF NOT EXISTS `blockonomics_order` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `addr` VARCHAR(255) NOT NULL,
  `txid` VARCHAR(255) NOT NULL,
  `status` INT(8) NOT NULL,
  `bits` INT(8) NOT NULL,
  `bits_payed` INT(8) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `users_id` INT NOT NULL,
  `total_value` FLOAT(10,5) NOT NULL,
  `currency` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `addr_UNIQUE` (`addr` ASC),
  INDEX `fk_blockonomics_order_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_blockonomics_order_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;