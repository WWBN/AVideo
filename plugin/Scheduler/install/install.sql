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
  `type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;