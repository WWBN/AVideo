<?php
/**
 * Helpers for install/updateStreamerURL.php (change the domain/URL of a site).
 *
 * Nothing here uses the database or globals; files and commands are only the ones passed as
 * arguments, so tests/Unit/UpdateStreamerURLTest.php covers it with temporary folders.
 *
 * The old domain is replaced only where it is the site address, never as plain text:
 * - inside a URL: "https://old.com/...", "rtmp://old.com/live", "https:\/\/old.com\/" (JSON),
 *   "https%3A%2F%2Fold.com" (URL-encoded), "ftp://user:pass@old.com/";
 * - a value that is only the domain: "old.com" (YPTSocket host) or "old.com" inside quotes (JSON);
 * - the Let's Encrypt folder: /etc/letsencrypt/live/old.com/ (only when the new certificate exists);
 * - in web server files, also the ServerName/ServerAlias/server_name/RewriteCond lines.
 * E-mail addresses (admin@old.com), folders (/var/www/old.com/), subdomains (cdn.old.com) and
 * other domains that contain it (old.com.br, myold.com) are kept.
 */

/**
 * Returns the URL in the form AVideo saves it (lowercase scheme and host, final "/"), or false.
 */
function siteURLChangeNormalizeURL($url)
{
    $url = trim((string) $url);
    if ($url === '' || !preg_match('~^https?://~i', $url) || preg_match('~[\s"\'<>\\\\]~', $url)) {
        return false;
    }
    $parts = parse_url($url);
    if (empty($parts['host']) || isset($parts['query']) || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])) {
        return false;
    }
    $host = strtolower($parts['host']);
    if (!siteURLChangeIsValidHost($host)) {
        return false;
    }
    $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
    if (substr($path, -1) !== '/') {
        $path .= '/';
    }
    if (!preg_match('~^/[A-Za-z0-9._\-/]*$~', $path) || strpos($path, '//') !== false || preg_match('~/\.\.?/~', $path)) {
        return false;
    }
    $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
    return strtolower($parts['scheme']) . '://' . $host . $port . $path;
}

function siteURLChangeIsValidHost($host)
{
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return true;
    }
    return strlen($host) <= 253
        && (bool) preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/', $host);
}

/**
 * A Let's Encrypt certificate can be requested only for a public domain name.
 */
function siteURLChangeCanHaveCertificate($host)
{
    return !filter_var($host, FILTER_VALIDATE_IP) && strpos($host, '.') !== false
        && !preg_match('/(^|\.)(localhost|local|test|example|invalid|internal)$/', $host);
}

/**
 * @return array oldURL, newURL, oldHost, newHost, certName (null: keep the certificate folders)
 */
function siteURLChangeCreateMap($oldURL, $newURL, $certName = null)
{
    $oldURL = siteURLChangeNormalizeURL($oldURL);
    $newURL = siteURLChangeNormalizeURL($newURL);
    if (empty($oldURL) || empty($newURL)) {
        throw new InvalidArgumentException('Invalid URL');
    }
    return [
        'oldURL' => $oldURL,
        'newURL' => $newURL,
        'oldHost' => parse_url($oldURL, PHP_URL_HOST),
        'newHost' => parse_url($newURL, PHP_URL_HOST),
        'certName' => $certName,
    ];
}

/**
 * Regex for the old host (and its www. variant), with the boundaries that keep other domains.
 */
function siteURLChangeHostRegex($map)
{
    $old = $map['oldHost'];
    $www = strpos($old, 'www.') === 0 ? '()' : '((?i:www\.)?)';
    return $www . '(' . preg_quote($old, '~') . ')(?![A-Za-z0-9_\-]|\.[A-Za-z0-9_\-])';
}

/**
 * New host for a matched host: www.old.com becomes www.new.com, old.com becomes new.com.
 */
function siteURLChangeNewHost($map, $wwwPrefix)
{
    if ($wwwPrefix === '' || strpos($map['newHost'], 'www.') === 0) {
        return $map['newHost'];
    }
    return $wwwPrefix . $map['newHost'];
}

