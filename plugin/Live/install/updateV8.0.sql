CREATE TABLE IF NOT EXISTS `live_schedule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `description` VARCHAR(255) NULL,
  `key` VARCHAR(255) NULL,
  `users_id` INT(11) NOT NULL,
  `live_servers_id` INT(11) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `scheduled_time` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  `poster` VARCHAR(255) NULL,
  `public` TINYINT(1) NULL,
  `saveTransmition` TINYINT(1) NULL,
  `showOnTV` TINYINT(4) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_schedule_users1_idx` (`users_id` ASC),
  INDEX `fk_live_schedule_live_servers1_idx` (`live_servers_id` ASC),
  CONSTRAINT `fk_live_schedule_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_schedule_live_servers1`
    FOREIGN KEY (`live_servers_id`)
    REFERENCES `live_servers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;