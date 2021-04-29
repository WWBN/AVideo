CREATE TABLE IF NOT EXISTS `livelinks_has_users_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `livelinks_id` INT(11) NOT NULL,
  `users_groups_id` INT(11) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_livelinks_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_livelinks_has_users_groups_livelinks1_idx` (`livelinks_id` ASC),
  CONSTRAINT `fk_livelinks_has_users_groups_livelinks1`
    FOREIGN KEY (`livelinks_id`)
    REFERENCES `livelinks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_livelinks_has_users_groups_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;