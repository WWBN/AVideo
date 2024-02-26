SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP TABLE IF EXISTS `live_restreams_logs`;

CREATE TABLE IF NOT EXISTS `live_restreams_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `restreamer` VARCHAR(255) NOT NULL,
  `m3u8` VARCHAR(400) NULL,
  `logFile` VARCHAR(255) NULL,
  `json` TEXT NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `live_transmitions_history_id` INT(11) NOT NULL,
  `live_restreams_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_live_restreams_logs_live_transmitions_history1_idx` (`live_transmitions_history_id` ASC),
  INDEX `fk_live_restreams_logs_live_restreams1_idx` (`live_restreams_id` ASC),
  CONSTRAINT `fk_live_restreams_logs_live_transmitions_history1`
    FOREIGN KEY (`live_transmitions_history_id`)
    REFERENCES `live_transmitions_history` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_live_restreams_logs_live_restreams1`
    FOREIGN KEY (`live_restreams_id`)
    REFERENCES `live_restreams` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
