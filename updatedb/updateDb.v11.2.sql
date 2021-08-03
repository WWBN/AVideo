SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `videos` 
ADD COLUMN `total_seconds_watching` INT(11) NULL DEFAULT 0 AFTER `live_transmitions_history_id`,
ADD INDEX `total_sec_watchinindex` (`total_seconds_watching` ASC);

ALTER TABLE `videos_statistics` 
ADD COLUMN `seconds_watching_video` INT(11) NULL DEFAULT NULL AFTER `session_id`,
ADD INDEX `sec_watchin_videos` (`seconds_watching_video` ASC);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

UPDATE configurations SET  version = '11.2', modified = now() WHERE id = 1;