-- add a metadata table to hold all informations obtained from ffprobe
-- The constrain of videos.id breaks any change at mine, I do not know why,
-- but this is why it is commented out.
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `videos_metadata` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `videos_id` INT NOT NULL,
  `resolution` VARCHAR(12) NOT NULL,
  `format` VARCHAR(12) NOT NULL,
  `stream_id` INT NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`videos_id`, `resolution`, `format`, `stream_id`, `name`),
  INDEX `fk_videos_metadata_videos1_idx` (`videos_id` ASC),
  CONSTRAINT `fk_videos_metadata_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB;

UPDATE configurations SET  version = '10.4', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;