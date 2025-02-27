CREATE TABLE IF NOT EXISTS  `user_notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `msg` TEXT NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` CHAR(1) NOT NULL,
  `time_readed` DATETIME NULL,
  `users_id` INT(11) NULL,
  `image` VARCHAR(255) NULL,
  `icon` VARCHAR(255) NULL,
  `href` VARCHAR(255) NULL,
  `onclick` VARCHAR(255) NULL,
  `element_class` VARCHAR(255) NULL,
  `element_id` VARCHAR(255) NULL,
  `priority` INT NOT NULL,
  `timezone` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_user_notifications_users1_idx` (`users_id` ASC) ,
  INDEX `index_user_notification_type` (`type` ASC) ,
  INDEX `index_user_notification_status` (`status` ASC) ,
  INDEX `index_user_notification_priority` (`priority` ASC) ,
  UNIQUE INDEX `unique_element_id` (`element_id` ASC) ,
  CONSTRAINT `fk_user_notifications_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES  `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;