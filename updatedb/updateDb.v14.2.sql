ALTER TABLE `users` ADD COLUMN `birth_date` DATE NULL DEFAULT NULL;

UPDATE configurations SET  version = '14.2', modified = now() WHERE id = 1;
