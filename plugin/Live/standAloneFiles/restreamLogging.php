<?php

/**
 * restreamLogging.php
 *
 * Structured, per-destination diagnostic logging for the restream pipeline. Framework-independent
 * (only requires restreamProfiles.php), so it can be copied alongside restreamer.json.php to a
 * standalone FFMPEG host, and is also required by LiveRestreamWatchdog.php (framework side).
 *
 * Every event is a single JSON line via error_log(), prefixed "[RestreamEvent]" so it can be
 * grepped independently of the existing free-text error_log() lines this plugin already writes
 * (those are left untouched - this is additive, not a replacement).
 *
 * All I/O here (exec/file reads) is best-effort: individual metrics are wrapped so one failing
 * command never prevents the rest of a diagnostic snapshot from being collected, and every
 * unavailable metric is explicitly reported as such rather than silently omitted.
 */

require_once __DIR__ . '/restreamProfiles.php';

define('RESTREAM_EVENT_LOG_PREFIX', '[RestreamEvent]');

function rl_hostname()
{
    $h = @php_uname('n');
    return $h ?: 'unknown-host';
}

/**
 * Short container id when running under Docker/containerd, null otherwise. Never requires
 * privileged access - only reads /proc/self/cgroup, which is always world-readable for the
 * process itself.
 */
function rl_containerId()
{
    $file = '/proc/self/cgroup';
    if (!is_readable($file)) {
        return null;
    }
    $content = @file_get_contents($file);
    if (empty($content)) {
        return null;
    }
    if (preg_match('/[0-9a-f]{64}/', $content, $m)) {
        return substr($m[0], 0, 12);
    }
    // cgroup v2 unified hierarchy sometimes only exposes the id in /proc/self/mountinfo.
    $hostnameFile = '/etc/hostname';
    if (is_readable($hostnameFile)) {
        $h = trim((string) @file_get_contents($hostnameFile));
        if (preg_match('/^[0-9a-f]{12}$/i', $h)) {
            return $h;
        }
    }
    return null;
}

function rl_newCorrelationId()
{
    try {
        return bin2hex(random_bytes(8));
    } catch (\Throwable $th) {
        return substr(md5(uniqid('', true)), 0, 16);
    }
}

/**
 * Emits one structured lifecycle event as a single JSON line. Never throws - a logging failure
 * must never interrupt restream processing.
 *
 * @param string $event One of the documented lifecycle event names (see restreamer.json.php /
 *                       LiveRestreamWatchdog.php call sites for the full list).
 * @param array  $fields Event-specific fields. Never pass raw secrets here - callers must
 *                       redact (redactSecretsInText()/getDestinationHostPort()) beforehand.
 */
function rl_logEvent($event, array $fields = array())
{
    try {
        $envelope = array(
            'tsUtc' => gmdate('Y-m-d\TH:i:s') . sprintf('.%03dZ', (int) (microtime(true) * 1000) % 1000),
            'hostname' => rl_hostname(),
            'containerId' => rl_containerId(),
            'event' => (string) $event,
        );
        error_log(RESTREAM_EVENT_LOG_PREFIX . ' ' . json_encode(array_merge($envelope, $fields)));
    } catch (\Throwable $th) {
        // Never let a logging failure break the caller.
    }
}

/**
 * Bounded tail reader: reads at most $maxBytes from the end of $path, never loading the whole
 * file into memory regardless of how large it has grown. This is the "ring buffer" read used by
 * progress parsing and incident diagnostics, so normal long-running progress output never causes
 * an unbounded read.
 */
function rl_tailFile($path, $maxBytes = 65536)
{
    if (empty($path) || !is_readable($path)) {
        return '';
    }
    $size = @filesize($path);
    if ($size === false) {
        return '';
    }
    $handle = @fopen($path, 'rb');
    if (!$handle) {
        return '';
    }
    $start = max(0, $size - $maxBytes);
    fseek($handle, $start);
    $data = stream_get_contents($handle);
    fclose($handle);
    return $data === false ? '' : $data;
}

/**
 * Keeps a destination's FFmpeg log file bounded so a multi-hour stream's periodic progress
 * output cannot fill the disk. Called from the watchdog's periodic cycle (never from the hot
 * FFmpeg-writing path), keeping only the last $maxBytes when the file exceeds $maxBytes*2.
 */
