CREATE TABLE IF NOT EXISTS `playback_access_rules` (
  `scope` ENUM('category', 'video') NOT NULL,
  `scope_id` INT NOT NULL,
  `mode` ENUM('require', 'unrestricted') NOT NULL DEFAULT 'require',
  `groups_json` TEXT NOT NULL,
  `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scope`, `scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE configurations SET version = '30.3', modified = NOW() WHERE id = 1;
