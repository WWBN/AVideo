alter table users add externalOptions TEXT NULL; 
UPDATE configurations SET  version = '5.8', modified = now() WHERE id = 1;
