ALTER TABLE `scheduler_commands` 
ADD COLUMN `repeat_minute` INT(11) NULL DEFAULT NULL,
ADD COLUMN `repeat_hour` INT(11) NULL DEFAULT NULL,
ADD COLUMN `repeat_day_of_month` INT(11) NULL DEFAULT NULL,
ADD COLUMN `repeat_month` INT(11) NULL DEFAULT NULL,
ADD COLUMN `repeat_day_of_week` INT(11) NULL DEFAULT NULL,
ADD COLUMN `type` VARCHAR(45)  DEFAULT NULL,
CHANGE COLUMN `date_to_execute` `date_to_execute` DATETIME NULL DEFAULT NULL ;