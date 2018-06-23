ALTER TABLE `configurations` ADD `authCanViewChart` TINYINT(2) NOT NULL DEFAULT '0' AFTER `authCanUploadVideos`;
ALTER TABLE `users` ADD `canViewChart` TINYINT(1) NOT NULL DEFAULT '0' AFTER `canUpload`;
UPDATE configurations SET  version = '5.7', modified = now() WHERE id = 1;
