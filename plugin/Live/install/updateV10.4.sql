ALTER TABLE `live_transmitions_history` 
ADD COLUMN `max_viewers_sametime` INT(10) UNSIGNED NULL DEFAULT NULL,
ADD COLUMN `total_viewers` INT(10) UNSIGNED NULL DEFAULT NULL;
