-- MySQL Workbench Synchronization
-- Generated: 2017-05-02 10:37
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(45) NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `users_has_users_groups` (
  `users_id` INT(11) NOT NULL,
  `users_groups_id` INT(11) NOT NULL,
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
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `videos_group_view` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `users_groups_id` INT(11) NOT NULL,
  `videos_id` INT(11) NOT NULL,
  INDEX `fk_videos_group_view_users_groups1_idx` (`users_groups_id` ASC),
  INDEX `fk_videos_group_view_videos1_idx` (`videos_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_videos_group_view_users_groups1`
    FOREIGN KEY (`users_groups_id`)
    REFERENCES `users_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_videos_group_view_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `users_has_users_groups` 
DROP FOREIGN KEY `fk_users_has_users_groups_users_groups1`;

ALTER TABLE `videos_group_view` 
DROP FOREIGN KEY `fk_videos_group_view_users_groups1`,
DROP FOREIGN KEY `fk_videos_group_view_videos1`;

ALTER TABLE `users_has_users_groups` 
ADD INDEX `fk_users_has_users_groups_users_groups1_idx` (`users_groups_id` ASC),
ADD INDEX `fk_users_has_users_groups_users1_idx` (`users_id` ASC),
DROP INDEX `fk_users_has_users_groups_users1_idx` ,
DROP INDEX `fk_users_has_users_groups_users_groups1_idx` ;

ALTER TABLE `videos_group_view` 
ADD INDEX `fk_videos_group_view_users_groups1_idx` (`users_groups_id` ASC),
ADD INDEX `fk_videos_group_view_videos1_idx` (`videos_id` ASC),
DROP INDEX `fk_videos_group_view_videos1_idx` ,
DROP INDEX `fk_videos_group_view_users_groups1_idx` ;

ALTER TABLE `users_has_users_groups` 
ADD CONSTRAINT `fk_users_has_users_groups_users_groups1`
  FOREIGN KEY (`users_groups_id`)
  REFERENCES `users_groups` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `videos_group_view` 
ADD CONSTRAINT `fk_videos_group_view_users_groups1`
  FOREIGN KEY (`users_groups_id`)
  REFERENCES `users_groups` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_videos_group_view_videos1`
  FOREIGN KEY (`videos_id`)
  REFERENCES `videos` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;



UPDATE configurations SET  version = '2.3', modified = now() WHERE id = 1;
