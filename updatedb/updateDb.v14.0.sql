ALTER TABLE `configurations` ADD COLUMN `description` TEXT NULL;

UPDATE configurations SET  version = '14.0', modified = now() WHERE id = 1;
