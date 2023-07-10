ALTER TABLE videos MODIFY COLUMN `order` INT(10) UNSIGNED DEFAULT NULL;
UPDATE videos SET  `order` = NULL WHERE id > 0;
UPDATE configurations SET  version = '12.5', modified = now() WHERE id = 1;