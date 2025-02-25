-- this update we had to update the sweet alert, you may need to update all your plugins
-- the filepath, in case we want to store the videos in a subdirectory of videos dir
-- filesize to start to count how much space the user is consuming.
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `videos` 
DROP FOREIGN KEY `fk_videos_sites1`,
DROP FOREIGN KEY `fk_videos_playlists1`;

ALTER TABLE `videos` 
ADD INDEX IF NOT EXISTS `video_status_idx` (`status` ASC),
ADD INDEX IF NOT EXISTS `video_type_idx` (`type` ASC) ;

ALTER TABLE `likes` 
ADD INDEX IF NOT EXISTS `likes_likes_idx` (`like` ASC);

UPDATE configurations SET  version = '9.0', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
