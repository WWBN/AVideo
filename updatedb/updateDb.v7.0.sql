SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `plugins` 
ADD INDEX `plugin_status` (`status` ASC);

ALTER TABLE `videos` 
ADD INDEX `videos_status_index` (`status` ASC),
ADD INDEX `is_suggested_index` (`isSuggested` ASC),
ADD INDEX `views_count_index` (`views_count` ASC),
ADD INDEX `filename_index` (`filename` ASC);

UPDATE configurations SET  version = '7.0', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
