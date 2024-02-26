ALTER TABLE `videos_statistics`
ADD COLUMN `timezone` VARCHAR(255) NULL,
ADD COLUMN `created_php_time` INT(11) NULL,
ADD INDEX `videos_statistics_php_time` (`created_php_time` ASC);
UPDATE configurations SET  version = '12.6', modified = now() WHERE id = 1;