function siteURLChangeIsSerialized($value)
{
    return (bool) preg_match('/^(a:\d+:\{|O:\d+:"|s:\d+:"|C:\d+:")/', ltrim((string) $value));
}

/**
 * A "/" as saved in the database: plain, JSON-escaped ("\/") or escaped twice ("\\/", older
 * Live plugin parameters).
 */
if (!defined('SITE_URL_CHANGE_SLASH')) {
    define('SITE_URL_CHANGE_SLASH', '\\\\*/');
}

/**
 * The old site address with the new scheme and port (only when they change): "http://old.com/"
 * becomes "https://new.net/", while "http://old.com:8080/" (another port) keeps its scheme.
 */
function siteURLChangeReplaceSiteAuthority($text, $map)
{
    $old = parse_url($map['oldURL']);
    $new = parse_url($map['newURL']);
    $oldPort = isset($old['port']) ? ':' . $old['port'] : '';
    $newPort = isset($new['port']) ? ':' . $new['port'] : '';
    if ($old['scheme'] === $new['scheme'] && $oldPort === $newPort) {
        return $text;
    }
    $slash = SITE_URL_CHANGE_SLASH;
    $authority = preg_quote($old['host'] . $oldPort, '~');
    $text = preg_replace_callback("~(?<![A-Za-z0-9+.\-])" . preg_quote($old['scheme'], '~') . ":((?:{$slash}){2}){$authority}(?={$slash})~i", function ($m) use ($new, $newPort) {
        return $new['scheme'] . ':' . $m[1] . $new['host'] . $newPort;
    }, $text);
    return preg_replace_callback("~(?<![A-Za-z0-9+.\-])" . preg_quote($old['scheme'], '~') . "%3A%2F%2F{$authority}(?=%2F)~i", function ($m) use ($new, $newPort) {
        return $new['scheme'] . '%3A%2F%2F' . $new['host'] . $newPort;
    }, $text);
}

/**
 * Old host after "//" (plain, escaped or URL-encoded), with optional user:pass@.
 */
function siteURLChangeReplaceURLHosts($text, $map)
{
    $slash = SITE_URL_CHANGE_SLASH;
    $pattern = "~((?:{$slash}){2}|%2F%2F)((?:[^\\s/?#@\"'<>\\\\%]+@)?)" . siteURLChangeHostRegex($map) . '~i';
    return preg_replace_callback($pattern, function ($m) use ($map) {
        return $m[1] . $m[2] . siteURLChangeNewHost($map, $m[3]);
    }, $text);
}

/**
 * /etc/letsencrypt/live/old.com[-0001]/ becomes /etc/letsencrypt/live/<certName>/ (also escaped).
 */
function siteURLChangeReplaceCertificatePaths($text, $map)
{
    if (empty($map['certName'])) {
        return $text;
    }
    $old = preg_quote($map['oldHost'], '~');
    $pattern = '~(' . SITE_URL_CHANGE_SLASH . ')etc\1letsencrypt\1live\1(?:www\.)?' . $old . '(?:-\d{4})?(?=\1)~i';
    return preg_replace_callback($pattern, function ($m) use ($map) {
        return $m[1] . 'etc' . $m[1] . 'letsencrypt' . $m[1] . 'live' . $m[1] . $map['certName'];
    }, $text);
}

/**
 * Replaces the old site address in a value saved in the database (plugin parameters, site
 * configuration, live servers, links in descriptions...). Returns the value unchanged when the
 * old address does not appear as a site address. Serialized PHP is never changed: the string
 * lengths it stores would break.
 */
function siteURLChangeReplaceValue($value, $map)
{
    if (!is_string($value) || $value === '' || stripos($value, $map['oldHost']) === false || siteURLChangeIsSerialized($value)) {
        return $value;
    }
    $trimmed = strtolower(trim($value));
    if ($trimmed === $map['oldHost'] || (strpos($map['oldHost'], 'www.') !== 0 && $trimmed === 'www.' . $map['oldHost'])) {
        return str_ireplace($trimmed, siteURLChangeNewHost($map, $trimmed === $map['oldHost'] ? '' : 'www.'), $value);
    }

    $value = siteURLChangeReplaceSiteAuthority($value, $map);
    $value = siteURLChangeReplaceURLHosts($value, $map);

    // A JSON value that is only the domain: {"host":"old.com"} (also inside an escaped JSON string).
    $pattern = '~(?<=")' . siteURLChangeHostRegex($map) . '(?=\\\\*")~i';
    $value = preg_replace_callback($pattern, function ($m) use ($map) {
        return siteURLChangeNewHost($map, $m[1]);
    }, $value);

    return siteURLChangeReplaceCertificatePaths($value, $map);
}

