-- Security: Update checkpoint Unauthenticated IDOR - Playlist Information Disclosure
UPDATE configurations SET version = '25.0', modified = now() WHERE id = 1;
