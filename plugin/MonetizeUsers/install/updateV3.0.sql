ALTER TABLE `monetize_user_reward_log`
DROP FOREIGN KEY `fk_monetize_user_reward_log_videos1`;

ALTER TABLE `monetize_user_reward_log`
ADD CONSTRAINT `fk_monetize_user_reward_log_videos1`
  FOREIGN KEY (`videos_id`)
  REFERENCES `videos` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
