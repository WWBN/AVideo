ALTER TABLE `live_transmition_history_log`
ADD COLUMN `user_agent` VARCHAR(255) NULL DEFAULT NULL,
ADD COLUMN `ip` VARCHAR(45) NULL DEFAULT NULL,
ADD INDEX `ua_index6_log` (`user_agent` ASC),
ADD INDEX `ip_index7_log` (`ip` ASC);
