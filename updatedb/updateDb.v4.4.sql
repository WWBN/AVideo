-- MySQL Workbench Synchronization
-- Generated: 2017-10-05 11:15
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


ALTER TABLE `videos` 
ADD COLUMN `next_videos_id` INT(11) NULL AFTER `videoLink`,
ADD INDEX `fk_videos_videos1_idx` (`next_videos_id` ASC);

ALTER TABLE `playlists_has_videos` 
ADD COLUMN `order` INT(11) NULL DEFAULT NULL AFTER `videos_id`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


UPDATE configurations SET  version = '4.4', modified = now() WHERE id = 1;