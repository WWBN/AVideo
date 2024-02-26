ALTER TABLE `monetize_user_reward_log`
ADD COLUMN `created_php_time` INT(11) NULL,
ADD INDEX `monetize_user_reward_log_created_php_time` (`created_php_time` ASC);