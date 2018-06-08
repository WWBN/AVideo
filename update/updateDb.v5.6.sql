ALTER TABLE `users` ADD COLUMN `analyticsCode` VARCHAR(45) NULL DEFAULT NULL ;

UPDATE configurations SET  version = '5.6', modified = now() WHERE id = 1;