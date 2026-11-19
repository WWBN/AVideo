#!/usr/bin/env bash
# Run only in a disposable Linux container with an empty tmpfs mounted at
# /var/www/html/AVideo/videos and CLONE_DUMP_CLEANUP_SANDBOX=1.
# Usage inside that container: bash this-test.sh /path/to/cleanup_clone_dumps.sh
set -euo pipefail

cleanup_script="${1:?Provide the cleanup script path}"
target='/var/www/html/AVideo/videos/clones'
if [[ ! -f /.dockerenv || "${CLONE_DUMP_CLEANUP_SANDBOX:-}" != 1 ]]; then
    echo 'Refusing to create fixtures outside an explicitly disposable container.' >&2
    exit 1
fi
if [[ -e "$target" || -L "$target" ]]; then
    echo 'Refusing to use an existing clones directory.' >&2
    exit 1
fi

mkdir -p "$target/nested"
touch "$target/Clone_mysqlDump_abc123.sql" "$target/Clone_mysqlDump_def456.sql"
touch "$target/Clone_mysqlDump_789abc.sql" "$target/manual-backup.sql"
touch "$target/index.html" "$target/movie.mp4" "$target/Clone_mysqlDump_not-hex.sql"
touch "$target/nested/Clone_mysqlDump_abcdef.sql"
touch -d '31 days ago' "$target/Clone_mysqlDump_abc123.sql"
touch -d '90 days ago' "$target/Clone_mysqlDump_def456.sql"
touch -d '29 days ago' "$target/Clone_mysqlDump_789abc.sql"
touch -d '90 days ago' "$target/manual-backup.sql" "$target/index.html" \
    "$target/movie.mp4" "$target/Clone_mysqlDump_not-hex.sql" \
    "$target/nested/Clone_mysqlDump_abcdef.sql"
ln -s "$target/manual-backup.sql" "$target/Clone_mysqlDump_123abc.sql"

bash "$cleanup_script" --dry-run
test -f "$target/Clone_mysqlDump_abc123.sql"
test -f "$target/Clone_mysqlDump_def456.sql"
echo 'PASS: dry run preserves old dumps'

if bash "$cleanup_script" --unknown >/dev/null 2>&1; then
    echo 'FAIL: unknown argument was accepted' >&2
    exit 1
fi
test -f "$target/Clone_mysqlDump_abc123.sql"
echo 'PASS: invalid arguments do not delete files'

bash "$cleanup_script"
test ! -e "$target/Clone_mysqlDump_abc123.sql"
test ! -e "$target/Clone_mysqlDump_def456.sql"
test -f "$target/Clone_mysqlDump_789abc.sql"
test -f "$target/manual-backup.sql"
test -f "$target/index.html"
test -f "$target/movie.mp4"
test -f "$target/Clone_mysqlDump_not-hex.sql"
test -f "$target/nested/Clone_mysqlDump_abcdef.sql"
test -L "$target/Clone_mysqlDump_123abc.sql"
echo 'PASS: only expired regular clone dumps in the target directory are deleted'

mv "$target" "${target}-original"
ln -s "${target}-original" "$target"
if bash "$cleanup_script" >/dev/null 2>&1; then
    echo 'FAIL: symbolic-link target was accepted' >&2
    exit 1
fi
test -f "${target}-original/Clone_mysqlDump_789abc.sql"
echo 'PASS: symbolic-link directory is refused'
