<?php

/**
 * LiveRestreamWatchdog
 *
 * Configurable watchdog, enabled by default, for the Live plugin restream feature.
 *
 * Scope (v1): only detects DESTINATION/OUTPUT disconnections of an already-started restream
 * (Broken pipe, Error muxing a packet, Error writing trailer, unexpected FFmpeg process
 * termination) while its source live is still active, and restarts it using the EXISTING
 * Live::restream() start flow. It never duplicates the FFmpeg start command, never runs a new
 * daemon, and is invoked from Live::executeEveryMinute() only.
 *
 * Explicitly out of scope for v1: source/HLS recovery, notifications/webhooks, and complex retry
 * strategies beyond a simple cooldown + max-attempts window.
 *
 * State (last failure time, last restart attempt time, restart attempt count, last successful
 * start time, last known PID, last failure reason, pending validation) is persisted as small JSON
 * files under ObjectYPT::getTmpCacheDir(), mirroring the existing
 * Live::getRestreamReconnectState()/saveRestreamReconnectState() pattern used for source
 * reconnection handling. No new database table/migration is used.
 */
class LiveRestreamWatchdog
{
    const LOG_PREFIX = '[LiveRestreamWatchdog]';
    const STATE_DIR = 'liveRestreamWatchdog';

    // evaluateRecheckedProcessState() decision outcomes
    const RECHECK_INCONCLUSIVE = 'inconclusive';
    const RECHECK_COMPLETED = 'completed';
    const RECHECK_RUNNING = 'running';
    const RECHECK_STILL_DOWN = 'still_down';

    // Patterns that classify a destination/output disconnection (diagnostics only, never the
    // sole trigger for a restart while the process is still running).
    private static $outputErrorPatterns = [
        'Broken pipe',
        'Error muxing a packet',
        'Error writing trailer',
    ];

    /**
     * Entry point, called once per minute from Live::executeEveryMinute() when
     * $objLive->enableRestreamWatchdog is enabled. Never blocks/sleeps and never restarts more
     * than the restreams that are actually found broken on this pass.
     *
     * @param stdClass $objLive Live plugin config object (AVideoPlugin::getDataObject('Live'))
     */
    public static function run($objLive = null)
    {
        if (empty($objLive)) {
            $objLive = AVideoPlugin::getDataObject('Live');
        }
        if (empty($objLive) || empty($objLive->enableRestreamWatchdog)) {
            return;
        }

        require_once __DIR__ . '/../standAloneFiles/functions.php';

        $activeLives = LiveTransmitionHistory::getActiveLives('', false);
        if (empty($activeLives) || !is_array($activeLives)) {
            return;
        }

        foreach ($activeLives as $live) {
            $liveTransmitionHistory_id = intval($live['id']);
            $users_id = intval($live['users_id']);
            if (empty($liveTransmitionHistory_id) || empty($users_id)) {
                continue;
            }

            // getActiveLives() only reflects LiveTransmitionHistory.finished IS NULL, which can be
            // stale (stats endpoint hiccup, history update delay) or simply not authoritative
            // enough on its own to decide to restart destination processes. Confirm with the
            // existing, already-cached (~90s) Live::isLiveAndIsReadyFromKey() check, once per live
            // (not per destination, so this stays cheap even with several restreams configured).
            // A brief OBS reconnect is tolerated by that same cache, so this does not block
            // recovery of a destination-only failure while the source itself is genuinely live.
            $key = @$live['key'];
            $live_servers_id = intval(@$live['live_servers_id']);
            if (empty($key) || !Live::isLiveAndIsReadyFromKey($key, $live_servers_id)) {
                continue;
            }

            // Match by actual usage on this live session, not by ownership: a restream
            // destination can be configured on an admin account while the live streams under a
            // different account (e.g. an operator/encoder account).
            $restreams = Live_restreams::getAllFromLiveTransmitionHistory($liveTransmitionHistory_id);
            if (empty($restreams) || !is_array($restreams)) {
                continue;
            }

            foreach ($restreams as $restream) {
                try {
                    self::checkRestream($live, $restream, $objLive);
                } catch (\Throwable $th) {
                    _error_log(self::LOG_PREFIX . " unexpected error checking restream="
                        . intval(@$restream['id']) . " live={$liveTransmitionHistory_id}: " . $th->getMessage());
                }
            }
        }
    }

