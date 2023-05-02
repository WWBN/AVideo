ALTER TABLE `scheduler_commands` 
ADD COLUMN `videos_id` INT(11) NULL DEFAULT NULL,
ADD INDEX `fk_scheduler_commands_videos1_idx` (`videos_id` ASC);

ALTER TABLE `scheduler_commands` 
ADD CONSTRAINT `fk_scheduler_commands_videos1`
  FOREIGN KEY (`videos_id`)
  REFERENCES `videos` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
