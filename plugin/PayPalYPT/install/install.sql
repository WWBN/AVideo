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

CREATE TABLE IF NOT EXISTS `PayPalYPT_checkout` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `invoice_number` VARCHAR(64) NOT NULL,
  `purpose` VARCHAR(20) NOT NULL,
  `selection_hash` CHAR(64) NOT NULL,
  `selection` TEXT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `currency` VARCHAR(3) NOT NULL,
  `environment` VARCHAR(10) NOT NULL,
  `client_hash` CHAR(64) NOT NULL,
  `payment_id` VARCHAR(64) NULL DEFAULT NULL,
  `agreement_id` VARCHAR(64) NULL DEFAULT NULL,
  `sale_id` VARCHAR(64) NULL DEFAULT NULL,
  `approval_url` VARCHAR(512) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'creating',
  `status_detail` VARCHAR(64) NULL DEFAULT NULL,
  `fulfillment` VARCHAR(20) NULL DEFAULT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uniq_paypal_checkout_invoice` (`invoice_number`),
  UNIQUE INDEX `uniq_paypal_checkout_payment` (`payment_id`),
  INDEX `idx_paypal_checkout_user` (`users_id`, `status`),
  INDEX `idx_paypal_checkout_agreement` (`agreement_id`),
  INDEX `idx_paypal_checkout_sale` (`sale_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
