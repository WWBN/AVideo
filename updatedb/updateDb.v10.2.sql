-- Security Update, strongly recommended.

UPDATE configurations SET  version = '10.2', modified = now() WHERE id = 1;