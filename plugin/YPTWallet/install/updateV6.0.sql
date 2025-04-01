ALTER TABLE `wallet_log`
ADD COLUMN `previous_wallet_balance` DOUBLE(20,10) NOT NULL DEFAULT 0;
