-- this update we had to update the sweet alert, you may need to update all your plugins
-- the filepath, in case we want to store the videos in a subdirectory of videos dir
-- filesize to start to count how much space the user is consuming.
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `videos` 
CHANGE COLUMN `type` `type` ENUM('audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie', 'zip') NOT NULL DEFAULT 'video' ;


UPDATE configurations SET  version = '8.8', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
