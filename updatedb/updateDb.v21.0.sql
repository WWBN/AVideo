-- Security: Update checkpoint Markdown XSS vulnerability
UPDATE configurations SET version = '21.0', modified = now() WHERE id = 1;
