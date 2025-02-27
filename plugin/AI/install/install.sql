CREATE TABLE IF NOT EXISTS `ai_responses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `elapsedTime` DOUBLE NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `videos_id` INT(11) NOT NULL,
  `price` DOUBLE NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ai_responses_videos1_idx` (`videos_id` ASC),
  CONSTRAINT `fk_ai_responses_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `ai_metatags_responses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `videoTitles` TEXT NULL,
  `keywords` TEXT NULL,
  `professionalDescription` TEXT NULL,
  `casualDescription` TEXT NULL,
  `shortSummary` TEXT NULL,
  `metaDescription` TEXT NULL,
  `rrating` VARCHAR(45) NULL,
  `rratingJustification` TEXT NULL,
  `prompt_tokens` INT UNSIGNED NULL,
  `completion_tokens` INT UNSIGNED NULL,
  `price_prompt_tokens` DOUBLE NULL,
  `price_completion_tokens` DOUBLE NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `ai_responses_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ai_metatags_responses_ai_responses1_idx` (`ai_responses_id` ASC),
  CONSTRAINT `fk_ai_metatags_responses_ai_responses1`
    FOREIGN KEY (`ai_responses_id`)
    REFERENCES `ai_responses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `ai_transcribe_responses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vtt` MEDIUMTEXT NULL,
  `language` VARCHAR(150) NULL,
  `duration` DOUBLE NULL,
  `text` MEDIUMTEXT NULL,
  `total_price` DOUBLE NULL,
  `size_in_bytes` INT NULL,
  `mp3_url` VARCHAR(400) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `ai_responses_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ai_transcribe_responses_ai_responses1_idx` (`ai_responses_id` ASC),
  CONSTRAINT `fk_ai_transcribe_responses_ai_responses1`
    FOREIGN KEY (`ai_responses_id`)
    REFERENCES `ai_responses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

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