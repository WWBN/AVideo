

-- -----------------------------------------------------
-- Table `meet_schedule`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `meet_schedule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT NOT NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `public` TINYINT(1) NULL,
  `live_stream` TINYINT(1) NULL,
  `password` VARCHAR(45) NULL,
  `topic` VARCHAR(255) NULL,
  `starts` DATETIME NULL,
  `finish` DATETIME NULL,
  `name` VARCHAR(255) NULL,
  `meet_code` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_meet_users_rights_users_idx` (`users_id` ASC),
  UNIQUE INDEX `meet_code_UNIQUE` (`meet_code` ASC),
  CONSTRAINT `fk_meet_users_rights_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `meet_schedule_has_users_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `meet_schedule_has_users_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `meet_schedule_id` INT NOT NULL,
  `users_groups_id` INT NOT NULL,
  INDEX `fk_meet_schedule_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_meet_schedule_has_users_groups_meet_schedule1_idx` (`meet_schedule_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_meet_schedule_has_users_groups_meet_schedule1`
    FOREIGN KEY (`meet_schedule_id`)
    REFERENCES `meet_schedule` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_meet_schedule_has_users_groups_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `meet_join_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `meet_join_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `meet_schedule_id` INT NOT NULL,
  `users_id` INT NULL,
  `created` DATETIME NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_meet_join_log_meet_schedule1_idx` (`meet_schedule_id` ASC),
  INDEX `fk_meet_join_log_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_meet_join_log_meet_schedule1`
    FOREIGN KEY (`meet_schedule_id`)
    REFERENCES `meet_schedule` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_meet_join_log_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

