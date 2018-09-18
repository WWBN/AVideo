alter table plugins add pluginversion varchar(6) "0.00";
UPDATE configurations SET  version = '5.9', modified = now() WHERE id = 1;