    /**
     * Evaluates a single (restream destination, live session) pair and restarts it if needed.
     */
    private static function checkRestream($live, $restream, $objLive)
    {
        $liveTransmitionHistory_id = intval($live['id']);
        $live_restreams_id = intval($restream['id']);
        if (empty($live_restreams_id)) {
            return;
        }

        $label = "restream={$live_restreams_id} live={$liveTransmitionHistory_id} key=" . self::maskSecret(@$live['key']);

        // Only watch restreams that have actually been started at least once for this session.
        // This naturally excludes restreams that are scheduled/never triggered.
        $latestLog = Live_restreams_logs::getLatest($liveTransmitionHistory_id, $live_restreams_id);
        if (empty($latestLog)) {
            return;
        }

        $status = self::fetchLogStatus($liveTransmitionHistory_id, $live_restreams_id, $latestLog['id'], 'log');
        if (empty($status)) {
            // Could not reach the restreamer (local or remote) at all; do nothing this cycle,
            // avoid guessing and possibly restarting a restream that is actually fine.
            return;
        }

        if (!empty($status->completed)) {
            // The restream was explicitly stopped (or finished) via the existing 'stop' action,
            // which renames the log file to *.completed. Never restart in this case.
            self::clearState($live_restreams_id, $liveTransmitionHistory_id, "manually stopped/completed {$label}");
            return;
        }

        $localProcess = self::getLocalProcess($live_restreams_id, $liveTransmitionHistory_id);
        $processRunning = ($localProcess !== false) || !empty($status->isActive);

        $state = self::getState($live_restreams_id, $liveTransmitionHistory_id);

        if ($processRunning) {
            self::handleHealthy($live_restreams_id, $liveTransmitionHistory_id, $state, $localProcess, $objLive, $label);
            return;
        }

        self::handleNotRunning($live, $restream, $status, $state, $objLive, $label);
    }

    private static function handleHealthy($live_restreams_id, $liveTransmitionHistory_id, $state, $localProcess, $objLive, $label)
    {
        $now = time();
        $pid = !empty($localProcess['pid']) ? intval($localProcess['pid']) : null;

        if (!empty($state['pending_validation'])) {
            if (!empty($pid) && !empty($state['last_known_pid']) && $pid != $state['last_known_pid']) {
                _error_log(self::LOG_PREFIX . " restart validated: new process pid={$pid} previous pid={$state['last_known_pid']} {$label}");
            } else {
                _error_log(self::LOG_PREFIX . " restart validated: restream is running again {$label}");
            }
            $state['pending_validation'] = false;
            $state['last_success_start_at'] = $now;
        }

        if (empty($state['healthy_since'])) {
            $state['healthy_since'] = $now;
        }
        if (!empty($pid)) {
            $state['last_known_pid'] = $pid;
        }

        $healthyResetSeconds = self::getConfigInt($objLive, 'restreamWatchdogHealthyResetSeconds', 600);
        if (!empty($state['restart_attempts']) && ($now - $state['healthy_since']) >= $healthyResetSeconds) {
            _error_log(self::LOG_PREFIX . " restream healthy for {$healthyResetSeconds}s, resetting restart attempt counter {$label}");
            $state['restart_attempts'] = [];
            $state['last_failure_reason'] = null;
        }

        self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
    }

