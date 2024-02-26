
CREATE TABLE IF NOT EXISTS `live_servers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL DEFAULT NULL,
  `url` VARCHAR(255) NULL,
  `status` CHAR(1) NULL DEFAULT 'a',
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `rtmp_server` VARCHAR(255) NULL DEFAULT NULL,
  `playerServer` VARCHAR(255) NULL DEFAULT NULL,
  `stats_url` VARCHAR(255) NULL DEFAULT NULL,
  `disableDVR` TINYINT(1) NULL DEFAULT NULL,
  `disableGifThumbs` TINYINT(1) NULL DEFAULT NULL,
  `useAadaptiveMode` TINYINT(1) NULL DEFAULT NULL,
  `protectLive` TINYINT(1) NULL DEFAULT NULL,
  `getRemoteFile` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `live_serversindex2` (`status` ASC),
  INDEX `live_servers` (`url` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `live_transmitions_history` 
ADD COLUMN `live_servers_id` INT(11) NULL DEFAULT NULL AFTER `users_id`,
ADD INDEX `fk_live_transmitions_history_live_servers1_idx` (`live_servers_id` ASC);

ALTER TABLE `live_transmitions_history` 
ADD CONSTRAINT `fk_live_transmitions_history_live_servers1`
  FOREIGN KEY (`live_servers_id`)
  REFERENCES `live_servers` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;