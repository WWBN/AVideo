-- MySQL Workbench Synchronization
-- Generated: 2017-07-25 10:38
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `youPHPTube`.`configurations` 
DROP COLUMN `doNotShowCategories`,
DROP COLUMN `doNotShowVideoAndAudioLinks`,
DROP COLUMN `encode_mp3spectrum`,
DROP COLUMN `encode_webm`,
DROP COLUMN `encode_mp4`,
DROP COLUMN `exiftoolPath`,
DROP COLUMN `exiftool`,
DROP COLUMN `youtubeDlPath`,
DROP COLUMN `ffmpegPath`,
DROP COLUMN `youtubeDl`,
DROP COLUMN `ffmpegOgg`,
DROP COLUMN `ffmpegSpectrum`,
DROP COLUMN `ffmpegMp3`,
DROP COLUMN `ffmpegWebmPortrait`,
DROP COLUMN `ffmpegWebm`,
DROP COLUMN `ffmpegMp4Portrait`,
DROP COLUMN `ffmpegMp4`,
DROP COLUMN `ffmpegImage`,
DROP COLUMN `ffprobeDuration`,
ADD COLUMN `encoderURL` VARCHAR(255) NULL DEFAULT NULL AFTER `smtpPort`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


UPDATE configurations SET  version = '4.0', modified = now() WHERE id = 1;