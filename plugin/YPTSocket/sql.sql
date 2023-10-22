CREATE TABLE IF NOT EXISTS `connections` (
    `resourceId` INT NOT NULL PRIMARY KEY,
    `users_id` INT NULL,
    `room_users_id` INT NULL,
    `videos_id` INT NULL,
    `live_key_servers_id` VARCHAR(255) NULL,
    `liveLink` VARCHAR(500) NULL,
    `isAdmin` TINYINT NULL,
    `live_key` VARCHAR(255) NULL,
    `live_servers_id` INT NULL,
    `user_name` VARCHAR(255) NULL,
    `browser` VARCHAR(255) NULL,
    `yptDeviceId` VARCHAR(255) NULL,
    `client` TEXT NULL,
    `selfURI` VARCHAR(255) NULL,
    `isCommandLine` TINYINT NULL,
    `page_title` VARCHAR(255) NULL,
    `os` VARCHAR(255) NULL,
    `country_code` VARCHAR(255) NULL,
    `country_name` VARCHAR(255) NULL,
    `identification` VARCHAR(255) NULL,
    `chat_is_banned` TINYINT NULL,
    `ip` VARCHAR(45) NULL,
    `location` VARCHAR(45) NULL,
    `data` TEXT NULL,
    `time` INT NULL
);
CREATE INDEX `index_users_id` ON connections (users_id);
CREATE INDEX `index_room_users_id` ON connections (room_users_id);
CREATE INDEX `index_videos_id` ON connections (videos_id);
CREATE INDEX `index_live_key_servers_id` ON connections (live_key_servers_id);
CREATE INDEX `index_liveLink` ON connections (liveLink);
CREATE INDEX `index_isAdmin` ON connections (isAdmin);
CREATE INDEX `index_selfURI` ON connections (selfURI);