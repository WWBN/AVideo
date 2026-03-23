-- Security: Update checkpoint
UPDATE configurations SET version = '28.0', modified = now() WHERE id = 1;
