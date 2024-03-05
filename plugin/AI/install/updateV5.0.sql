CREATE TABLE IF NOT EXISTS `ai_responses_json` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `response` MEDIUMTEXT NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `ai_type` VARCHAR(45) NOT NULL,
  `ai_responses_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `typeAiIndex` (`ai_type` ASC) ,
  INDEX `fk_ai_responses_json_ai_responses1_idx` (`ai_responses_id` ASC) ,
  CONSTRAINT `fk_ai_responses_json_ai_responses1`
    FOREIGN KEY (`ai_responses_id`)
    REFERENCES `ai_responses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;