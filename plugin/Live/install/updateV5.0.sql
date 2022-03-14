CREATE TABLE IF NOT EXISTS `live_restreams` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `stream_url` VARCHAR(255) NOT NULL,
  `stream_key` VARCHAR(255) NOT NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `parameters` TEXT NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_restreams_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_live_restreams_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;