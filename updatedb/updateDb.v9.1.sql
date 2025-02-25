-- this update we had to update the sweet alert, you may need to update all your plugins
-- the filepath, in case we want to store the videos in a subdirectory of videos dir
-- filesize to start to count how much space the user is consuming.
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `categories` 
ADD INDEX IF NOT EXISTS `category_name_idx` (`name` ASC);

UPDATE configurations SET  version = '9.1', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
