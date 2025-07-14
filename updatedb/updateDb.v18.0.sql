-- Security: Update checkpoint
UPDATE configurations SET  version = '18.0', modified = now() WHERE id = 1;