/**
 * Replaces the old domain in an Apache or NGINX configuration file. URLs keep their scheme
 * (nginx-rtmp callbacks such as on_publish only work with http://), folders (DocumentRoot,
 * root, alias) are kept, and certificate paths change only when $map['certName'] is set.
 */
function siteURLChangeReplaceServerConfig($text, $map)
{
    $parts = preg_split('/(\r\n|\n|\r)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $broad = '~(?<![A-Za-z0-9.@_\-])' . siteURLChangeHostRegex($map) . '~i';
    foreach ($parts as $index => $line) {
        if ($index % 2 === 1 || stripos($line, $map['oldHost']) === false) {
            continue;
        }
        $line = siteURLChangeReplaceURLHosts($line, $map);
        $line = siteURLChangeReplaceCertificatePaths($line, $map);
        if (preg_match('/^\s*#?\s*(ServerName|ServerAlias|server_name|RewriteCond)\b/i', $line)) {
            $line = preg_replace_callback($broad, function ($m) use ($map) {
                return siteURLChangeNewHost($map, $m[1]);
            }, $line);
        }
        $parts[$index] = $line;
    }
    return implode('', $parts);
}

/**
 * Lines that differ between two versions of a text file, for the summary.
 *
 * @return array of ['line' => number, 'old' => text, 'new' => text]
 */
function siteURLChangeChangedLines($old, $new)
{
    $oldLines = preg_split('/\r\n|\n|\r/', $old);
    $newLines = preg_split('/\r\n|\n|\r/', $new);
    $changes = [];
    foreach ($oldLines as $index => $line) {
        if (isset($newLines[$index]) && $newLines[$index] !== $line) {
            $changes[] = ['line' => $index + 1, 'old' => trim($line), 'new' => trim($newLines[$index])];
        }
    }
    return $changes;
}

/**
 * Same site for the Encoder (it compares addresses without the scheme, see Streamer::isURLAllowed()).
 */
function siteURLChangeIsSameSite($url, $siteURL)
{
    $normalize = function ($value) {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('~^https?://~', '', $value);
        return rtrim($value, '/') . '/';
    };
    return trim((string) $url) !== '' && $normalize($url) === $normalize($siteURL);
}

/**
 * Encoder "Allowed Streamers Sites" (one address per line): the old address becomes the new one.
 *
 * @return string the new list (unchanged when the old address is not in it)
 */
function siteURLChangeReplaceAllowedStreamers($list, $oldURL, $newURL)
{
    $list = (string) $list;
    $eol = strpos($list, "\r\n") !== false ? "\r\n" : "\n";
    $lines = preg_split('/\r\n|\n|\r/', $list);
    $result = [];
    $changed = false;
    $hasNew = false;
    foreach ($lines as $line) {
        if (siteURLChangeIsSameSite($line, $newURL)) {
            $hasNew = true;
        }
    }
    foreach ($lines as $line) {
        if (siteURLChangeIsSameSite($line, $oldURL)) {
            $changed = true;
            if ($hasNew) {
                continue;
            }
            $hasNew = true;
            $line = $newURL;
        }
        $result[] = $line;
    }
    return $changed ? implode($eol, $result) : $list;
}

/**
 * Reads the simple assignments of a configuration.php without running it:
 * $global['key'] = 'value'; and $variable = 'value';
 *
 * @return array name => ['literal' => bool, 'value' => mixed, 'token' => token index]
 *               (name is "global.key" or "variable"; the last assignment wins)
 */
function siteURLChangeReadPHPAssignments($code)
{
    $tokens = token_get_all($code);
    $count = count($tokens);
    $next = function ($i) use ($tokens, $count) {
        for ($i++; $i < $count; $i++) {
            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            return $i;
        }
        return $count;
    };
    $assignments = [];
    for ($i = 0; $i < $count; $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_VARIABLE) {
            continue;
        }
        $name = substr($tokens[$i][1], 1);
        $j = $next($i);
        if ($name === 'global') {
            if (($tokens[$j] ?? null) !== '[') {
                continue;
            }
            $k = $next($j);
            if (!is_array($tokens[$k] ?? null) || $tokens[$k][0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }
            $key = siteURLChangeDecodePHPString($tokens[$k][1]);
            $j = $next($k);
            if ($key === null || ($tokens[$j] ?? null) !== ']') {
                continue;
            }
            $name = 'global.' . $key;
            $j = $next($j);
        }
        if (($tokens[$j] ?? null) !== '=') {
            continue;
        }
        $v = $next($j);
        $end = $next($v);
        $literal = ($tokens[$end] ?? null) === ';';
        $value = null;
        if ($literal && is_array($tokens[$v])) {
            if ($tokens[$v][0] === T_CONSTANT_ENCAPSED_STRING) {
                $value = siteURLChangeDecodePHPString($tokens[$v][1]);
                $literal = $value !== null;
            } elseif ($tokens[$v][0] === T_LNUMBER || $tokens[$v][0] === T_DNUMBER) {
                $value = $tokens[$v][1] + 0;
            } elseif ($tokens[$v][0] === T_STRING && in_array(strtolower($tokens[$v][1]), ['true', 'false'], true)) {
                $value = strtolower($tokens[$v][1]) === 'true';
            } else {
                $literal = false;
            }
        } else {
            $literal = false;
        }
        $all = $assignments[$name]['all'] ?? [];
        $all[] = ['token' => $v, 'literal' => $literal];
        $assignments[$name] = ['literal' => $literal, 'value' => $value, 'token' => $v, 'all' => $all];
    }
    return $assignments;
}

/**
 * Value of a PHP string literal, or null when it has variables or escapes we do not handle.
 */
function siteURLChangeDecodePHPString($literal)
{
    $quote = $literal[0];
    $inner = substr($literal, 1, -1);
    if ($quote === "'") {
        return preg_replace('/\\\\([\\\\\'])/', '$1', $inner);
    }
    if ($quote === '"' && strpos($inner, '$') === false && strpos($inner, '\\') === false) {
        return $inner;
    }
    return null;
}

/**
 * Number of assignments of a name that are built at runtime (not plain text), for example
 * the old installer's  if (false && !empty($_SERVER['SERVER_NAME'])) { $global['webSiteRootURL'] = "http" . ...; }
 */
function siteURLChangeCountExpressionAssignments($code, $name)
{
    $assignments = siteURLChangeReadPHPAssignments($code);
    $count = 0;
    foreach ($assignments[$name]['all'] ?? [] as $assignment) {
        $count += $assignment['literal'] ? 0 : 1;
    }
    return $count;
}

/**
 * Changes string assignments of a configuration.php ($global['key'] or $variable), keeping
 * every other byte of the file. Only plain text assignments change; assignments built at
 * runtime are kept, and at least one plain text assignment of each name must exist.
 *
 * @param array $values name ("global.key" or "variable") => new string value
 * @return string the new file content
 * @throws RuntimeException when a name has no plain text assignment
 */
function siteURLChangeSetPHPAssignments($code, array $values)
{
    $assignments = siteURLChangeReadPHPAssignments($code);
    $tokens = token_get_all($code);
    $replace = [];
    foreach ($values as $name => $value) {
        if (empty($assignments[$name])) {
            throw new RuntimeException("{$name} is not set in the file");
        }
        foreach ($assignments[$name]['all'] as $assignment) {
            $index = $assignment['token'];
            if ($assignment['literal'] && is_array($tokens[$index]) && $tokens[$index][0] === T_CONSTANT_ENCAPSED_STRING) {
                $replace[$index] = var_export((string) $value, true);
            }
        }
        if (!array_intersect_key($replace, array_flip(array_column($assignments[$name]['all'], 'token')))) {
            throw new RuntimeException("{$name} is not a plain text value; change it by hand");
        }
    }
    $content = '';
    foreach ($tokens as $index => $token) {
        $content .= isset($replace[$index]) ? $replace[$index] : (is_array($token) ? $token[1] : $token);
    }
    // Same number of assignments, and every plain text one now has the new value.
    $check = siteURLChangeReadPHPAssignments($content);
    $newTokens = token_get_all($content);
    foreach ($values as $name => $value) {
        if (count($check[$name]['all'] ?? []) !== count($assignments[$name]['all'])) {
            throw new RuntimeException("Could not change {$name}");
        }
        foreach ($check[$name]['all'] as $position => $assignment) {
            $before = $assignments[$name]['all'][$position];
            if ($assignment['literal'] !== $before['literal']) {
                throw new RuntimeException("Could not change {$name}");
            }
            if ($assignment['literal'] && is_array($tokens[$before['token']]) && $tokens[$before['token']][0] === T_CONSTANT_ENCAPSED_STRING) {
                $text = $newTokens[$assignment['token']][1] ?? '';
                if (siteURLChangeDecodePHPString($text) !== (string) $value) {
                    throw new RuntimeException("Could not change {$name}");
                }
            }
        }
    }
    return $content;
}

/**
 * Replaces the site address in every plain text setting of a configuration.php (webSiteRootURL
 * and any other address someone added, for example a CORS list), with the same rule as the
 * database. Paths ($global['systemRootPath'], logfile) and passwords never match that rule.
 *
 * @return array ['content' => new code, 'changes' => [['name' => ..., 'old' => ..., 'new' => ...]],
 *                'remaining' => line numbers that still have the old address (expressions, arrays)]
 */
function siteURLChangeReplacePHPStringLiterals($code, $map)
{
    $tokens = token_get_all($code);
    $changes = [];
    $replace = [];
    foreach (siteURLChangeReadPHPAssignments($code) as $name => $assignment) {
        if (stripos($name, 'mysql') === 0) {
            continue; // the database server keeps its name even when it is the site domain
        }
        foreach ($assignment['all'] as $item) {
            $token = $tokens[$item['token']];
            if (!$item['literal'] || !is_array($token) || $token[0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }
            $old = siteURLChangeDecodePHPString($token[1]);
            $new = siteURLChangeReplaceValue($old, $map);
            if ($old !== null && $new !== $old) {
                $replace[$item['token']] = var_export($new, true);
                $changes[] = ['name' => $name, 'old' => $old, 'new' => $new];
            }
        }
    }
    $content = '';
    foreach ($tokens as $index => $token) {
        $content .= isset($replace[$index]) ? $replace[$index] : (is_array($token) ? $token[1] : $token);
    }
    $remaining = [];
    foreach (preg_split('/\r\n|\n|\r/', $content) as $number => $line) {
        if (preg_match('~^\s*(//|#|\*)~', $line)) {
            continue; // comments
        }
        if (siteURLChangeReplaceServerConfig($line, $map) !== $line || siteURLChangeReplaceValue($line, $map) !== $line) {
            $remaining[] = $number + 1;
        }
    }
    return ['content' => $content, 'changes' => $changes, 'remaining' => $remaining];
}

/**
 * Lines of other files with the old domain (not a subdomain or a longer domain), to report what
 * this script does not change (/etc/hosts, crontabs, systemd units, another NGINX).
 *
 * @return array "file:line" => trimmed line
 */
function siteURLChangeFindMentions(array $files, $map)
{
    $pattern = '~(?<![A-Za-z0-9.@_\-])' . siteURLChangeHostRegex($map) . '~i';
    $mentions = [];
    foreach ($files as $file) {
        $content = @file_get_contents($file);
        if ($content === false || stripos($content, $map['oldHost']) === false) {
            continue;
        }
        foreach (preg_split('/\r\n|\n|\r/', $content) as $number => $line) {
            if (preg_match($pattern, $line) && !preg_match('/^\s*#/', $line)) {
                $mentions["{$file}:" . ($number + 1)] = trim($line);
            }
        }
    }
    return $mentions;
}

/**
 * A value that was valid JSON must still be valid JSON after the change.
 */
function siteURLChangeKeepsJSONValid($old, $new)
{
    $first = substr(ltrim((string) $old), 0, 1);
    if ($first !== '{' && $first !== '[') {
        return true;
    }
    json_decode($old);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return true; // it was not valid before (some old plugin parameters are not)
    }
    json_decode($new);
    return json_last_error() === JSON_ERROR_NONE;
}

// --- Files and commands (Apache, NGINX, configuration.php) ------------------------------------

function siteURLChangeFindExecutable(array $paths)
{
    foreach ($paths as $path) {
        if (@is_executable($path)) {
            return $path;
        }
    }
    return null;
}

/**
 * Runs a command without a shell (each argument passed as is).
 *
 * @return int exit code (-1 when it could not start)
 */
function siteURLChangeRun(array $command, $show = false, &$output = null)
{
    $null = DIRECTORY_SEPARATOR === '/' ? '/dev/null' : 'NUL';
    $process = proc_open($command, [0 => ['file', $null, 'r'], 1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes);
    if (!is_resource($process)) {
        $output = '';
        return -1;
    }
    $output = '';
    while (($line = fgets($pipes[1])) !== false) {
        $output .= $line;
        if ($show) {
            echo '   ' . $line;
        }
    }
    fclose($pipes[1]);
    return proc_close($process);
}

function siteURLChangeBackupFile($path, $backupDir)
{
    $copy = $backupDir . '/files/' . ltrim(str_replace(':', '', $path), '/');
    if (is_file($copy)) {
        return; // keep the original of this run
    }
    if (!is_dir(dirname($copy))) {
        mkdir(dirname($copy), 0700, true);
    }
    if (!copy($path, $copy)) {
        throw new RuntimeException("could not copy {$path} to the backup folder");
    }
    @chmod($copy, 0600);
}

/**
 * Writes the new content to a temporary file next to the original (same permissions and owner).
 *
 * @return array the prepared file, for siteURLChangeCommitFile()
 */
function siteURLChangePrepareFile($path, $content, $lintPHP = false)
{
    $real = realpath($path);
    if ($real === false || !is_writable($real) || !is_writable(dirname($real))) {
        throw new RuntimeException("{$path} is not writable (run as root or as the owner of the file)");
    }
    $temporary = dirname($real) . '/.' . basename($real) . '.tmp-' . bin2hex(random_bytes(4));
    if (file_put_contents($temporary, $content, LOCK_EX) !== strlen($content)) {
        @unlink($temporary);
        throw new RuntimeException("could not write next to {$real}");
    }
    @chmod($temporary, fileperms($real) & 0777);
    if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
        @chown($temporary, fileowner($real));
        @chgrp($temporary, filegroup($real));
    }
    if ($lintPHP) {
        $code = siteURLChangeRun([PHP_BINARY, '-l', $temporary], false, $output);
        if ($code !== 0) {
            @unlink($temporary);
            throw new RuntimeException('the new file did not pass php -l: ' . trim($output));
        }
    }
    return ['path' => $real, 'temporary' => $temporary];
}

function siteURLChangeCommitFile($prepared, $backupDir)
{
    siteURLChangeBackupFile($prepared['path'], $backupDir);
    if (!rename($prepared['temporary'], $prepared['path'])) {
        @unlink($prepared['temporary']);
        throw new RuntimeException("could not replace {$prepared['path']}");
    }
}

function siteURLChangeDiscardFile($prepared)
{
    if (!empty($prepared['temporary'])) {
        @unlink($prepared['temporary']);
    }
}

function siteURLChangeWriteFile($path, $content, $backupDir)
{
    siteURLChangeCommitFile(siteURLChangePrepareFile($path, $content), $backupDir);
}

/**
 * Tables whose old values are history or are rebuilt: caches, logs, statistics, sessions, the
 * IP location database, and payment records (invoices, checkouts, wallet), which are kept as
 * they were sent to the payment provider.
 * live_restreams_logs is not only history: its restreamer URL is used again to check and stop
 * the restreams that are running (Live_restreams_logs::getURL()).
 */
function siteURLChangeIsSkippedTable($table)
{
    if (strtolower($table) === 'live_restreams_logs') {
        return false;
    }
    return (bool) preg_match('/cache|audit|statistic|session|ip2location|invoice|payment|checkout|wallet|transaction|(^|_)logs?$|_history$/i', $table);
}

/**
 * Generated files that keep full addresses and are not removed by ObjectYPT::deleteALLCache():
 * Video::getVideosPaths() answers from videos/permanentCache/getVideosPaths_*.tmp when the cache
 * is empty (the video sources, with the old domain, until it is rebuilt in the background) and
 * the PlayerSkins EPG keeps videos/EPGCache/*.cache for 24 hours. Both are rebuilt when missing.
 *
 * @return array files to delete
 */
function siteURLChangeListGeneratedCacheFiles($videosDir)
{
    $videosDir = rtrim($videosDir, '/\\') . '/';
    return array_merge(
        glob($videosDir . 'permanentCache/getVideosPaths_*.tmp') ?: [],
        glob($videosDir . 'EPGCache/*.cache') ?: []
    );
}

function siteURLChangeListConfigFiles(array $dirs, $recursive)
{
    $files = [];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile() && preg_match('/\.conf$/', $file->getFilename())) {
                    $files[] = $file->getPathname();
                }
            }
        } else {
            $files = array_merge($files, glob(rtrim($dir, '/') . '/*.conf') ?: []);
        }
    }
    $unique = [];
    foreach ($files as $file) {
        $real = realpath($file);
        if ($real !== false && is_file($real)) {
            $unique[$real] = $real; // sites-enabled links to sites-available
        }
    }
    return array_values($unique);
}

