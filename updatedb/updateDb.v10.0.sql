-- Add Login control
-- Fix some layout issues
-- support for encoder 3.3 and dynamic resolutions for MP4 and webm


ALTER TABLE `playlists` 
ADD COLUMN `showOnTV` TINYINT NULL DEFAULT NULL,
ADD INDEX `showOnTVindex3` (`showOnTV` ASC);

UPDATE configurations SET  version = '10.0', modified = now() WHERE id = 1;