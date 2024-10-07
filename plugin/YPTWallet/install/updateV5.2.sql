ALTER TABLE `wallet_log` 
DROP FOREIGN KEY `fk_wallet_log_wallet1`;

ALTER TABLE `wallet_log`
ADD CONSTRAINT `fk_wallet_log_wallet1`
  FOREIGN KEY (`wallet_id`)
  REFERENCES `wallet` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
