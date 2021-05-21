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
  `email` VARCHAR(254) NULL,
  `password` VARCHAR(145) NOT NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` DATETIME NOT NULL,
  `isAdmin` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('a', 'i') NOT NULL DEFAULT 'a',
  `photoURL` VARCHAR(255) NULL,
  `lastLogin` DATETIME NULL,
  `recoverPass` VARCHAR(255) NULL,
  `backgroundURL` VARCHAR(255) NULL,
  `canStream` TINYINT(1) NULL,
  `canUpload` TINYINT(1) NULL,
  `canCreateMeet` TINYINT(1) NULL,
  `canViewChart` TINYINT(1) NOT NULL DEFAULT 0,
  `about` TEXT NULL,
  `channelName` VARCHAR(45) NULL,
  `emailVerified` TINYINT(1) NOT NULL DEFAULT 0,
  `analyticsCode` VARCHAR(45) NULL DEFAULT NULL,
  `externalOptions` TEXT NULL,
  `first_name` VARCHAR(255) NULL DEFAULT NULL,
  `last_name` VARCHAR(255) NULL DEFAULT NULL,
  `address` VARCHAR(255) NULL DEFAULT NULL,
  `zip_code` VARCHAR(45) NULL DEFAULT NULL,
  `country` VARCHAR(100) NULL DEFAULT NULL,
  `region` VARCHAR(100) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `donationLink` VARCHAR(225) NULL DEFAULT NULL,
  `extra_info` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_UNIQUE` (`user` ASC))
ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `users_blob` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `blob` LONGBLOB NULL,
  `users_id` INT NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_users_document_image_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_users_document_image_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `clean_name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL,
  `nextVideoOrder` INT(2) NOT NULL DEFAULT '0',
  `parentId` INT NOT NULL DEFAULT '0',
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  `iconClass` VARCHAR(45) NOT NULL DEFAULT 'fa fa-folder',
  `users_id` INT(11) NOT NULL DEFAULT 1,
  `private` TINYINT(1) NULL DEFAULT 0,
  `allow_download` TINYINT(1) NULL DEFAULT 1,
  `order` INT(11) NULL DEFAULT NULL,
  `suggested` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_categories_users1_idx` (`users_id` ASC),
  INDEX `clean_name_INDEX2` (`clean_name` ASC),
  INDEX `sortcategoryOrderIndex` (`order` ASC),
  INDEX `category_name_idx` (`name` ASC),
  INDEX `categoriesindex9suggested` (`suggested` ASC),
  FULLTEXT INDEX `index7cname` (`name`),
  FULLTEXT INDEX `index8cdescr` (`description`),
  UNIQUE INDEX `clean_name_UNIQUE` (`clean_name` ASC), 
  CONSTRAINT `fk_categories_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `sites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL DEFAULT NULL,
  `url` VARCHAR(255)  NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `status` CHAR(1) NULL DEFAULT NULL,
  `secret` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `videos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `videos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) NOT NULL,
  `clean_title` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,
  `views_count` INT NOT NULL DEFAULT 0,
  `views_count_25` INT(11) NULL DEFAULT 0,
  `views_count_50` INT(11) NULL DEFAULT 0,
  `views_count_75` INT(11) NULL DEFAULT 0,
  `views_count_100` INT(11) NULL DEFAULT 0,
  `status` VARCHAR(16) NOT NULL DEFAULT 'e' ,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  `users_id` INT NOT NULL,
  `categories_id` INT NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `duration` VARCHAR(15) NOT NULL,
  `type` ENUM('audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie', 'zip') NOT NULL DEFAULT 'video',
  `videoDownloadedLink` VARCHAR(255) NULL,
  `order` INT UNSIGNED NOT NULL DEFAULT 1,
  `rotation` SMALLINT NULL DEFAULT 0,
  `zoom` FLOAT NULL DEFAULT 1,
  `youtubeId` VARCHAR(45) NULL,
  `videoLink` VARCHAR(255) NULL,
  `next_videos_id` INT NULL,
  `isSuggested` INT(1) NOT NULL DEFAULT 0,
  `trailer1` VARCHAR(255) NULL DEFAULT NULL,
  `trailer2` VARCHAR(255) NULL DEFAULT NULL,
  `trailer3` VARCHAR(255) NULL DEFAULT NULL,
  `rate` FLOAT(4,2) NULL DEFAULT NULL,
  `can_download` TINYINT(1) NULL DEFAULT NULL,
  `can_share` TINYINT(1) NULL DEFAULT NULL,
  `rrating` VARCHAR(45) NULL DEFAULT NULL,
  `externalOptions` TEXT NULL DEFAULT NULL,
  `only_for_paid` TINYINT(1) NULL DEFAULT NULL,
  `serie_playlists_id` INT(11) NULL DEFAULT NULL,
  `sites_id` INT(11) NULL,
  `video_password` VARCHAR(45) NULL DEFAULT NULL,
  `encoderURL` VARCHAR(255) NULL DEFAULT NULL,
  `filepath` VARCHAR(255) NULL DEFAULT NULL,
  `filesize` BIGINT(19) UNSIGNED NULL DEFAULT 0,
  `live_transmitions_history_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_videos_users_idx` (`users_id` ASC),
  INDEX `fk_videos_categories1_idx` (`categories_id` ASC),
  UNIQUE INDEX `clean_title_UNIQUE` (`clean_title` ASC),
  INDEX `index5` (`order` ASC),
  INDEX `fk_videos_videos1_idx` (`next_videos_id` ASC),
  INDEX `fk_videos_sites1_idx` (`sites_id` ASC),
  INDEX `clean_title_INDEX` (`clean_title` ASC),
  INDEX `video_filename_INDEX` (`filename` ASC),
  INDEX `video_status_idx` (`status` ASC),
  INDEX `video_type_idx` (`type` ASC) ,
  INDEX `fk_videos_live_transmitions_history1_idx` (`live_transmitions_history_id` ASC),
  FULLTEXT INDEX `index17vname` (`title`),
  FULLTEXT INDEX `index18vdesc` (`description`),
  CONSTRAINT `fk_videos_sites1`
    FOREIGN KEY (`sites_id`)
    REFERENCES `sites` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_videos_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_videos_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_videos_videos1`
    FOREIGN KEY (`next_videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
