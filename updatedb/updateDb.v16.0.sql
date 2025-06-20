ALTER TABLE `videos_statistics`
ADD COLUMN `user_agent` VARCHAR(255) NULL,
ADD COLUMN `app` VARCHAR(45) NULL,
ADD INDEX `video_statistics_ua` (`user_agent` ASC),
ADD INDEX `video_statistics_app` (`app` ASC);
UPDATE configurations SET  version = '16.0', modified = now() WHERE id = 1;