CREATE TABLE IF NOT EXISTS `tags_subscriptions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `tags_id` INT(11) NOT NULL,
  `users_id` INT(11) NOT NULL,
  `notify` TINYINT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_tags_subscriptions_tags1_idx` (`tags_id` ASC) ,
  INDEX `fk_tags_subscriptions_users1_idx` (`users_id` ASC) ,
  INDEX `indextagsnotify` (notify ASC),
  CONSTRAINT `fk_tags_subscriptions_tags1`
    FOREIGN KEY (`tags_id`)
    REFERENCES `tags` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tags_subscriptions_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;