<?php

/**
 * Filesystem cache admission and maintenance. Only ObjectYPT's site-specific
 * tree is owned here; uploads, recordings, backups and sibling sites are not.
 * New implementation: Object.php and Cache/crontab.php had no size accounting.
 */
class ObjectCacheMaintenance
{
    const DEFAULT_MAX_BYTES = 1073741824;
    const DEFAULT_MAX_AGE = 259200;
    const DEFAULT_INTERVAL = 3600;
    const DEFAULT_MAX_FILES = 50000;

    private static function openState($root)
    {
        $root = rtrim($root, '/\\');
        if (!is_dir($root) && !@mkdir($root, 0777, true) && !is_dir($root)) {
            return false;
        }
        // Outside the tree so deleteALLCache's atomic rename cannot split locks.
        if (is_link($root) || is_link($root . '.maintenance')) {
            return false;
        }
        $handle = @fopen($root . '.maintenance', 'c+');
        if (!$handle) {
            return false;
        }
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }
        return $handle;
    }

    private static function readState($handle)
    {
        rewind($handle);
        $state = json_decode(stream_get_contents($handle), true);
        return is_array($state) ? $state : [];
    }

    private static function saveState($handle, $state)
    {
        rewind($handle);
        ftruncate($handle, 0);
        $ok = fwrite($handle, json_encode($state)) !== false && fflush($handle);
        if (!$ok) {
            _error_log('Object cache maintenance state could not be saved');
        }
        return $ok;
    }

    public static function requestCleanup($root, $interval)
    {
        $handle = self::openState($root);
        if (!$handle) {
            return false;
        }
        try {
            $state = self::readState($handle);
            if (!empty($state['requested']) && time() - $state['requested'] < max(60, $interval)) {
                return false;
            }
            $state['requested'] = time();
            return self::saveState($handle, $state);
        } finally {
            fclose($handle);
        }
    }

    private static function isOwnedPath($root, $file)
    {
        $root = realpath($root);
        if ($root === false) {
            return false;
        }
        $root = preg_replace('~/+~', '/', str_replace('\\', '/', $root)) . '/';
        $file = preg_replace('~/+~', '/', str_replace('\\', '/', $file));
        if (strpos($file, $root) !== 0 || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $file)) {
            return false;
        }
        $path = rtrim($root, '/');
        foreach (explode('/', substr($file, strlen($root))) as $part) {
            $path .= '/' . $part;
            if (is_link($path)) {
                return false;
            }
        }
        return true;
    }

    public static function write($root, $file, $content, $maxBytes, $maxFiles = self::DEFAULT_MAX_FILES)
    {
        $handle = self::openState($root);
        if (!$handle) {
            return false; // A background scan never stalls an HTTP worker.
        }
        $temporary = false;
        try {
            if (!self::isOwnedPath($root, $file)) {
                return false;
            }
            $state = self::readState($handle);
            // Never assume an existing installation's cache is empty. The first
            // asynchronous scan establishes its size before admitting new data.
            if (!isset($state['bytes'], $state['files'])) {
                return false;
            }
            clearstatcache(true, $file);
            $oldBytes = is_file($file) ? filesize($file) : 0;
            $newFile = !is_file($file);
            $index = dirname($file) . DIRECTORY_SEPARATOR . 'index.html';
            $newIndex = !is_file($index);
            $bytes = strlen($content);
            // Deletions elsewhere leave a conservative overestimate until the
            // next scan. Atomic replacement temporarily needs both copies.
            if ($state['bytes'] + $bytes > max(1, $maxBytes) ||
                $state['files'] + intval($newFile) + intval($newIndex) > max(1, $maxFiles)) {
                return false;
            }
            if (!is_dir(dirname($file)) && !@mkdir(dirname($file), 0777, true) && !is_dir(dirname($file))) {
                return false;
            }
            // Preserve ObjectYPT's directory-listing protection, including when
            // a previous cleanup removed the last cache entry in this directory.
            if ($newIndex && @file_put_contents($index, '') === false) {
                return false;
            }
            if ($newIndex) {
                $state['files']++;
                self::saveState($handle, $state);
            }
            $temporary = @tempnam(dirname($file), '.object-cache-');
            if (!$temporary || @file_put_contents($temporary, $content) !== $bytes || !@rename($temporary, $file)) {
                return false;
            }
            $temporary = false;
            $state['bytes'] = max(0, $state['bytes'] - $oldBytes) + $bytes;
            $state['files'] += intval($newFile);
            self::saveState($handle, $state);
            return $bytes;
        } finally {
            if ($temporary !== false) {
                @unlink($temporary);
            }
            fclose($handle);
        }
    }

    public static function deleteExpired($root, $file, $cutoff)
    {
        $handle = self::openState($root);
        if (!$handle) {
            return false;
        }
        try {
            clearstatcache(true, $file);
            return self::isOwnedPath($root, $file) && is_file($file) && filemtime($file) < $cutoff && @unlink($file);
        } finally {
            fclose($handle);
        }
    }

    public static function cleanup($root, $maxAge)
    {
        $handle = self::openState($root);
        if (!$handle) {
            return false;
        }
        try {
            $bytes = 0;
            $deleted = 0;
            $files = 0;
            $cutoff = time() - max(60, $maxAge);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $entry) {
                if ($entry->isLink()) {
                    continue;
                }
                $path = $entry->getPathname();
                if ($entry->isDir()) {
                    $index = $path . DIRECTORY_SEPARATOR . 'index.html';
                    if (is_file($index) && !is_link($index) && filemtime($index) < $cutoff && count(scandir($path)) === 3) {
                        $indexBytes = filesize($index);
                        if (@unlink($index)) {
                            // CHILD_FIRST already counted this marker.
                            $bytes -= $indexBytes;
                            $files--;
                            $deleted++;
                        }
                    }
                    @rmdir($path); // Only empty directories; never recursive removal.
                    continue;
                }
                $obsolete = $entry->getMTime() < $cutoff &&
                    (substr($path, -6) === '.cache' ||
                     strpos($entry->getFilename(), '.object-cache-') === 0);
                if ($obsolete && @unlink($path)) {
                    $deleted++;
                } else {
                    $bytes += $entry->getSize();
                    $files++;
                }
            }
            $state = self::readState($handle);
            $state['bytes'] = $bytes;
            $state['files'] = $files;
            $state['scanned'] = time();
            self::saveState($handle, $state);
            return ['bytes' => $bytes, 'deleted' => $deleted];
        } catch (Throwable $th) {
            _error_log('Object cache maintenance: ' . $th->getMessage());
            return false;
        } finally {
            fclose($handle);
        }
    }
}
