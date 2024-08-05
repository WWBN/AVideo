-- Add new column isChannelSuggested if it does not exist
ALTER TABLE `videos`
ADD COLUMN `isChannelSuggested` INT(1) UNSIGNED NOT NULL DEFAULT 0;

-- Add new index for isChannelSuggested if it does not exist
CREATE INDEX `is_channel_suggested` ON `videos` (`isChannelSuggested`);

-- Update the version in configurations table
UPDATE configurations SET version = '14.4', modified = now() WHERE id = 1;
