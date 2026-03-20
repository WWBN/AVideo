-- Security: Update checkpoint
UPDATE configurations SET version = '27.0', modified = now() WHERE id = 1;
