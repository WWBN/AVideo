ALTER TABLE `videos` ADD COLUMN `made_for_kids` TINYINT(1) NOT NULL DEFAULT 0,
ADD INDEX `index_made_for_kids` (`made_for_kids` ASC);

UPDATE configurations SET  version = '14.1', modified = now() WHERE id = 1;
