#!/usr/bin/env bash
# Standalone maintenance tool; no AVideo configuration or database is loaded.
# Usage: bash cleanup_clone_dumps.sh [--dry-run]
set -euo pipefail

readonly target='/var/www/html/AVideo/videos/clones'
readonly keep_days=30
dry_run=false

if (( $# > 1 )); then
    echo 'Usage: bash cleanup_clone_dumps.sh [--dry-run]' >&2
    exit 1
fi
case "${1:-}" in
    '') ;;
    --dry-run) dry_run=true ;;
    --help|-h)
        echo 'Usage: bash cleanup_clone_dumps.sh [--dry-run]'
        echo 'Keep the last 30 days of clone SQL dumps, based on modification time.'
        exit 0
        ;;
    *) echo "Unknown argument: $1" >&2; exit 1 ;;
esac

if [[ ! -d "$target" || -L "$target" ]]; then
    echo "Missing directory or symbolic link refused: $target" >&2
    exit 1
fi
cd -P -- "$target"
if [[ "$(pwd -P)" != "$target" ]]; then
    echo "Directory must resolve to its exact configured path: $target" >&2
    exit 1
fi

cutoff=$(( $(date +%s) - keep_days * 86400 ))
criteria=( -maxdepth 1 -regextype posix-extended -type f
    -regex '.*/Clone_mysqlDump_[0-9a-fA-F]+\.sql' ! -newermt "@$cutoff" )

if "$dry_run"; then
    echo "Dry run: clone SQL dumps older than $keep_days days in $target:"
    find -P . "${criteria[@]}" -print
else
    echo "Deleting clone SQL dumps older than $keep_days days in $target:"
    find -P . "${criteria[@]}" -delete -print
fi
