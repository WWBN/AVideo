ALTER TABLE `meet_schedule` 
ADD COLUMN `timezone` VARCHAR(255) NULL DEFAULT NULL AFTER `meet_code`;