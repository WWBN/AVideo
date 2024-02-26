CREATE TABLE IF NOT EXISTS `emails_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `message` TEXT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_msg` (`message`(255) ASC))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `email_to_user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sent_at` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `emails_messages_id` INT NOT NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_email_to_user_emails_messages1_idx` (`emails_messages_id` ASC),
  INDEX `fk_email_to_user_users1_idx` (`users_id` ASC),
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