function rl_truncateLogFileIfTooLarge($path, $maxBytes = 2097152)
{
    if (empty($path) || !is_writable($path)) {
        return false;
    }
    $size = @filesize($path);
    if ($size === false || $size <= ($maxBytes * 2)) {
        return false;
    }
    $tail = rl_tailFile($path, $maxBytes);
    $marker = "...[log truncated by restreamLogging, kept last " . strlen($tail) . " bytes]...\n";
    return @file_put_contents($path, $marker . $tail) !== false;
}

/**
 * Simple per-key rate limiter for expensive/verbose diagnostics (system snapshot), backed by a
 * single marker file's mtime. Returns true only when at least $minIntervalSeconds have elapsed
 * since the last time this exact key was allowed.
 */
function rl_shouldEmitSnapshot($key, $minIntervalSeconds = 300)
{
    $dir = sys_get_temp_dir() . '/restream_diag';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $key) . '.marker';
    $last = @filemtime($file);
    $now = time();
    if ($last !== false && ($now - $last) < $minIntervalSeconds) {
        return false;
    }
    @touch($file);
    return true;
}

/**
 * Runs a shell command with a short timeout and returns its trimmed stdout, or null when the
 * binary is unavailable/unusable/times out. Every diagnostic sub-check funnels through this so
 * "gracefully skip when unavailable" is enforced in one place.
 */
function rl_safeExec($command)
{
    if (!function_exists('exec') || stripos(PHP_OS, 'WIN') !== false) {
        return null;
    }
    $output = array();
    $returnCode = 0;
    @exec($command . ' 2>/dev/null', $output, $returnCode);
    if ($returnCode !== 0 && empty($output)) {
        return null;
    }
    return implode("\n", $output);
}

/**
 * Best-effort, bounded, non-blocking system diagnostic snapshot. Every section is independently
 * guarded: a missing/failing command yields 'unavailable' for that section only, never an
 * exception, and never requires elevated privileges. Callers MUST gate calls to this through
 * rl_shouldEmitSnapshot() to keep it rate limited.
 *
 * @param array $context Optional: ['pid' => ffmpeg pid, 'destinationHost' => host, 'appName' => ...]
 */
