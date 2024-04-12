ALTER TABLE `configurations`
MODIFY COLUMN `theme` TEXT NULL DEFAULT 'default';

UPDATE configurations SET  version = '14.3', modified = now() WHERE id = 1;