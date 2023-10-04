ALTER TABLE `wallet` MODIFY `users_id` INT NULL;
ALTER TABLE `wallet` DROP FOREIGN KEY `fk_wallet_users`;
ALTER TABLE `wallet`
ADD CONSTRAINT `fk_wallet_users`
  FOREIGN KEY (`users_id`) REFERENCES `users` (`id`)
  ON DELETE SET NULL
  ON UPDATE NO ACTION;
