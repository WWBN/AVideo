CREATE TABLE IF NOT EXISTS `publisher_social_medias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `api_details` TEXT NULL COMMENT 'Placeholder for storing API-related data if necessary',
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `publisher_user_preferences` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `publisher_social_medias_id` INT NOT NULL,
  `preferred_profile` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `json` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_publisher_user_preferences_users1_idx` (`users_id` ASC),
  INDEX `fk_publisher_user_preferences_publisher_social_medias1_idx` (`publisher_social_medias_id` ASC),
  CONSTRAINT `fk_publisher_user_preferences_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_publisher_user_preferences_publisher_social_medias1`
    FOREIGN KEY (`publisher_social_medias_id`)
    REFERENCES `publisher_social_medias` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `publisher_video_publisher_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `publish_datetimestamp` INT UNSIGNED NOT NULL,
  `status` CHAR(1) NULL,
  `details` TEXT NULL,
  `videos_id` INT(11) NOT NULL,
  `users_id` INT(11) NOT NULL,
  `publisher_social_medias_id` INT NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Publisher_video_publisher_logs_videos1_idx` (`videos_id` ASC),
  INDEX `fk_Publisher_video_publisher_logs_users1_idx` (`users_id` ASC),
  INDEX `fk_Publisher_video_publisher_logs_publisher_social_medias1_idx` (`publisher_social_medias_id` ASC),
  CONSTRAINT `fk_Publisher_video_publisher_logs_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Publisher_video_publisher_logs_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Publisher_video_publisher_logs_publisher_social_medias1`
    FOREIGN KEY (`publisher_social_medias_id`)
    REFERENCES `publisher_social_medias` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `publisher_schedule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `scheduled_timestamp` INT UNSIGNED NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `videos_id` INT(11) NOT NULL,
  `users_id` INT(11) NOT NULL,
  `publisher_social_medias_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_publisher_schedule_videos1_idx` (`videos_id` ASC),
  INDEX `fk_publisher_schedule_users1_idx` (`users_id` ASC),
  INDEX `fk_publisher_schedule_publisher_social_medias1_idx` (`publisher_social_medias_id` ASC),
  CONSTRAINT `fk_publisher_schedule_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_publisher_schedule_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_publisher_schedule_publisher_social_medias1`
    FOREIGN KEY (`publisher_social_medias_id`)
    REFERENCES `publisher_social_medias` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;