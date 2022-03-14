ALTER TABLE `live_restreams` 
CHANGE COLUMN `name` `name` VARCHAR(500) NOT NULL ,
CHANGE COLUMN `stream_url` `stream_url` VARCHAR(500) NOT NULL ,
CHANGE COLUMN `stream_key` `stream_key` VARCHAR(500) NOT NULL ;