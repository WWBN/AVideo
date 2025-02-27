CREATE TABLE IF NOT EXISTS `scheduler_commands` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `callbackURL` VARCHAR(255) NOT NULL,
  `parameters` TEXT NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `date_to_execute` DATETIME NULL,
  `time_to_execute` INT(11) NULL,
  `executed_in` DATETIME NULL DEFAULT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `callbackResponse` TEXT NULL DEFAULT NULL,
  `timezone` VARCHAR(255) NULL,
  `created_php_time` INT(11) NULL,
  `repeat_minute` INT NULL,
  `repeat_hour` INT NULL,
  `repeat_day_of_month` INT NULL,
  `repeat_month` INT NULL,
  `repeat_day_of_week` INT NULL,
  `day_of_week` INT NULL,
  `videos_id` INT(11) NULL,
  `type` VARCHAR(45)  DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_scheduler_commands_videos1_idx` (`videos_id` ASC),
  INDEX `scheduler_commands_created_php_time` (`created_php_time` ASC),
  INDEX `time_to_execute_index` (`time_to_execute` ASC),
  CONSTRAINT `fk_scheduler_commands_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `emails_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `message` TEXT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `email_to_user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sent_at` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `created_php_time` INT(11) NULL,
  `modified` DATETIME NULL,
  `emails_messages_id` INT NOT NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_email_to_user_emails_messages1_idx` (`emails_messages_id` ASC),
  INDEX `fk_email_to_user_users1_idx` (`users_id` ASC),
  INDEX `email_to_user_created_php_time` (`created_php_time` ASC),
  CONSTRAINT `fk_email_to_user_emails_messages1`
    FOREIGN KEY (`emails_messages_id`)
    REFERENCES `emails_messages` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_to_user_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;