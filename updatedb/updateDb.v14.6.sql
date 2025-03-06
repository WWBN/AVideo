ALTER TABLE `comments`
ADD COLUMN `chat_messages_id` INT(11) NULL DEFAULT NULL;

ALTER TABLE `comments`
ADD INDEX `chat_messages_comments` (`chat_messages_id`);

-- Update the version in configurations table
UPDATE configurations SET version = '14.6', modified = now() WHERE id = 1;
