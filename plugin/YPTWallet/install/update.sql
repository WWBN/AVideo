ALTER TABLE `wallet` 
ADD COLUMN `crypto_wallet_address` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `wallet_log` 
ADD COLUMN `status` ENUM('pending', 'success', 'canceled') NOT NULL DEFAULT 'success',
ADD COLUMN `type` VARCHAR(45) NULL DEFAULT NULL AFTER `status`,
ADD INDEX `wallet_log_type` (`type` ASC) ;