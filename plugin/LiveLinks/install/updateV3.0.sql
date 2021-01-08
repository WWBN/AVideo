ALTER TABLE `LiveLinks` 
ADD COLUMN `categories_id` INT(11) NULL,
ADD INDEX `fk_livelinks_categories1_idx` (`categories_id` ASC);

ALTER TABLE `LiveLinks` 
ADD CONSTRAINT `fk_LiveLinks_users2`
  FOREIGN KEY (`users_id`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_livelinks_categories1`
  FOREIGN KEY (`categories_id`)
  REFERENCES `categories` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;