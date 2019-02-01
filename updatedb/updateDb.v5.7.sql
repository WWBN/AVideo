ALTER TABLE `configurations` ADD `authCanViewChart` TINYINT(2) NOT NULL DEFAULT '0' AFTER `authCanUploadVideos`;
ALTER TABLE `users` ADD `canViewChart` TINYINT(1) NOT NULL DEFAULT '0' AFTER `canUpload`;
ALTER TABLE `videos` CHANGE `type` `type` ENUM('audio','video','embed','linkVideo','linkAudio','torrent') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'video';
UPDATE configurations SET  version = '5.7', modified = now() WHERE id = 1;
