-- MySQL Workbench Synchronization
-- Generated: 2017-04-25 19:11
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `likes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `like` INT(1) NOT NULL DEFAULT 0 COMMENT '1 = Like\n0 = Does not metter\n-1 = Dislike',
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `videos_id` INT(11) NOT NULL,
  `users_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_likes_videos1_idx` (`videos_id` ASC),
  INDEX `fk_likes_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_likes_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_likes_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `likes` 
DROP FOREIGN KEY `fk_likes_users1`;

ALTER TABLE `likes` 
ADD INDEX `fk_likes_videos1_idx` (`videos_id` ASC),
ADD INDEX `fk_likes_users1_idx` (`users_id` ASC),
DROP INDEX `fk_likes_users1_idx` ,
DROP INDEX `fk_likes_videos1_idx` ;

ALTER TABLE `likes` 
DROP FOREIGN KEY `fk_likes_videos1`;

ALTER TABLE `likes` ADD CONSTRAINT `fk_likes_videos1`
  FOREIGN KEY (`videos_id`)
  REFERENCES `videos` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_likes_users1`
  FOREIGN KEY (`users_id`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


UPDATE configurations SET  version = '2.0', modified = now() WHERE id = 1;