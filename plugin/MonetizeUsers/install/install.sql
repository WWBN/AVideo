CREATE TABLE IF NOT EXISTS `monetize_user_reward_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `videos_id` INT(11) NOT NULL,
  `video_owner_users_id` INT(11) NOT NULL,
  `percentage_watched` DOUBLE NOT NULL,
  `seconds_watching_video` INT NOT NULL,
  `when_watched` DATETIME NOT NULL,
  `total_reward` DOUBLE NOT NULL,
  `who_watched_users_id` INT(11) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `created_php_time` INT(11) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_monetize_user_reward_log_videos1_idx` (`videos_id` ASC) ,
  INDEX `monetize_user_reward_log_idx1` (`video_owner_users_id` ASC) ,
  INDEX `monetize_user_reward_log_idx2` (`when_watched` ASC) ,
  INDEX `monetize_user_reward_log_created_php_time` (`created_php_time` ASC),
  CONSTRAINT `fk_monetize_user_reward_log_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;