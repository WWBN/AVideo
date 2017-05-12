-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NULL,
  `email` VARCHAR(45) NULL,
  `password` VARCHAR(45) NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  `isAdmin` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('a', 'i') NOT NULL DEFAULT 'a',
  `photoURL` VARCHAR(255) NULL,
  `lastLogin` DATETIME NULL,
  `recoverPass` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_UNIQUE` (`user` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `clean_name` VARCHAR(45) NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  `iconClass` VARCHAR(45) NOT NULL DEFAULT 'fa fa-folder',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `clean_name_UNIQUE` (`clean_name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `videos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `videos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `clean_title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `views_count` INT NOT NULL DEFAULT 0,
  `status` ENUM('a', 'i', 'e', 'x', 'd', 'xmp4', 'xwebm', 'xmp3', 'xogg', 'ximg') NOT NULL DEFAULT 'e' COMMENT 'a = active\ni = inactive\ne = encoding\nx = encoding error\nd = downloading\nxmp4 = encoding mp4 error \nxwebm = encoding webm error \nxmp3 = encoding mp3 error \nxogg = encoding ogg error \nximg = get image error\nad = Advertising',
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  `users_id` INT NOT NULL,
  `categories_id` INT NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `duration` VARCHAR(15) NOT NULL,
  `type` ENUM('audio', 'video') NOT NULL DEFAULT 'video',
  `videoDownloadedLink` VARCHAR(255) NULL,
  `order` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `fk_videos_users_idx` (`users_id` ASC),
  INDEX `fk_videos_categories1_idx` (`categories_id` ASC),
  UNIQUE INDEX `clean_title_UNIQUE` (`clean_title` ASC),
  INDEX `index5` (`order` ASC),
  CONSTRAINT `fk_videos_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_videos_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `comment` VARCHAR(255) NOT NULL,
  `videos_id` INT NOT NULL,
  `users_id` INT NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_comments_videos1_idx` (`videos_id` ASC),
  INDEX `fk_comments_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_comments_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `configurations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `configurations` (
  `id` INT NOT NULL,
  `video_resolution` VARCHAR(12) NOT NULL,
  `users_id` INT NOT NULL,
  `version` VARCHAR(10) NOT NULL,
  `webSiteTitle` VARCHAR(45) NOT NULL DEFAULT 'YouPHPTube',
  `language` VARCHAR(6) NOT NULL DEFAULT 'en',
  `contactEmail` VARCHAR(45) NOT NULL,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  `authGoogle_id` VARCHAR(255) NULL,
  `authGoogle_key` VARCHAR(255) NULL,
  `authGoogle_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `authFacebook_id` VARCHAR(255) NULL,
  `authFacebook_key` VARCHAR(255) NULL,
  `authFacebook_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `authCanUploadVideos` TINYINT(1) NOT NULL DEFAULT 0,
  `authCanComment` TINYINT(1) NOT NULL DEFAULT 1,
  `ffprobeDuration` VARCHAR(400) NULL,
  `ffmpegImage` VARCHAR(400) NULL,
  `ffmpegMp4` VARCHAR(400) NULL,
  `ffmpegMp4Portrait` VARCHAR(400) NULL,
  `ffmpegWebm` VARCHAR(400) NULL,
  `ffmpegWebmPortrait` VARCHAR(400) NULL,
  `ffmpegMp3` VARCHAR(400) NULL,
  `ffmpegOgg` VARCHAR(400) NULL,
  `youtubeDl` VARCHAR(400) NULL,
  `ffmpegPath` VARCHAR(255) NULL,
  `youtubeDlPath` VARCHAR(255) NULL,
  `exiftool` VARCHAR(255) NULL,
  `exiftoolPath` VARCHAR(255) NULL,
  `head` TEXT NULL,
  `logo` VARCHAR(255) NULL,
  `logo_small` VARCHAR(255) NULL,
  `adsense` TEXT NULL,
  `mode` ENUM('Youtube', 'Gallery') NULL DEFAULT 'Youtube',
  PRIMARY KEY (`id`),
  INDEX `fk_configurations_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_configurations_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `videos_statistics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `videos_statistics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `when` DATETIME NOT NULL,
  `ip` VARCHAR(45) NULL,
  `users_id` INT NULL,
  `videos_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_videos_statistics_users1_idx` (`users_id` ASC),
  INDEX `fk_videos_statistics_videos1_idx` (`videos_id` ASC),
  CONSTRAINT `fk_videos_statistics_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_videos_statistics_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `likes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `likes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `like` INT(1) NOT NULL DEFAULT 0 COMMENT '1 = Like\n0 = Does not metter\n-1 = Dislike',
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `videos_id` INT NOT NULL,
  `users_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_likes_videos1_idx` (`videos_id` ASC),
  INDEX `fk_likes_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_likes_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_likes_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `users_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(45) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `users_has_users_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_has_users_groups` (
  `users_id` INT NOT NULL,
  `users_groups_id` INT NOT NULL,
  PRIMARY KEY (`users_id`, `users_groups_id`),
  INDEX `fk_users_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_users_has_users_groups_users1_idx` (`users_id` ASC),
  UNIQUE INDEX `index_user_groups_unique` (`users_groups_id` ASC, `users_id` ASC),
  CONSTRAINT `fk_users_has_users_groups_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_users_has_users_groups_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `videos_group_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `videos_group_view` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_groups_id` INT NOT NULL,
  `videos_id` INT NOT NULL,
  INDEX `fk_videos_group_view_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_videos_group_view_videos1_idx` (`videos_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_videos_group_view_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_videos_group_view_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `video_ads`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `video_ads` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ad_title` VARCHAR(255) NOT NULL,
  `starts` DATETIME NOT NULL,
  `finish` DATETIME NULL,
  `skip_after_seconds` INT(4) NULL,
  `redirect` VARCHAR(300) NULL,
  `finish_max_clicks` INT NULL,
  `finish_max_prints` INT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `videos_id` INT NOT NULL,
  `categories_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_video_ads_videos1_idx` (`videos_id` ASC),
  INDEX `fk_video_ads_categories1_idx` (`categories_id` ASC),
  CONSTRAINT `fk_video_ads_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_video_ads_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `video_ads_logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `video_ads_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `datetime` DATETIME NOT NULL,
  `clicked` TINYINT(1) NOT NULL DEFAULT 0,
  `ip` VARCHAR(45) NOT NULL,
  `video_ads_id` INT NOT NULL,
  `users_id` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_video_ads_logs_users1_idx` (`users_id` ASC),
  INDEX `fk_video_ads_logs_video_ads1_idx` (`video_ads_id` ASC),
  CONSTRAINT `fk_video_ads_logs_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_video_ads_logs_video_ads1`
    FOREIGN KEY (`video_ads_id`)
    REFERENCES `video_ads` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
