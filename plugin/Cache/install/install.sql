-- Drop the table if it exists
DROP TABLE IF EXISTS `CachesInDB`;

-- Create the table
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
  INDEX `caches9` (`name` ASC),
  UNIQUE INDEX `unique_cache_index`(`name`(250), `domain`(50), `ishttps`, `user_location`(50), `loggedType`),
  FULLTEXT INDEX `name_fulltext` (`name`)
) ENGINE = InnoDB;

-- Drop the cache_schedule_delete table if it exists
DROP TABLE IF EXISTS `cache_schedule_delete`;

-- Create the cache_schedule_delete table
CREATE TABLE IF NOT EXISTS `cache_schedule_delete` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC)
) ENGINE = InnoDB;
