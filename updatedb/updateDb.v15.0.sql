-- Security: Update checkpoint
UPDATE configurations SET version = '15.0', modified = now() WHERE id = 1;
