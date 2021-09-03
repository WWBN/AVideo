CREATE TABLE IF NOT EXISTS `scheduler_commands` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `callbackURL` VARCHAR(255) NOT NULL,
  `parameters` TEXT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `date_to_execute` DATETIME NOT NULL,
  `executed_in` DATETIME NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `callbackResponse` TEXT NULL,
  `timezone` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;