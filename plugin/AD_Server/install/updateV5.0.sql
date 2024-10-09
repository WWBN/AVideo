DROP TABLE `vast_campaigns_logs`;

CREATE TABLE IF NOT EXISTS `vast_campaigns_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NULL DEFAULT NULL,
  `type` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `vast_campaigns_has_videos_id` INT(11) NULL,
  `ip` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(400) NULL,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  `videos_id` INT(11) NULL,
  `json` TEXT NULL,
  `video_position` INT UNSIGNED NULL,
  `external_referrer` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_vast_campains_logs_users1_idx` (`users_id` ASC),
  INDEX `fk_vast_campaigns_logs_vast_campaigns_has_videos1_idx` (`vast_campaigns_has_videos_id` ASC),
  INDEX `fk_vast_campaigns_logs_videos1_idx` (`videos_id` ASC),
  INDEX `vast_campaigns_type` (`type` ASC),
  INDEX `vast_campaigns_created` (`created` ASC),
  INDEX `vast_campaigns_created2` (`created_php_time` ASC),
  INDEX `vast_campaigns_external` (`external_referrer` ASC),
  CONSTRAINT `fk_vast_campaigns_logs_vast_campaigns_has_videos1`
    FOREIGN KEY (`vast_campaigns_has_videos_id`)
    REFERENCES `vast_campaigns_has_videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_vast_campains_logs_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE SET NULL,
  CONSTRAINT `fk_vast_campaigns_logs_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;