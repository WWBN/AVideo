CREATE TABLE IF NOT EXISTS `logincontrol_history` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT NOT NULL,
  `uniqidV4` VARCHAR(45) NOT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `confirmation_code` VARCHAR(45) NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_login_history_users_idx` (`users_id` ASC),
  INDEX `uniqueidv4_index` USING BTREE (`uniqidV4`),
  INDEX `sort_created_index` (`created` ASC),
  CONSTRAINT `fk_login_history_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;