-- Security: Update checkpoint
UPDATE configurations SET  version = '17.0', modified = now() WHERE id = 1;
