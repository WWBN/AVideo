ALTER TABLE `wallet_log` 
ADD COLUMN `information` TEXT NULL DEFAULT NULL,
ADD COLUMN `json_data` TEXT NULL DEFAULT NULL;