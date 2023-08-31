ALTER TABLE CachesInDB 
ADD UNIQUE `unique_cache_index`(`name`, `domain`, `ishttps`, `user_location`, `loggedType`);
