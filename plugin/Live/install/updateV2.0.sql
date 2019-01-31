CREATE TABLE IF NOT EXISTS `live_transmitions_history` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `key` VARCHAR(255) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmitions_history_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_live_transmitions_history_users`
    FOREIGN KEY (`users_id`)
    REFERENCES  `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB