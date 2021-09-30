CREATE TABLE IF NOT EXISTS `statistics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_id` INT NOT NULL,
  `total_videos` INT NULL,
  `total_video_views` INT NULL,
  `total_subscriptions` INT NULL,
  `total_comments` INT NULL,
  `total_likes` INT NULL,
  `total_dislikes` INT NULL,
  `total_duration_seconds` INT NULL,
  `modified` DATETIME NULL,
  `created` DATETIME NULL,
  `collected_date` DATE NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_statistics_users_idx` (`users_id` ASC),
  CONSTRAINT `fk_statistics_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