function rl_getDiagnosticSnapshot(array $context = array())
{
    $snapshot = array();

    // --- CPU / memory ---
    $snapshot['loadAverage'] = function_exists('sys_getloadavg') ? sys_getloadavg() : 'unavailable';

    $meminfo = @is_readable('/proc/meminfo') ? @file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;
    if (!empty($meminfo)) {
        $mem = array();
        foreach ($meminfo as $line) {
            if (preg_match('/^(MemTotal|MemAvailable|MemFree|SwapTotal|SwapFree):\s*(\d+)/', $line, $m)) {
                $mem[$m[1]] = (int) $m[2]; // kB
            }
        }
        $snapshot['memoryKb'] = $mem ?: 'unavailable';
    } else {
        $snapshot['memoryKb'] = 'unavailable';
    }

    if (!empty($context['pid'])) {
        $ps = rl_safeExec('ps -o %cpu,%rss,stat -p ' . (int) $context['pid']);
        $snapshot['ffmpegProcess'] = $ps !== null ? trim($ps) : 'unavailable (process gone or ps missing)';
    } else {
        $snapshot['ffmpegProcess'] = 'unavailable (no pid provided)';
    }
    $appPs = rl_safeExec('ps -o %cpu,%rss -p ' . getmypid());
    $snapshot['appProcess'] = $appPs !== null ? trim($appPs) : 'unavailable';

    // --- Container cgroup limits (v2 first, v1 fallback) ---
    $snapshot['cgroup'] = rl_getCgroupInfo();

    // --- OOM evidence (best-effort, dmesg is frequently unreadable without privileges) ---
    $dmesg = rl_safeExec('dmesg -T --level=err,warn 2>/dev/null | tail -n 200');
    if ($dmesg === null) {
        $dmesg = rl_safeExec('dmesg | tail -n 200');
    }
    if ($dmesg !== null && preg_match('/(out of memory|killed process|oom-killer|oom_kill)/i', $dmesg, $m)) {
        $snapshot['oomEvidence'] = true;
        $snapshot['oomEvidenceLine'] = trim(substr($dmesg, max(0, strpos($dmesg, $m[0]) - 60), 200));
    } elseif ($dmesg !== null) {
        $snapshot['oomEvidence'] = false;
    } else {
        $snapshot['oomEvidence'] = 'unavailable (dmesg not permitted)';
    }

    // --- Disk ---
    $snapshot['diskFreeBytes'] = @disk_free_space(sys_get_temp_dir());
    $dfInodes = rl_safeExec('df -Pi ' . escapeshellarg(sys_get_temp_dir()) . ' | tail -n 1');
    $snapshot['diskInodes'] = $dfInodes !== null ? trim($dfInodes) : 'unavailable';
    $iostat = rl_safeExec('iostat -x 1 2 2>/dev/null | tail -n 20');
    $snapshot['diskIo'] = $iostat !== null ? trim($iostat) : 'unavailable (iostat not installed)';

    // --- Network ---
    $netDev = @is_readable('/proc/net/dev') ? @file_get_contents('/proc/net/dev') : false;
    $snapshot['networkInterfaces'] = $netDev !== false ? trim($netDev) : 'unavailable';

    $host = !empty($context['destinationHost']) ? $context['destinationHost'] : null;
    if (!empty($host)) {
        $resolved = @gethostbyname($host);
        $snapshot['dnsResolution'] = array(
            'host' => $host,
            'resolvedIp' => ($resolved !== $host) ? $resolved : 'unavailable (resolution failed)',
        );
        $ss = rl_safeExec('ss -tn 2>/dev/null | grep ' . escapeshellarg($host));
        if ($ss === null || $ss === '') {
            $ss = rl_safeExec("ss -tn state established 2>/dev/null | grep -F " . escapeshellarg((string) $resolved));
        }
        $snapshot['tcpConnectionState'] = ($ss !== null && $ss !== '') ? trim($ss) : 'unavailable (no matching socket or ss missing)';
    } else {
        $snapshot['dnsResolution'] = 'unavailable (no destination host provided)';
        $snapshot['tcpConnectionState'] = 'unavailable (no destination host provided)';
    }

    $route = rl_safeExec('ip route show default');
    $snapshot['defaultRoute'] = $route !== null ? trim($route) : 'unavailable (ip command missing)';

    $conntrackCount = @is_readable('/proc/sys/net/netfilter/nf_conntrack_count') ? trim((string) @file_get_contents('/proc/sys/net/netfilter/nf_conntrack_count')) : null;
    $conntrackMax = @is_readable('/proc/sys/net/netfilter/nf_conntrack_max') ? trim((string) @file_get_contents('/proc/sys/net/netfilter/nf_conntrack_max')) : null;
    $snapshot['conntrack'] = ($conntrackCount !== null && $conntrackMax !== null)
        ? array('count' => (int) $conntrackCount, 'max' => (int) $conntrackMax)
        : 'unavailable';

    $kernelNet = rl_safeExec("dmesg -T 2>/dev/null | grep -iE 'network|oom|killed process' | tail -n 20");
    $snapshot['recentKernelMessages'] = $kernelNet !== null ? trim($kernelNet) : 'unavailable (dmesg not permitted)';

    return $snapshot;
}

function rl_getCgroupInfo()
{
    $info = array();

    // cgroup v2
    if (is_readable('/sys/fs/cgroup/memory.max') && is_readable('/sys/fs/cgroup/memory.current')) {
        $max = trim((string) @file_get_contents('/sys/fs/cgroup/memory.max'));
        $current = trim((string) @file_get_contents('/sys/fs/cgroup/memory.current'));
        $info['memoryLimit'] = ($max === 'max') ? 'unlimited' : $max;
        $info['memoryUsage'] = $current;
    } elseif (is_readable('/sys/fs/cgroup/memory/memory.limit_in_bytes')) {
        // cgroup v1
        $info['memoryLimit'] = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.limit_in_bytes'));
        $info['memoryUsage'] = is_readable('/sys/fs/cgroup/memory/memory.usage_in_bytes')
            ? trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.usage_in_bytes'))
            : 'unavailable';
    } else {
        $info['memoryLimit'] = 'unavailable (not containerized or cgroup path differs)';
        $info['memoryUsage'] = 'unavailable';
    }

    if (is_readable('/sys/fs/cgroup/cpu.max')) {
        $info['cpuQuota'] = trim((string) @file_get_contents('/sys/fs/cgroup/cpu.max'));
    } elseif (is_readable('/sys/fs/cgroup/cpu/cpu.cfs_quota_us')) {
        $info['cpuQuota'] = trim((string) @file_get_contents('/sys/fs/cgroup/cpu/cpu.cfs_quota_us'));
    } else {
        $info['cpuQuota'] = 'unavailable';
    }

    return $info;
}
