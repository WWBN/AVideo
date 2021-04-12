-- Add Missin column
-- this update may release a column already exists error in some installations

ALTER TABLE `users` 
ADD COLUMN `users_extra_info` `order` INT NOT NULL DEFAULT 0;

UPDATE configurations SET  version = '10.6', modified = now() WHERE id = 1;