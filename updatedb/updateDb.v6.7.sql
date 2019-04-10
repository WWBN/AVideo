SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `subscribes` 
ADD COLUMN `subscriber_users_id` INT(11) NOT NULL AFTER `notify`,
ADD INDEX `fk_subscribes_users2_idx` (`subscriber_users_id` ASC);

ALTER TABLE `subscribes` 
ADD CONSTRAINT `fk_subscribes_users2`
  FOREIGN KEY (`subscriber_users_id`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `videos_statistics` 
ADD INDEX `when_statisci` (`when` ASC);

ALTER TABLE `videos_statistics` 
ADD COLUMN `session_id` VARCHAR(45) NOT NULL AFTER `videos_id`,
ADD INDEX `session_id_statistics` (`session_id` ASC);

UPDATE configurations SET  version = '6.7', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
