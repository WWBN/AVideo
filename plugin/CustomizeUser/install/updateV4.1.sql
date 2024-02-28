UPDATE `users`
SET `channelName` = `user`
WHERE `channelName` REGEXP '^_[0-9a-fA-F]{13}$';