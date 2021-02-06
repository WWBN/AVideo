SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `videos` 
MODIFY COLUMN status 
ENUM('a', 'k', 'i', 'e', 'x', 'd', 'xmp4', 'xwebm', 'xmp3', 'xogg', 'ximg', 'u', 'p', 't') NOT NULL DEFAULT 'e' COMMENT 'a = active\nk = active and encofing\ni = inactive\ne = encoding\nx = encoding error\nd = downloading\nu = Unlisted\np = private\nxmp4 = encoding mp4 error \nxwebm = encoding webm error \nxmp3 = encoding mp3 error \nxogg = encoding ogg error \nximg = get image error\nt = Transfering';

UPDATE configurations SET  version = '9.8', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
