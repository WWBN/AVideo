SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- version 1
ALTER TABLE `users` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `videos` 
DROP COLUMN `type`;

ALTER TABLE `videos` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
CHANGE COLUMN `status` `status` ENUM('a', 'i', 'e', 'x') NOT NULL DEFAULT 'e' COMMENT 'a = active\ni = inactive\ne = encoding\nx = encoding error' ,
ADD COLUMN `type` ENUM('audio', 'video') NOT NULL DEFAULT 'video' AFTER `duration`;

ALTER TABLE `comments` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `categories` 
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
CHANGE COLUMN `created` `created` DATETIME NOT NULL DEFAULT now() ,
CHANGE COLUMN `modified` `modified` DATETIME NOT NULL DEFAULT now() ;

DROP TABLE `configurations`;

CREATE TABLE IF NOT EXISTS `configurations` (
  `id` INT(11) NOT NULL,
  `video_resolution` VARCHAR(12) NOT NULL,
  `users_id` INT(11) NOT NULL,
  `version` VARCHAR(10) NOT NULL,
  `created` DATETIME NOT NULL DEFAULT now(),
  `modified` DATETIME NOT NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  INDEX `fk_configurations_users1_idx` (`users_id` ASC),
  CONSTRAINT `fk_configurations_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `comments` 
DROP FOREIGN KEY `fk_comments_videos1`;

ALTER TABLE `comments` ADD CONSTRAINT `fk_comments_videos1`
  FOREIGN KEY (`videos_id`)
  REFERENCES `videos` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;