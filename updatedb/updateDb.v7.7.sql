SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


ALTER TABLE `videos` 
CHANGE COLUMN `type` `type` ENUM('audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie') NOT NULL DEFAULT 'video' ,
ADD COLUMN `serie_playlists_id` INT(11) NULL DEFAULT NULL,
ADD INDEX `fk_videos_playlists1_idx` (`serie_playlists_id` ASC);

ALTER TABLE `videos` 
ADD CONSTRAINT `fk_videos_playlists1`
  FOREIGN KEY (`serie_playlists_id`)
  REFERENCES `playlists` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


UPDATE configurations SET  version = '7.7', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
