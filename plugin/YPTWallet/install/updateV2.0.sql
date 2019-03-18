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