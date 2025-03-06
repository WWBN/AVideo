ALTER TABLE `comments`
ADD COLUMN `live_transmitions_history_id` INT(11) NULL DEFAULT NULL,
ADD INDEX `fk_comments_live_transmitions_history1_idx` (`live_transmitions_history_id`),
ADD CONSTRAINT `fk_comments_live_transmitions_history1`
    FOREIGN KEY (`live_transmitions_history_id`)
    REFERENCES `live_transmitions_history` (`id`)
    ON DELETE SET NULL
    ON UPDATE SET NULL;

ALTER TABLE `comments`
ADD COLUMN `created_php_time` BIGINT UNSIGNED NULL;

ALTER TABLE `comments`
ADD COLUMN `modified_php_time` BIGINT UNSIGNED NULL;

-- Update the version in configurations table
UPDATE configurations SET version = '14.5', modified = now() WHERE id = 1;
