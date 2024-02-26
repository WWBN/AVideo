-- Allow differents status code

ALTER TABLE `videos` 
CHANGE COLUMN `status` `status` VARCHAR(16) NOT NULL DEFAULT 'e';

UPDATE configurations SET  version = '10.8', modified = now() WHERE id = 1;