ALTER TABLE `configurations`
MODIFY COLUMN `theme` VARCHAR(255) NULL DEFAULT 'default';

UPDATE configurations SET  version = '14.3', modified = now() WHERE id = 1;