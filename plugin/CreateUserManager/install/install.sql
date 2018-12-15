CREATE TABLE IF NOT EXISTS `userManagers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `users_id` INT NOT NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  PRIMARY KEY (`id`),
  INDEX `fk_userManagers_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_userManagers_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;