-- Allow larger group names

ALTER TABLE `users_groups` 
CHANGE COLUMN `group_name` `group_name` VARCHAR(255) NULL DEFAULT NULL;

UPDATE configurations SET  version = '9.3', modified = now() WHERE id = 1;