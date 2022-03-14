-- Add Meet Support

ALTER TABLE `users` 
ADD COLUMN `canCreateMeet` TINYINT(1) NULL DEFAULT NULL ;

UPDATE configurations SET  version = '9.4', modified = now() WHERE id = 1;