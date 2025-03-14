CREATE TABLE IF NOT EXISTS `btc_invoices` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `invoice_identification` VARCHAR(255) NOT NULL,
  `users_id` INT(11) NOT NULL,
  `amount_currency` DECIMAL(15,5) NOT NULL,
  `amount_btc` DECIMAL(16,8) NOT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `status` CHAR(1) NOT NULL,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  `json` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `invoice_identification_UNIQUE` (`invoice_identification` ASC),
  INDEX `fk_btc_invoices_users1_idx` (`users_id` ASC),
  INDEX `btc_invoice_status` (`status` ASC),
  INDEX `btc_invoice_id` (`invoice_identification` ASC),
  CONSTRAINT `fk_btc_invoices_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `btc_payments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `btc_invoices_id` INT NOT NULL,
  `transaction_identification` VARCHAR(255) NOT NULL,
  `amount_received_btc` DECIMAL(16,8) NOT NULL,
  `confirmations` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  `json` TEXT NOT NULL,
  `store` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_btc_payments_btc_invoices1_idx` (`btc_invoices_id` ASC),
  INDEX `btc_tx_id` (`transaction_identification` ASC),
  CONSTRAINT `fk_btc_payments_btc_invoices1`
    FOREIGN KEY (`btc_invoices_id`)
    REFERENCES `btc_invoices` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