    private static function handleNotRunning($live, $restream, $status, $state, $objLive, $label)
    {
        $liveTransmitionHistory_id = intval($live['id']);
        $live_restreams_id = intval($restream['id']);
        $now = time();

        if (!empty($state['pending_validation'])) {
            _error_log(self::LOG_PREFIX . " restart attempt did not recover the restream (still not running) {$label}");
            $state['pending_validation'] = false;
        }

        // Fetch the heavier log content only now, to classify the failure reason for diagnostics.
        $contentStatus = self::fetchLogStatus($liveTransmitionHistory_id, $live_restreams_id, null, 'logContent');
        $failureReason = self::classifyFailure($contentStatus);

        $state['healthy_since'] = null;
        $state['last_failure_at'] = $now;
        $state['last_failure_reason'] = $failureReason;

        _error_log(self::LOG_PREFIX . " restream disconnected from destination, reason=\"{$failureReason}\" {$label}");
        self::logDiagnosticSnapshot($live, $restream, $contentStatus, $state, $label);

        $windowSeconds = self::getConfigInt($objLive, 'restreamWatchdogWindowSeconds', 900);
        $attempts = self::pruneAttempts(@$state['restart_attempts'], $windowSeconds, $now);
        $state['restart_attempts'] = $attempts;

        $maxAttempts = self::getConfigInt($objLive, 'restreamWatchdogMaxAttempts', 3);
        if (count($attempts) >= $maxAttempts) {
            _error_log(self::LOG_PREFIX . " maximum restart attempts reached ({$maxAttempts} within {$windowSeconds}s), will not restart automatically {$label}");
            self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
            return;
        }

        $cooldownSeconds = self::getConfigInt($objLive, 'restreamWatchdogCooldownSeconds', 120);
        if (!empty($state['last_restart_attempt_at']) && ($now - $state['last_restart_attempt_at']) < $cooldownSeconds) {
            _error_log(self::LOG_PREFIX . " cooldown active, skipping restart attempt this cycle {$label}");
            self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
            return;
        }

        self::attemptRestart($live, $restream, $objLive, $label);
    }

