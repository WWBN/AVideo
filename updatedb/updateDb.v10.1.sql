-- When upload a video to a certain category, this video will be part of a user group automatically
-- one category can add your video in more than one user group

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
ENGINE = Aria;

-- Allow user add some extra info in JSON format, for example bank info and other thing
-- This is based on users_extra_info table 

ALTER TABLE `users` 
ADD COLUMN `extra_info` TEXT NULL DEFAULT NULL;

-- this will handle the custom options for the users

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

-- add full text search https://github.com/WWBN/AVideo/issues/4343

ALTER TABLE `categories` 
ADD FULLTEXT INDEX `index7cname` (`name`);

ALTER TABLE `categories` 
ADD FULLTEXT INDEX `index8cdescr` (`description`);

ALTER TABLE `videos` 
ADD FULLTEXT INDEX `index17vname` (`title`);

ALTER TABLE `videos` 
ADD FULLTEXT INDEX `index18vdesc` (`description`);

UPDATE configurations SET  version = '10.1', modified = now() WHERE id = 1;
