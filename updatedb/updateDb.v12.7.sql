ALTER TABLE `videos_statistics`
ADD COLUMN `rewarded` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
ADD INDEX `videos_statistics_rewarded` (`rewarded` ASC);
UPDATE configurations SET  version = '12.7', modified = now() WHERE id = 1;