DROP TABLE IF EXISTS `CachesInDB_Memory`;
DROP TABLE IF EXISTS `CachesInDB_Blob`;

CREATE TABLE IF NOT EXISTS `CachesInDB_Memory` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `domain` VARCHAR(100) NULL,
  `ishttps` TINYINT NULL,
  `loggedType` ENUM('n', 'l', 'a') NULL DEFAULT 'n' COMMENT 'n=not logged\nl=logged\na=admin',
  `user_location` VARCHAR(100) NULL,
  `expires` DATETIME NULL,
  `timezone` VARCHAR(100) NULL,
  `created_php_time` INT(11) NULL,
  `name` VARCHAR(500) NULL,
  PRIMARY KEY (`id`),
  INDEX `caches1` (`domain` ASC),
  INDEX `caches2` (`ishttps` ASC),
  INDEX `caches3` (`loggedType` ASC),
  INDEX `caches4` (`user_location` ASC),
  INDEX `caches9` (`name` ASC))
ENGINE = MEMORY;

CREATE TABLE IF NOT EXISTS `CachesInDB_Blob` (
  `id` INT NOT NULL,
  `content` MEDIUMBLOB NULL,
  PRIMARY KEY (`id`)
) ENGINE = MyISAM;
