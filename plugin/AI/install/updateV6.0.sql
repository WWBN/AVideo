CREATE TABLE IF NOT EXISTS `ai_scheduler` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `json` MEDIUMTEXT NOT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `ai_scheduler_type` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `created_php_time` BIGINT NULL,
  `modified_php_time` BIGINT NULL,
  PRIMARY KEY (`id`),
  INDEX `status_ai_schedler_index` (`status` ASC))
ENGINE = InnoDB;