-- MySQL Workbench Forward Engineering

CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `timeInSeconds` INT NOT NULL,
  `name` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `videos_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_bookmarks_videos_idx` (`videos_id` ASC),
  CONSTRAINT `fk_bookmarks_videos`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;