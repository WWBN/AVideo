-- Security: Update checkpoint Authenticated SSRF vulnerability
UPDATE configurations SET version = '22.0', modified = now() WHERE id = 1;
