ALTER TABLE `configurations` 
CHANGE COLUMN `language` `language` VARCHAR(25) NOT NULL DEFAULT 'en_US';
UPDATE configurations SET  version = '12.8', modified = now() WHERE id = 1;