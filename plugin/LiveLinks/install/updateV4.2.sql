ALTER TABLE `LiveLinks`
  ADD COLUMN `timezone` VARCHAR(255) NULL,
  ADD COLUMN `start_php_time` BIGINT NULL,
  ADD COLUMN `end_php_time` BIGINT NULL;
