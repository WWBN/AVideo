CREATE TABLE IF NOT EXISTS `users_connections` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id1` INT(11) NOT NULL,
  `users_id2` INT(11) NOT NULL,
  `user1_status` CHAR(1) NULL,
  `user2_status` CHAR(1) NULL,
  `user1_mute` INT(1) UNSIGNED NULL,
  `user2_mute` INT(1) UNSIGNED NULL,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  `json` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_users_connections_users1_idx` (`users_id1` ASC),
  INDEX `fk_users_connections_users2_idx` (`users_id2` ASC),
  UNIQUE INDEX `unique_connections` (`users_id1` ASC, `users_id2` ASC),
  INDEX `user1_status` (`users_id1` ASC),
  INDEX `user2_status` (`users_id2` ASC),
  CONSTRAINT `fk_users_connections_users1`
    FOREIGN KEY (`users_id1`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_users_connections_users2`
    FOREIGN KEY (`users_id2`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;