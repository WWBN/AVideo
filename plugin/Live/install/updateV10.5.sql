-- MySQL Workbench Synchronization
-- Generated: 2022-03-01 11:10
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: msn

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `live_transmitions_history` 
DROP FOREIGN KEY `fk_live_transmitions_history_live_servers1`;

ALTER TABLE `live_schedule` 
DROP FOREIGN KEY `fk_live_schedule_live_servers1`;

ALTER TABLE `live_transmitions_history` 
ADD COLUMN `users_id_company` INT(11) NULL DEFAULT NULL AFTER `total_viewers`,
ADD INDEX `fk_live_transmitions_history_users1_idx` (`users_id_company` ASC);

ALTER TABLE `live_schedule` 
ADD COLUMN `users_id_company` INT(11) NULL DEFAULT NULL AFTER `scheduled_password`,
ADD INDEX `fk_live_schedule_users2_idx` (`users_id_company` ASC);

ALTER TABLE `live_transmitions_history` 
ADD CONSTRAINT `fk_live_transmitions_history_live_servers1`
  FOREIGN KEY (`live_servers_id`)
  REFERENCES `live_servers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_live_transmitions_history_users1`
  FOREIGN KEY (`users_id_company`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `live_schedule` 
ADD CONSTRAINT `fk_live_schedule_live_servers1`
  FOREIGN KEY (`live_servers_id`)
  REFERENCES `live_servers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_live_schedule_users2`
  FOREIGN KEY (`users_id_company`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
