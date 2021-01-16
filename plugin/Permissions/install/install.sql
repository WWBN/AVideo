CREATE TABLE IF NOT EXISTS `users_groups_permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `users_groups_id` INT(11) NOT NULL,
  `plugins_id` INT(11) NOT NULL,
  `type` INT(11) NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_permissions_users_groups2_idx` (`users_groups_id` ASC),
  INDEX `fk_permissions_plugins1_idx` (`plugins_id` ASC),
  CONSTRAINT `fk_permissions_users_groups2`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_permissions_plugins1`
    FOREIGN KEY (`plugins_id`)
    REFERENCES `plugins` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;