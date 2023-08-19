ALTER TABLE `playlists`
ADD COLUMN `showOnFirstPage` TINYINT(1) UNSIGNED NULL DEFAULT 0,
ADD INDEX `showonFirstpage` (`showOnFirstPage` ASC);

UPDATE configurations SET  version = '12.9', modified = now() WHERE id = 1;