    private static function attemptRestart($live, $restream, $objLive, $label)
    {
        $liveTransmitionHistory_id = intval($live['id']);
        $live_restreams_id = intval($restream['id']);

        $lock = self::acquireLock($live_restreams_id, $liveTransmitionHistory_id);
        if (empty($lock)) {
            _error_log(self::LOG_PREFIX . " lock unavailable, another watchdog cycle is already handling this restream {$label}");
            return;
        }

        try {
            $now = time();

            // Re-read the state from disk: another (overlapping) execution may have updated it
            // between our initial check and acquiring the lock. All limits (window/max-attempts/
            // cooldown) MUST be re-applied here, against this freshly re-read state, otherwise two
            // overlapping executeEveryMinute() cycles could each pass the pre-lock checks using
            // stale data and both restart the same destination, exceeding the configured limits.
            $state = self::getState($live_restreams_id, $liveTransmitionHistory_id);

            $windowSeconds = self::getConfigInt($objLive, 'restreamWatchdogWindowSeconds', 900);
            $attempts = self::pruneAttempts(@$state['restart_attempts'], $windowSeconds, $now);
            $state['restart_attempts'] = $attempts;

            $maxAttempts = self::getConfigInt($objLive, 'restreamWatchdogMaxAttempts', 3);
            if (count($attempts) >= $maxAttempts) {
                _error_log(self::LOG_PREFIX . " maximum restart attempts reached ({$maxAttempts} within {$windowSeconds}s) after acquiring lock, will not restart {$label}");
                self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
                return;
            }

            $cooldownSeconds = self::getConfigInt($objLive, 'restreamWatchdogCooldownSeconds', 120);
            if (!empty($state['last_restart_attempt_at']) && ($now - $state['last_restart_attempt_at']) < $cooldownSeconds) {
                _error_log(self::LOG_PREFIX . " cooldown active after acquiring lock, another execution just restarted this restream {$label}");
                self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
                return;
            }

            // Recheck the process state now that we hold the lock, to confirm no other process
            // (this watchdog, a manual action, or FFmpeg itself) already restarted it.
            $recheckProcess = self::getLocalProcess($live_restreams_id, $liveTransmitionHistory_id);
            $recheckStatus = self::fetchLogStatus($liveTransmitionHistory_id, $live_restreams_id, null, 'log');
            $recheckDecision = self::evaluateRecheckedProcessState($recheckProcess, $recheckStatus);

            if ($recheckDecision === self::RECHECK_INCONCLUSIVE) {
                // fetchLogStatus() can fail (timeout/network/malformed response) independently of
                // whether FFmpeg is still running; with no local process confirmation either,
                // never guess a restart out of an unconfirmed state.
                _error_log(self::LOG_PREFIX . " restart skipped: status unavailable/inconclusive after acquiring lock, will not guess {$label}");
                return;
            }

            if ($recheckDecision === self::RECHECK_COMPLETED) {
                // A manual stop (local or remote) may have happened between the first check and
                // acquiring the lock; a manually stopped/completed restream must never be revived.
                self::clearState($live_restreams_id, $liveTransmitionHistory_id, "manually stopped/completed during locked recheck {$label}");
                return;
            }

            if ($recheckDecision === self::RECHECK_RUNNING) {
                _error_log(self::LOG_PREFIX . " restart skipped: restream is running again before the watchdog acted {$label}");
                $state['pending_validation'] = false;
                if (!empty($recheckProcess['pid'])) {
                    $state['last_known_pid'] = intval($recheckProcess['pid']);
                }
                self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
                return;
            }

            $state['last_restart_attempt_at'] = $now;
            $state['restart_attempts'][] = $now;
            $state['pending_validation'] = true;
            self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);

            $attemptCount = count($state['restart_attempts']);
            _error_log(self::LOG_PREFIX . " restart attempt #{$attemptCount} started, reusing Live::restream() {$label}");

            $result = Live::restream($liveTransmitionHistory_id, $live_restreams_id);

            _error_log(self::LOG_PREFIX . " restart attempt #{$attemptCount} requested, result=" . json_encode(!empty($result)) . " {$label}");
        } finally {
            self::releaseLock($lock);
        }
    }

    // ---------------------------------------------------------------------
    // Detection helpers
    // ---------------------------------------------------------------------

    /**
     * Fetches restream log status/content using the existing, SSRF-safe, local-or-remote helper
     * (Live_restreams_logs::getURLFromTransmitionAndRestream()), mirroring the exact same call
     * used by plugin/Live/view/Live_restreams/getLogContent.json.php.
     */
    private static function fetchLogStatus($liveTransmitionHistory_id, $live_restreams_id, $live_restreams_logs_id, $action)
    {
        try {
            if (!empty($live_restreams_logs_id)) {
                $url = Live_restreams_logs::getURL($liveTransmitionHistory_id, $live_restreams_id, $live_restreams_logs_id, $action);
            } else {
                $url = Live_restreams_logs::getURLFromTransmitionAndRestream($liveTransmitionHistory_id, $live_restreams_id, $action);
            }
            if (empty($url)) {
                return false;
            }
            $content = url_get_contents($url, '', 0, false, false, false);
            if (empty($content)) {
                return false;
            }
            $obj = json_decode($content);
            if (empty($obj) || !empty($obj->error)) {
                return false;
            }
            return $obj;
        } catch (\Throwable $th) {
            _error_log(self::LOG_PREFIX . ' fetchLogStatus error: ' . $th->getMessage());
            return false;
        }
    }

    /**
     * Local-only (co-located restreamer) process lookup. Matches on the live_restreams_id /
     * liveTransmitionHistory_id query markers that startRestream() already appends to the m3u8
     * input URL, so it stays correct even though the existing getRestreamsRuning() grep pattern
     * ("ffmpeg -re -rw_timeout") no longer matches the actual generated command (the "-re" flag
     * is currently commented out in restreamer.json.php's startRestream()).
     */
    private static function getLocalProcess($live_restreams_id, $liveTransmitionHistory_id)
    {
        if (stripos(PHP_OS, 'WIN') !== false || !function_exists('exec')) {
            return false;
        }
        $output = [];
        $returnCode = 0;
        @exec('ps -eo pid,args 2>/dev/null | grep ffmpeg | grep -v grep', $output, $returnCode);
        if (empty($output)) {
            return false;
        }
        $needle1 = "live_restreams_id={$live_restreams_id}";
        $needle2 = "liveTransmitionHistory_id={$liveTransmitionHistory_id}";
        foreach ($output as $line) {
            if (strpos($line, $needle1) !== false && strpos($line, $needle2) !== false) {
                if (preg_match('/^\s*(\d+)/', $line, $m)) {
                    return ['pid' => intval($m[1]), 'line' => $line];
                }
            }
        }
        return false;
    }

    /**
     * Pure decision for the post-lock recheck in attemptRestart(): never guesses a restart out of
     * an unconfirmed state, and always defers to an explicit completed=true (manual stop) over
     * any isActive/process signal. No I/O, no locking, safe to unit test in isolation.
     *
     * @param array|false  $recheckProcess getLocalProcess() result
     * @param stdClass|false $recheckStatus fetchLogStatus() result
     * @return string One of the self::RECHECK_* constants
     */
    private static function evaluateRecheckedProcessState($recheckProcess, $recheckStatus)
    {
        if ($recheckStatus === false && $recheckProcess === false) {
            return self::RECHECK_INCONCLUSIVE;
        }

        if (!empty(@$recheckStatus->completed)) {
            return self::RECHECK_COMPLETED;
        }

        $stillDown = ($recheckProcess === false) && ($recheckStatus === false || empty($recheckStatus->isActive));
        return $stillDown ? self::RECHECK_STILL_DOWN : self::RECHECK_RUNNING;
    }

    private static function classifyFailure($contentStatus)
    {
        $content = !empty($contentStatus->content) ? $contentStatus->content : '';
        if (!empty($content)) {
            foreach (self::$outputErrorPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    return $pattern;
                }
            }
        }
        return 'FFmpeg process not running (unexpected termination)';
    }

    // ---------------------------------------------------------------------
    // State persistence (mirrors Live::getRestreamReconnectState* pattern)
    // ---------------------------------------------------------------------

    private static function getStateFile($live_restreams_id, $liveTransmitionHistory_id)
    {
        $hash = md5($live_restreams_id . '|' . $liveTransmitionHistory_id);
        return ObjectYPT::getTmpCacheDir() . self::STATE_DIR . "/{$hash}.json";
    }

    private static function getState($live_restreams_id, $liveTransmitionHistory_id)
    {
        $file = self::getStateFile($live_restreams_id, $liveTransmitionHistory_id);
        if (empty($file) || !file_exists($file)) {
            return self::emptyState();
        }
        $content = @file_get_contents($file);
        $state = json_decode($content, true);
        if (!is_array($state)) {
            return self::emptyState();
        }
        return array_merge(self::emptyState(), $state);
    }

    private static function emptyState()
    {
        return [
            'last_failure_at' => null,
            'last_restart_attempt_at' => null,
            'restart_attempts' => [],
            'last_success_start_at' => null,
            'last_known_pid' => null,
            'last_failure_reason' => null,
            'healthy_since' => null,
            'pending_validation' => false,
        ];
    }

    private static function saveState($live_restreams_id, $liveTransmitionHistory_id, $state)
    {
        $file = self::getStateFile($live_restreams_id, $liveTransmitionHistory_id);
        @_file_put_contents($file, json_encode($state));
    }

    private static function clearState($live_restreams_id, $liveTransmitionHistory_id, $reasonForLog = '')
    {
        if (!empty($reasonForLog)) {
            _error_log(self::LOG_PREFIX . " skipping restart: {$reasonForLog}");
        }
        $file = self::getStateFile($live_restreams_id, $liveTransmitionHistory_id);
        if (!empty($file) && file_exists($file)) {
            @unlink($file);
        }
    }

    private static function pruneAttempts($attempts, $windowSeconds, $now)
    {
        if (empty($attempts) || !is_array($attempts)) {
            return [];
        }
        $result = [];
        foreach ($attempts as $timestamp) {
            $timestamp = intval($timestamp);
            if ($timestamp >= ($now - $windowSeconds)) {
                $result[] = $timestamp;
            }
        }
        return $result;
    }

    // ---------------------------------------------------------------------
    // Per-restream locking (separate from the existing FFmpeg-start lock in
    // restreamer.json.php's startRestream(); this one only guards the watchdog's own
    // restart decision against overlapping executeEveryMinute() cycles).
    // ---------------------------------------------------------------------

    private static function acquireLock($live_restreams_id, $liveTransmitionHistory_id)
    {
        $hash = md5($live_restreams_id . '|' . $liveTransmitionHistory_id);
        $file = ObjectYPT::getTmpCacheDir() . self::STATE_DIR . "/{$hash}.lock";
        make_path($file);
        $handle = @fopen($file, 'c');
        if (empty($handle)) {
            return false;
        }
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }
        return $handle;
    }

    private static function releaseLock($handle)
    {
        if (!empty($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    // ---------------------------------------------------------------------
    // Lightweight, diagnostic-only resource snapshot. Never used to trigger a restart by itself.
    // ---------------------------------------------------------------------

    private static function logDiagnosticSnapshot($live, $restream, $contentStatus, $state, $label)
    {
        global $global;

        $snapshot = [
            'restream_id' => intval($restream['id']),
            'liveTransmitionHistory_id' => intval($live['id']),
            'sourceHost' => self::getSourceHost($live),
            'destinationHost' => parse_url(@$restream['stream_url'], PHP_URL_HOST),
            'lastKnownPid' => !empty($state['last_known_pid']) ? intval($state['last_known_pid']) : null,
            'loadAverage' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            'diskFreeBytes' => @disk_free_space($global['systemRootPath']),
            'memory' => self::getMemoryInfo(),
            'ffmpegProcessCount' => self::getFfmpegProcessCount(),
            'lastLogLines' => self::getLastLogLines($contentStatus, 10),
        ];

        _error_log(self::LOG_PREFIX . ' diagnostic snapshot ' . $label . ' ' . json_encode($snapshot));
    }

    private static function getSourceHost($live)
    {
        global $global;
        $live_servers_id = intval(@$live['live_servers_id']);
        if (!empty($live_servers_id)) {
            try {
                $server = new Live_servers($live_servers_id);
                $host = parse_url($server->getPlayerServer(), PHP_URL_HOST);
                if (!empty($host)) {
                    return $host;
                }
            } catch (\Throwable $th) {
                // fall through to default host below
            }
        }
        return parse_url(@$global['webSiteRootURL'], PHP_URL_HOST);
    }

    private static function getMemoryInfo()
    {
        $meminfoFile = '/proc/meminfo';
        if (!is_readable($meminfoFile)) {
            return null;
        }
        $lines = @file($meminfoFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return null;
        }
        $data = [];
        foreach ($lines as $line) {
            if (preg_match('/^(MemTotal|MemAvailable|MemFree):\s*(\d+)/', $line, $m)) {
                $data[$m[1]] = intval($m[2]); // kB
            }
        }
        return empty($data) ? null : $data;
    }

    private static function getFfmpegProcessCount()
    {
        if (stripos(PHP_OS, 'WIN') !== false || !function_exists('exec')) {
            return null;
        }
        $output = [];
        @exec('ps -eo pid 2>/dev/null | grep -c ffmpeg', $output);
        return !empty($output[0]) ? intval($output[0]) : 0;
    }

    private static function getLastLogLines($contentStatus, $numberOfLines)
    {
        if (empty($contentStatus->content)) {
            return null;
        }
        $lines = explode("\n", $contentStatus->content);
        return array_slice($lines, -$numberOfLines);
    }

    // ---------------------------------------------------------------------
    // Small utilities
    // ---------------------------------------------------------------------

    private static function getConfigInt($objLive, $field, $default)
    {
        if (empty($objLive) || !isset($objLive->{$field}) || $objLive->{$field} === '') {
            return $default;
        }
        return intval($objLive->{$field});
    }

    /**
     * Masks a secret/identifier for logging: keeps only a short, non-reversible prefix, never the
     * full stream key/URL/token.
     */
    private static function maskSecret($value)
    {
        if (empty($value)) {
            return '';
        }
        $value = (string) $value;
        if (strlen($value) <= 4) {
            return str_repeat('*', strlen($value));
        }
        return substr($value, 0, 4) . str_repeat('*', min(8, strlen($value) - 4));
    }
}
