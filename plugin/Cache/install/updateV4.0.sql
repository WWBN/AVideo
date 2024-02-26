TRUNCATE `CachesInDB`;

ALTER TABLE `CachesInDB`
ADD COLUMN `created_php_time` INT(11) NULL,
ADD INDEX `CachesInDB_created_php_time` (`created_php_time` ASC);