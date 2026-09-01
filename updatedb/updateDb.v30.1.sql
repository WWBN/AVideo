SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- Atomic, race-free rate-limit counters. enforceRateLimit() previously read the
-- counter from ObjectYPT::getCacheGlobal()/setCacheGlobal() and wrote it back
-- non-atomically (read, compare, write), so concurrent requests all read the
-- same stale value and most increments were lost under parallel load. This
-- table backs a single atomic INSERT ... ON DUPLICATE KEY UPDATE per attempt.
-- ratelimit_key is a raw sha256 hash (BINARY(32)) of the logical key rather
-- than the plain string, to keep the primary key compact and fixed-size.
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `ratelimit_key` BINARY(32) NOT NULL,
    `attempts` INT(11) NOT NULL DEFAULT 1,
    `expires_at` DATETIME NOT NULL,
    PRIMARY KEY (`ratelimit_key`),
    KEY `rate_limits_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE configurations SET version = '30.1', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
