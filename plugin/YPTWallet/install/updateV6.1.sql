-- Repair installations where the combined 5.1 ALTER stopped on an existing column.
ALTER TABLE `wallet_log` ADD COLUMN IF NOT EXISTS `information` TEXT NULL DEFAULT NULL;
ALTER TABLE `wallet_log` ADD COLUMN IF NOT EXISTS `json_data` TEXT NULL DEFAULT NULL;
