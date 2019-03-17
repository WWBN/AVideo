CREATE TABLE IF NOT EXISTS `live_transmition_history_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `live_transmitions_history_id` INT NOT NULL,
  `users_id` INT(11) NULL,
  `session_id` VARCHAR(45) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_transmition_history_log_live_transmitions_history1_idx` (`live_transmitions_history_id` ASC),
  CONSTRAINT `fk_live_transmition_history_log_live_transmitions_history1`
    FOREIGN KEY (`live_transmitions_history_id`)
    REFERENCES `live_transmitions_history` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB