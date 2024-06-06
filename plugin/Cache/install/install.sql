CREATE TABLE IF NOT EXISTS `CachesInDB` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `content` MEDIUMBLOB NULL,
  `domain` VARCHAR(100) NULL,
  `ishttps` TINYINT NULL,
  `loggedType` ENUM('n', 'l', 'a') NULL DEFAULT 'n' COMMENT 'n=not logged\nl=logged\na=admin',
  `user_location` VARCHAR(100) NULL,
  `expires` DATETIME NULL,
  `timezone` VARCHAR(100) NULL,
  `created_php_time` INT(11) NULL,
  `name` VARCHAR(500) NULL,
  PRIMARY KEY (`id`),
  INDEX `cacheds1` (`domain` ASC),
  INDEX `caches2` (`ishttps` ASC),
  INDEX `caches3` (`loggedType` ASC),
  INDEX `caches4` (`user_location` ASC),
  INDEX `caches9` (`name` ASC))
ENGINE = InnoDB;

ALTER TABLE CachesInDB ADD FULLTEXT(name);

-- Drop the unique index if it exists
DROP PROCEDURE IF EXISTS DropUniqueIndexIfExists;

CREATE PROCEDURE DropUniqueIndexIfExists()
BEGIN
    DECLARE index_exists INT DEFAULT 0;

    -- Check if the index already exists
    SELECT COUNT(*)
    INTO index_exists
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = DATABASE() AND table_name = 'CachesInDB' AND index_name = 'unique_cache_index';

    -- If the index exists, drop it
    IF index_exists > 0 THEN
        SET @sql = 'ALTER TABLE CachesInDB DROP INDEX `unique_cache_index`';
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END;

-- Call the procedure to drop the index if it exists
CALL DropUniqueIndexIfExists();

-- Add the unique index
ALTER TABLE CachesInDB 
ADD UNIQUE `unique_cache_index`(`name`(250), `domain`(50), `ishttps`, `user_location`(50), `loggedType`);

-- Create the cache_schedule_delete table if it does not exist
CREATE TABLE IF NOT EXISTS `cache_schedule_delete` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;
