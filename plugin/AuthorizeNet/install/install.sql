CREATE TABLE IF NOT EXISTS `anet_webhook_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uniq_key` VARCHAR(120) NOT NULL,
  `event_type` VARCHAR(120) NOT NULL,
  `trans_id` VARCHAR(45) NULL,
  `payload_json` JSON NOT NULL,
  `processed` TINYINT(1) NOT NULL DEFAULT 0,
  `error_text` TEXT NULL,
  `status` VARCHAR(45) NULL,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uniq_key_UNIQUE` (`uniq_key` ASC),
  INDEX `fk_anet_webhook_log_users1_idx` (`users_id` ASC),
  INDEX `event_type_index` (`event_type` ASC),
  CONSTRAINT `fk_anet_webhook_log_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
