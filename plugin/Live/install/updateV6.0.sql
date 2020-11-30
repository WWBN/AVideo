ALTER TABLE `live_transmitions` 
ADD COLUMN `showOnTV` TINYINT NULL DEFAULT NULL,
ADD INDEX `showOnTVLiveindex3` (`showOnTV` ASC);