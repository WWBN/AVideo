-- Add Login control
-- Fix some layout issues
-- support for encoder 3.3 and dynamic resolutions for MP4 and webm

ALTER TABLE `users` 
CHANGE COLUMN `email` `email` VARCHAR(254) NULL,
CHANGE COLUMN `created` `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `configurations` 
CHANGE COLUMN `contactEmail` `contactEmail` VARCHAR(254) NOT NULL;

UPDATE configurations SET  version = '9.7', modified = now() WHERE id = 1;