function siteURLChangePlanServerFiles(array $files, $map)
{
    $plan = ['files' => []];
    foreach ($files as $file) {
        $content = @file_get_contents($file);
        if ($content === false || stripos($content, $map['oldHost']) === false) {
            continue;
        }
        $new = siteURLChangeReplaceServerConfig($content, $map);
        if ($new !== $content) {
            $plan['files'][$file] = siteURLChangeChangedLines($content, $new);
        }
    }
    return $plan;
}

function siteURLChangeServerFilesMention(array $files, $text)
{
    foreach (array_keys($files) as $file) {
        if (stripos((string) @file_get_contents($file), $text) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Changes the files of one web server, checks the configuration and reloads it.
 * When the check fails, the files are restored and the server is not reloaded.
 */
function siteURLChangeApplyServerFiles($name, $group, $map, $backupDir)
{
    $previous = [];
    try {
        foreach (array_keys($group['plan']['files']) as $file) {
            $content = file_get_contents($file);
            $new = siteURLChangeReplaceServerConfig($content, $map);
            if ($new !== $content) {
                $previous[$file] = $content;
                siteURLChangeWriteFile($file, $new, $backupDir);
            }
        }
    } catch (\Throwable $th) {
        echo "{$name}: " . $th->getMessage() . PHP_EOL;
        siteURLChangeRestoreFiles($previous);
        return false;
    }
    if (empty($previous)) {
        return true;
    }
    echo "{$name}: " . count($previous) . ' file(s) changed.' . PHP_EOL;
    if (!empty($group['test'])) {
        if (siteURLChangeRun($group['test'], false, $output) !== 0) {
            echo "{$name} check failed:" . PHP_EOL . '   ' . trim(str_replace("\n", "\n   ", trim($output))) . PHP_EOL;
            siteURLChangeRestoreFiles($previous);
            return false;
        }
    }
    if (!empty($group['reload'])) {
        if (siteURLChangeRun($group['reload'], false, $output) !== 0) {
            echo "{$name} reload failed: " . trim($output) . PHP_EOL;
        } else {
            echo "{$name} reloaded." . PHP_EOL;
        }
    } else {
        echo "{$name} was not reloaded (not running here): restart it to use the new files." . PHP_EOL;
    }
    return true;
}

function siteURLChangeRestoreFiles(array $previous)
{
    foreach ($previous as $file => $content) {
        if (file_put_contents($file, $content) === false) {
            echo "COULD NOT RESTORE {$file}: copy it back from the backup folder." . PHP_EOL;
        }
    }
}
