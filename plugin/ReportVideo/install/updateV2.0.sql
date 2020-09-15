ALTER TABLE `videos_reported` 
CHANGE COLUMN `videos_id` `videos_id` INT(11) NULL DEFAULT NULL ,
ADD COLUMN `status` CHAR(1) NOT NULL DEFAULT 'a' ,
ADD COLUMN `reported_users_id` INT(11) NOT NULL ,
ADD INDEX `fk_videos_reported_users2_idx` (`reported_users_id` ASC);

ALTER TABLE `videos_reported` 
ADD CONSTRAINT `fk_videos_reported_users2`
  FOREIGN KEY (`reported_users_id`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;