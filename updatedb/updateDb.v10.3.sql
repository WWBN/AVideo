-- Allow save recoded live info on the video
ALTER TABLE `videos` 
ADD COLUMN `live_transmitions_history_id` INT(11) NULL DEFAULT NULL,
ADD INDEX `fk_videos_live_transmitions_history1_idx` (`live_transmitions_history_id` ASC);

UPDATE configurations SET  version = '10.3', modified = now() WHERE id = 1;