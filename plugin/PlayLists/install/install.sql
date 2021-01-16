CREATE TABLE IF NOT EXISTS `playlists_schedules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `playlists_id` INT NOT NULL,
  `name` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `status` CHAR(1) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `loop` TINYINT NULL,
  `start_datetime` DATETIME NULL,
  `finish_datetime` DATETIME NULL,
  `repeat` CHAR(1) NULL,
  `parameters` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_playlists_schedules_playlists_idx` (`playlists_id` ASC),
  CONSTRAINT `fk_playlists_schedules_playlists`
    FOREIGN KEY (`playlists_id`)
    REFERENCES `playlists` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;