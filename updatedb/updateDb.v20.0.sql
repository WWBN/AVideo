-- Security: Update checkpoint
UPDATE configurations SET version = '20.0', modified = now() WHERE id = 1;
