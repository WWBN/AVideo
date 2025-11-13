-- Add allowed_resolutions field to users_groups table
ALTER TABLE `users_groups`
ADD COLUMN `allowed_resolutions` TEXT NULL DEFAULT NULL
COMMENT 'JSON array of allowed video resolutions for this user group'
AFTER `group_name`;

-- Security: Update checkpoint
UPDATE configurations SET version = '18.1', modified = now() WHERE id = 1;