CONSTRAINT `fk_videos_playlists1`
  FOREIGN KEY (`serie_playlists_id`)
  REFERENCES `playlists` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `videos_metadata` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `videos_id` INT NOT NULL,
  `resolution` VARCHAR(12) NOT NULL,
  `format` VARCHAR(12) NOT NULL,
  `stream_id` INT NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`videos_id`, `resolution`, `format`, `stream_id`, `name`),
  INDEX `fk_videos_metadata_videos1_idx` (`videos_id` ASC),
  CONSTRAINT `fk_videos_metadata_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `comment` TEXT NOT NULL,
  `videos_id` INT NOT NULL,
  `users_id` INT NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  `comments_id_pai` INT NULL,
  `pin` INT(1) NOT NULL DEFAULT 0 COMMENT 'If = 1 will be on the top',
  PRIMARY KEY (`id`),
  INDEX `fk_comments_videos1_idx` (`videos_id` ASC),
  INDEX `fk_comments_users1_idx` (`users_id` ASC),
  INDEX `fk_comments_comments1_idx` (`comments_id_pai` ASC),
  CONSTRAINT `fk_comments_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_comments1`
    FOREIGN KEY (`comments_id_pai`)
    REFERENCES `comments` (`id`)
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
  `webSiteTitle` VARCHAR(45) NOT NULL DEFAULT 'AVideo',
  `language` VARCHAR(6) NOT NULL DEFAULT 'en',
  `contactEmail` VARCHAR(254) NOT NULL,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  `authGoogle_id` VARCHAR(255) NULL,
  `authGoogle_key` VARCHAR(255) NULL,
  `authGoogle_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `authFacebook_id` VARCHAR(255) NULL,
  `authFacebook_key` VARCHAR(255) NULL,
  `authFacebook_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `authCanUploadVideos` TINYINT(1) NOT NULL DEFAULT 0,
  `authCanViewChart` TINYINT(2) NOT NULL DEFAULT 0,
  `authCanComment` TINYINT(1) NOT NULL DEFAULT 1,
  `head` TEXT NULL,
  `logo` VARCHAR(255) NULL,
  `logo_small` VARCHAR(255) NULL,
  `adsense` TEXT NULL,
  `mode` ENUM('Youtube', 'Gallery') NULL DEFAULT 'Youtube',
  `disable_analytics` TINYINT(1) NULL DEFAULT 0,
  `disable_youtubeupload` TINYINT(1) NULL DEFAULT 0,
  `allow_download` TINYINT(1) NULL DEFAULT 0,
  `session_timeout` INT NULL DEFAULT 3600,
  `autoplay` TINYINT(1) NULL,
  `theme` VARCHAR(45) NULL DEFAULT 'default',
  `smtp` TINYINT(1) NULL,
  `smtpAuth` TINYINT(1) NULL,
  `smtpSecure` VARCHAR(255) NULL COMMENT '\'ssl\'; // secure transfer enabled REQUIRED for Gmail',
  `smtpHost` VARCHAR(255) NULL COMMENT '\"smtp.gmail.com\"',
  `smtpUsername` VARCHAR(255) NULL COMMENT '\"email@gmail.com\"',
  `smtpPassword` VARCHAR(255) NULL,
  `smtpPort` INT NULL,
  `encoderURL` VARCHAR(255) NULL,
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
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `lastVideoTime` INT(11) NULL DEFAULT NULL,
  `session_id` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_videos_statistics_users1_idx` (`users_id` ASC),
  INDEX `fk_videos_statistics_videos1_idx` (`videos_id` ASC),
  INDEX `when_statisci` (`when` ASC),
  INDEX `session_id_statistics` (`session_id` ASC),
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
  INDEX `likes_likes_idx` (`like` ASC),
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
  `group_name` VARCHAR(255) NULL,
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
-- Table `subscribes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscribes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `status` ENUM('a', 'i') NOT NULL DEFAULT 'a',
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `ip` VARCHAR(45) NULL,
  `users_id` INT NOT NULL DEFAULT 1 COMMENT 'subscribes to user channel',
  `notify` TINYINT(1) NOT NULL DEFAULT 1,
  `subscriber_users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_subscribes_users1_idx` (`users_id` ASC),
  INDEX `fk_subscribes_users2_idx` (`subscriber_users_id` ASC),
  CONSTRAINT `fk_subscribes_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_subscribes_users2`
    FOREIGN KEY (`subscriber_users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `playlists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `playlists` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `users_id` INT(11) NOT NULL,
  `status` ENUM('public', 'private', 'unlisted', 'favorite', 'watch_later') NOT NULL DEFAULT 'public',
  `showOnTV` TINYINT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_playlists_users1_idx` (`users_id` ASC),
  INDEX `showOnTVindex3` (`showOnTV` ASC),
  CONSTRAINT `fk_playlists_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `playlists_has_videos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `playlists_has_videos` (
  `playlists_id` INT NOT NULL,
  `videos_id` INT NOT NULL,
  `order` INT NULL,
  PRIMARY KEY (`playlists_id`, `videos_id`),
  INDEX `fk_playlists_has_videos_videos1_idx` (`videos_id` ASC),
  INDEX `fk_playlists_has_videos_playlists1_idx` (`playlists_id` ASC),
  CONSTRAINT `fk_playlists_has_videos_playlists1`
    FOREIGN KEY (`playlists_id`)
    REFERENCES `playlists` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_playlists_has_videos_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `plugins`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `plugins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(45) NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `object_data` TEXT NULL,
  `name` VARCHAR(255) NOT NULL,
  `dirName` VARCHAR(255) NOT NULL,
  `pluginversion` VARCHAR(6) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uuid_UNIQUE` (`uuid` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comments_likes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments_likes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `like` INT(1) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `users_id` INT NOT NULL,
  `comments_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_comments_likes_users1_idx` (`users_id` ASC),
  INDEX `fk_comments_likes_comments1_idx` (`comments_id` ASC),
  CONSTRAINT `fk_comments_likes_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_likes_comments1`
    FOREIGN KEY (`comments_id`)
    REFERENCES `comments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `category_type_cache`
-- -----------------------------------------------------
CREATE TABLE `category_type_cache` (
  `categoryId` int(11) NOT NULL,
  `type` int(2) NOT NULL COMMENT '0=both, 1=audio, 2=video' DEFAULT 0,
  `manualSet` int(1) NOT NULL COMMENT '0=auto, 1=manual' DEFAULT 0

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `categories_has_users_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `categories_id` INT(11) NOT NULL,
  `users_groups_id` INT(11) NOT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  PRIMARY KEY (`id`),
  INDEX `fk_categories_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_categories_has_users_groups_categories1_idx` (`categories_id` ASC),
  CONSTRAINT `fk_categories_has_users_groups_categories1`
    FOREIGN KEY (`categories_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_categories_has_users_groups_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `users_extra_info` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `field_name` VARCHAR(45) NOT NULL,
  `field_type` VARCHAR(45) NOT NULL,
  `field_options` TEXT NULL,
  `field_default_value` VARCHAR(45) NULL,
  `parameters` TEXT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `ordersortusers_extra_info` USING BTREE (`order`))
ENGINE = InnoDB;

ALTER TABLE `category_type_cache`
  ADD UNIQUE KEY `categoryId` (`categoryId`);

ALTER TABLE `plugins` 
ADD INDEX `plugin_status` (`status` ASC);

ALTER TABLE `videos` 
ADD INDEX `videos_status_index` (`status` ASC),
ADD INDEX `is_suggested_index` (`isSuggested` ASC),
ADD INDEX `views_count_index` (`views_count` ASC),
ADD INDEX `filename_index` (`filename` ASC);

COMMIT;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
