-- Add Missin column
-- this update may release a column already exists error in some installations

ALTER TABLE `users_extra_info` 
ADD COLUMN IF NOT EXISTS `order` INT NOT NULL DEFAULT 0;

UPDATE configurations SET  version = '10.7', modified = now() WHERE id = 1;
