CREATE TABLE IF NOT EXISTS `scheduler_commands` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `callbackURL` VARCHAR(255) NOT NULL,
  `parameters` TEXT NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `date_to_execute` DATETIME NULL,
  `executed_in` DATETIME NULL DEFAULT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `callbackResponse` TEXT NULL DEFAULT NULL,
  `timezone` VARCHAR(255) NULL,
  `repeat_minute` INT NULL,
  `repeat_hour` INT NULL,
  `repeat_day_of_month` INT NULL,
  `repeat_month` INT NULL,
  `repeat_day_of_week` INT NULL,
  `day_of_week` INT NULL,
  `videos_id` INT(11) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_scheduler_commands_videos1_idx` (`videos_id` ASC) VISIBLE,
  CONSTRAINT `fk_scheduler_commands_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;