ALTER TABLE `scheduler_commands`
ADD COLUMN `created_php_time` INT(11) NULL,
ADD COLUMN `time_to_execute` INT(11) NULL,
ADD INDEX `scheduler_commands_created_php_time` (`created_php_time` ASC),
ADD INDEX `scheduler_commands_time_to_execute` (`time_to_execute` ASC);

ALTER TABLE `email_to_user`
ADD COLUMN `created_php_time` INT(11) NULL,
ADD INDEX `email_to_user_created_php_time` (`created_php_time` ASC);