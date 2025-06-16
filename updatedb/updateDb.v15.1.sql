ALTER TABLE `videos` ADD COLUMN `created_php_time` BIGINT UNSIGNED NULL;
ALTER TABLE `videos` ADD COLUMN `modified_php_time` BIGINT UNSIGNED NULL;

UPDATE videos SET created_php_time = UNIX_TIMESTAMP(created),  modified_php_time = UNIX_TIMESTAMP(modified) WHERE created_php_time IS NULL OR modified_php_time IS NULL;

UPDATE configurations SET version = '15.1', modified = now() WHERE id = 1;

