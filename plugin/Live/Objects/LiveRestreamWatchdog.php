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

    // computeRestreamPhase() - persisted (state['phase']) diagnostic state machine, purely for
    // operator/log visibility into what the watchdog currently believes about a restream. Note
    // PHASE_HEALTHY also covers the FIFO output layer's own internal recovery window: FIFO
    // recovery keeps the same OS process alive by design (see isProcessConsideredRunning()), so
    // it is structurally indistinguishable from "healthy" at this layer - the watchdog is
    // intentionally never the thing that detects/reacts to a FIFO-internal reconnect attempt.
    const PHASE_HEALTHY = 'healthy';
    const PHASE_DOWN = 'down';
    const PHASE_RESTARTING = 'restarting';
    const PHASE_BLOCKED = 'blocked';

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
        require_once __DIR__ . '/../standAloneFiles/restreamProfiles.php';
        require_once __DIR__ . '/../standAloneFiles/restreamLogging.php';

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
        $processRunning = self::isProcessConsideredRunning($localProcess, $status);

        $state = self::getState($live_restreams_id, $liveTransmitionHistory_id);

        if ($processRunning) {
            self::handleHealthy($live_restreams_id, $liveTransmitionHistory_id, $state, $localProcess, $objLive, $label);
            return;
        }

        self::handleNotRunning($live, $restream, $status, $state, $objLive, $label);
    }

    /**
     * Pure liveness decision, extracted from checkRestream() so it is directly unit-testable.
     *
     * Deliberately PID/process-existence based, never log-content based: while the opt-in FIFO
     * output-recovery layer (restreamProfiles.php) is internally reconnecting a destination
     * (a transient TCP/TLS hiccup), the FFmpeg OS process never exits and keeps the exact same
     * pid - so this always evaluates to "running" for that whole internal-recovery window, and
     * the watchdog correctly never treats FIFO's own recovery attempts as a failure requiring a
     * full process restart. Only once FIFO's own bounded max_recovery_attempts is exhausted and
     * FFmpeg actually exits does this evaluate to "not running", handing off to the watchdog as
     * the second, unconditional layer of protection (see restreamProfiles.php's module docblock).
     *
     * @param array|false $localProcess getLocalProcess() result
     * @param stdClass|false $status fetchLogStatus() result
     */
    private static function isProcessConsideredRunning($localProcess, $status)
    {
        return ($localProcess !== false) || !empty(@$status->isActive);
    }

    private static function handleHealthy($live_restreams_id, $liveTransmitionHistory_id, $state, $localProcess, $objLive, $label)
    {
        $now = time();
        $pid = !empty($localProcess['pid']) ? intval($localProcess['pid']) : null;

        $pidChanged = !empty($pid) && !empty($state['last_known_pid']) && $pid != $state['last_known_pid'];

        if (!empty($state['pending_validation'])) {
            if ($pidChanged) {
                _error_log(self::LOG_PREFIX . " restart validated: new process pid={$pid} previous pid={$state['last_known_pid']} {$label}");
            } else {
                _error_log(self::LOG_PREFIX . " restart validated: restream is running again {$label}");
            }
            rl_logEvent('destination_recovered', array(
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'live_restreams_id' => $live_restreams_id,
                'pid' => $pid,
            ));
            rl_logEvent('watchdog_restart_succeeded', array(
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'live_restreams_id' => $live_restreams_id,
                'pid' => $pid,
            ));
            $state['pending_validation'] = false;
            $state['last_success_start_at'] = $now;
        }

        // healthy_since must reflect THIS process's own uptime, not merely "the last time this
        // function saw anything running" - otherwise a restart that lands within the same
        // executeEveryMinute() minute as the previous (crashed) process's last healthy
        // observation inherits its now-stale healthy_since, and the restart-attempt counter can
        // reach restreamWatchdogHealthyResetSeconds and reset to zero while the NEW process has
        // itself only been running a few seconds - silently discarding the failure history that
        // restreamWatchdogMaxAttempts is supposed to be counting against.
        $pidStartedAt = !empty($localProcess['startedAt']) ? intval($localProcess['startedAt']) : null;
        $state['healthy_since'] = self::computeHealthySinceOnObservation(
            @$state['healthy_since'],
            @$state['last_known_pid'],
            $pid,
            $pidStartedAt,
            $now
        );
        if (!empty($pid)) {
            $state['last_known_pid'] = $pid;
        }

        $healthyResetSeconds = self::getConfigInt($objLive, 'restreamWatchdogHealthyResetSeconds', 600);
        if (!empty($state['restart_attempts']) && ($now - $state['healthy_since']) >= $healthyResetSeconds) {
            _error_log(self::LOG_PREFIX . " restream healthy for {$healthyResetSeconds}s, resetting restart attempt counter {$label}");
            $state['restart_attempts'] = [];
            $state['last_failure_reason'] = null;
        }

        $state['phase'] = self::computeRestreamPhase($state, true, false);
        self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
    }

    /**
     * Pure decision for handleHealthy()'s healthy_since bookkeeping - see the call site comment
     * for why this must be PID-identity based rather than "was the previous observation down".
     *
     * @param int|null $previousHealthySince state['healthy_since'] before this observation
     * @param int|null $previousKnownPid state['last_known_pid'] before this observation
     * @param int|null $currentPid the pid observed THIS cycle (null if unknown, e.g. remote-only)
     * @param int|null $currentPidStartedAt the observed process's own start time, when known
     * @param int $now
     * @return int the healthy_since value to persist
     */
    private static function computeHealthySinceOnObservation($previousHealthySince, $previousKnownPid, $currentPid, $currentPidStartedAt, $now)
    {
        $previousHealthySince = empty($previousHealthySince) ? null : intval($previousHealthySince);
        $previousKnownPid = empty($previousKnownPid) ? null : intval($previousKnownPid);
        $currentPid = empty($currentPid) ? null : intval($currentPid);

        $isFirstObservation = ($previousHealthySince === null);
        $pidChanged = ($currentPid !== null) && ($previousKnownPid !== null) && ($currentPid !== $previousKnownPid);

        if ($isFirstObservation || $pidChanged) {
            // Prefer the OS-reported process start time when available (accurate even across a
            // crash+restart landing within the same executeEveryMinute() cycle); fall back to
            // "now" only when the pid/start time could not be determined (e.g. a remote executor,
            // where liveness is known only via status->isActive with no local pid at all).
            return $currentPidStartedAt !== null ? $currentPidStartedAt : $now;
        }

        return $previousHealthySince;
    }

    private static function handleNotRunning($live, $restream, $status, $state, $objLive, $label)
    {
        $liveTransmitionHistory_id = intval($live['id']);
        $live_restreams_id = intval($restream['id']);
        $now = time();

        if (!empty($state['pending_validation'])) {
            _error_log(self::LOG_PREFIX . " restart attempt did not recover the restream (still not running) {$label}");
        }

        // Fetch the heavier log content only now, to classify the failure reason for diagnostics.
        $contentStatus = self::fetchLogStatus($liveTransmitionHistory_id, $live_restreams_id, null, 'logContent');
        $failureReason = self::classifyFailure($contentStatus);

        // Additional, richer taxonomy (dns_failure/tls_failure/timeout/resource_exhaustion/...)
        // purely for structured-log correlation with restreamer.json.php's own events; does not
        // replace $failureReason (kept as-is for state/back-compat, see classifyFailure() above).
        $failureClassification = classifyFfmpegFailure(
            !empty($contentStatus->content) ? $contentStatus->content : '',
            array('intentionalStop' => false)
        );

        // NOTE: $state here is a diagnostic-only snapshot read BEFORE this function acquired any
        // lock - it is only ever used below for the human-readable diagnostic snapshot log, never
        // saved. The failure observation (healthy_since=null/last_failure_at/last_failure_reason/
        // phase) and every attempt-window/max-attempts/cooldown decision are computed and
        // persisted EXCLUSIVELY inside attemptRestart(), merged onto a freshly re-read copy of the
        // state while the per-restream lock is held (see mergeFailureObservationIntoState()).
        // Saving a locally-mutated copy of this pre-lock $state here (as a previous revision did)
        // is exactly the race this refactor fixes: a concurrently running, lock-holding cycle for
        // the same restream could have already persisted newer restart_attempts/
        // last_restart_attempt_at/pending_validation, and an unprotected save from this function
        // would silently clobber them, defeating restreamWatchdogMaxAttempts and permitting
        // duplicate/excessive restarts.
        $diagnosticState = $state;
        $diagnosticState['healthy_since'] = null;
        $diagnosticState['last_failure_at'] = $now;
        $diagnosticState['last_failure_reason'] = $failureReason;

        _error_log(self::LOG_PREFIX . " restream disconnected from destination, reason=\"{$failureReason}\" {$label}");
        rl_logEvent('destination_unhealthy_detected', array(
            'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
            'live_restreams_id' => $live_restreams_id,
            'destinationHost' => parse_url(@$restream['stream_url'], PHP_URL_HOST),
            'failureReason' => $failureReason,
            'failureClassification' => $failureClassification,
        ));
        self::logDiagnosticSnapshot($live, $restream, $contentStatus, $diagnosticState, $label, $objLive);

        self::attemptRestart($live, $restream, $objLive, $label, $failureReason);
    }

    /**
     * Pure merge of a freshly-observed "destination is not running" failure onto a state that
     * MUST already have been re-read from disk AFTER the per-restream lock was acquired (see
     * attemptRestart()) - never call this with the state handleNotRunning() read before any lock
     * existed, since that copy can be stale relative to a concurrently running, lock-holding cycle
     * for the same restream: saving it would silently clobber that cycle's newer
     * restart_attempts/last_restart_attempt_at/pending_validation, defeating
     * restreamWatchdogMaxAttempts and permitting duplicate/excessive restarts. Only the
     * failure-observation fields are overwritten; every attempt-bookkeeping field already present
     * in $freshState (restart_attempts, last_restart_attempt_at, last_known_pid, ...) is left
     * untouched here.
     */
    private static function mergeFailureObservationIntoState(array $freshState, $now, $failureReason)
    {
        $freshState['healthy_since'] = null;
        $freshState['last_failure_at'] = $now;
        $freshState['last_failure_reason'] = $failureReason;
        $freshState['pending_validation'] = false;
        return $freshState;
    }

    private static function attemptRestart($live, $restream, $objLive, $label, $failureReason = '')
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

            // Merge THIS cycle's failure observation onto the state we just re-read (never onto
            // the pre-lock copy handleNotRunning() read before this lock existed - see
            // mergeFailureObservationIntoState()'s own docblock for the exact race this avoids).
            $state = self::mergeFailureObservationIntoState($state, $now, $failureReason);

            $windowSeconds = self::getConfigInt($objLive, 'restreamWatchdogWindowSeconds', 900);
            $attempts = self::pruneAttempts(@$state['restart_attempts'], $windowSeconds, $now);
            $state['restart_attempts'] = $attempts;

            $maxAttempts = self::getConfigInt($objLive, 'restreamWatchdogMaxAttempts', 3);
            if (count($attempts) >= $maxAttempts) {
                _error_log(self::LOG_PREFIX . " maximum restart attempts reached ({$maxAttempts} within {$windowSeconds}s) after acquiring lock, will not restart {$label}");
                rl_logEvent('restart_skipped_max_attempts', array(
                    'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                    'live_restreams_id' => $live_restreams_id,
                    'maxAttempts' => $maxAttempts,
                    'windowSeconds' => $windowSeconds,
                ));
                $state['phase'] = self::computeRestreamPhase($state, false, true);
                self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
                return;
            }

            $configuredCooldownSeconds = self::getConfigInt($objLive, 'restreamWatchdogCooldownSeconds', 120);
            $attemptNumber = count($attempts) + 1;
            $backoffSequence = self::getBackoffSequence($objLive);
            $jitterPercent = self::getConfigInt($objLive, 'restreamWatchdogBackoffJitterPercent', 20);
            $computedBackoffSeconds = computeRestreamBackoffDelaySeconds($attemptNumber, $backoffSequence, $jitterPercent);
            $cooldownSeconds = max($configuredCooldownSeconds, $computedBackoffSeconds);
            if (!empty($state['last_restart_attempt_at']) && ($now - $state['last_restart_attempt_at']) < $cooldownSeconds) {
                _error_log(self::LOG_PREFIX . " cooldown active after acquiring lock, another execution just restarted this restream {$label}");
                rl_logEvent('restart_skipped_cooldown', array(
                    'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                    'live_restreams_id' => $live_restreams_id,
                    'attemptNumber' => $attemptNumber,
                    'cooldownSeconds' => $cooldownSeconds,
                ));
                $state['phase'] = self::computeRestreamPhase($state, false, false);
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
                rl_logEvent('restart_recheck_running_again', array(
                    'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                    'live_restreams_id' => $live_restreams_id,
                ));
                $state['pending_validation'] = false;
                if (!empty($recheckProcess['pid'])) {
                    $state['last_known_pid'] = intval($recheckProcess['pid']);
                }
                // The recheck just confirmed the process IS running again - reflect that in the
                // diagnostic phase too, instead of leaving it at the "down" value computed by
                // mergeFailureObservationIntoState() a moment ago (that value described this
                // cycle's ORIGINAL observation, now superseded by this locked recheck).
                $state['phase'] = self::computeRestreamPhase($state, true, false);
                self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);
                return;
            }

            $state['last_restart_attempt_at'] = $now;
            $state['restart_attempts'][] = $now;
            $state['pending_validation'] = true;
            $state['phase'] = self::computeRestreamPhase($state, false, false);
            self::saveState($live_restreams_id, $liveTransmitionHistory_id, $state);

            $attemptCount = count($state['restart_attempts']);
            _error_log(self::LOG_PREFIX . " restart attempt #{$attemptCount} started, reusing Live::restream() {$label}");
            rl_logEvent('restart_attempt_started', array(
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'live_restreams_id' => $live_restreams_id,
                'attemptNumber' => $attemptCount,
                'destinationHost' => parse_url(@$restream['stream_url'], PHP_URL_HOST),
            ));
            rl_logEvent('watchdog_restart_started', array(
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'live_restreams_id' => $live_restreams_id,
                'attemptNumber' => $attemptCount,
                'reason' => $failureReason,
            ));

            // recoveryMode=true is the hard invariant: this call is only ever allowed to resume
            // the ONE existing destination that just failed (Live::restream()/
            // isValidRecoveryRestreamRequest() refuse a recovery call with an empty
            // live_restreams_id), and never creates a new destination/broadcast - see
            // Live::restream()'s own docblock for the full policy.
            $result = Live::restream($liveTransmitionHistory_id, $live_restreams_id, false, array(
                'recoveryMode' => true,
                'reason' => $failureReason,
            ));

            _error_log(self::LOG_PREFIX . " restart attempt #{$attemptCount} requested, result=" . json_encode(!empty($result)) . " {$label}");
            rl_logEvent('restart_attempt_result', array(
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'live_restreams_id' => $live_restreams_id,
                'attemptNumber' => $attemptCount,
                'result' => !empty($result),
            ));
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
                    $pid = intval($m[1]);
                    return ['pid' => $pid, 'line' => $line, 'startedAt' => self::getProcessStartUnixTime($pid)];
                }
            }
        }
        return false;
    }

    /**
     * Best-effort process start time (unix timestamp) via /proc, Linux-only - returns null on
     * any failure (non-Linux host, unreadable /proc, unexpected format) rather than guessing.
     * The two pure parsing/computation steps are split out below so they stay unit-testable
     * without real /proc access (this dev environment is Windows).
     */
    private static function getProcessStartUnixTime($pid)
    {
        if (stripos(PHP_OS, 'WIN') !== false || empty($pid)) {
            return null;
        }
        $statContent = @file_get_contents("/proc/{$pid}/stat");
        $uptimeContent = @file_get_contents('/proc/uptime');
        if ($statContent === false || $uptimeContent === false) {
            return null;
        }
        $startTicks = self::parseProcStatStartTicks($statContent);
        if ($startTicks === null) {
            return null;
        }
        $uptimeParts = explode(' ', trim($uptimeContent));
        if (empty($uptimeParts[0]) || !is_numeric($uptimeParts[0])) {
            return null;
        }
        return self::computeProcessStartUnixTimeFromTicks(time(), (float) $uptimeParts[0], $startTicks);
    }

    /**
     * Parses field 22 (starttime, in clock ticks since boot) of /proc/[pid]/stat. The process
     * name field (2nd, parenthesized) may itself contain spaces/parentheses, so fields are
     * counted from the LAST ')' rather than by naive whitespace-splitting from the start.
     */
    private static function parseProcStatStartTicks($statContent)
    {
        if (!is_string($statContent) || $statContent === '') {
            return null;
        }
        $lastParen = strrpos($statContent, ')');
        if ($lastParen === false) {
            return null;
        }
        $rest = trim(substr($statContent, $lastParen + 1));
        $fields = preg_split('/\s+/', $rest);
        // Field 3 in $fields (state) is stat's field #3; starttime is stat's field #22, i.e.
        // index 22 - 3 = 19 in this zero-based $fields array (which starts at stat's field #3).
        $index = 22 - 3;
        if (!isset($fields[$index]) || !is_numeric($fields[$index])) {
            return null;
        }
        return (int) $fields[$index];
    }

    /**
     * Pure conversion of /proc/[pid]/stat's starttime (clock ticks since boot) + /proc/uptime
     * (seconds since boot) into a unix timestamp. $ticksPerSecond is sysconf(_SC_CLK_TCK), 100 on
     * effectively every modern Linux distribution/kernel.
     */
    private static function computeProcessStartUnixTimeFromTicks($nowUnix, $uptimeSeconds, $startTicks, $ticksPerSecond = 100)
    {
        $ticksPerSecond = $ticksPerSecond > 0 ? $ticksPerSecond : 100;
        $bootUnix = $nowUnix - $uptimeSeconds;
        return (int) round($bootUnix + ($startTicks / $ticksPerSecond));
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

    /**
     * Pure computation of the diagnostic state['phase'] field - see the PHASE_* constants'
     * docblock. $maxAttemptsReached takes priority (a destination that has exhausted its restart
     * budget for the current window is reported as blocked/terminal even if $state still shows a
     * pending_validation from a much earlier cycle).
     */
    private static function computeRestreamPhase(array $state, $processRunning, $maxAttemptsReached)
    {
        if (!empty($maxAttemptsReached)) {
            return self::PHASE_BLOCKED;
        }
        if (!empty($state['pending_validation'])) {
            return self::PHASE_RESTARTING;
        }
        if (!empty($processRunning)) {
            return self::PHASE_HEALTHY;
        }
        return self::PHASE_DOWN;
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
            'phase' => null,
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

    private static function logDiagnosticSnapshot($live, $restream, $contentStatus, $state, $label, $objLive = null)
    {
        global $global;

        $liveTransmitionHistory_id = intval($live['id']);
        $live_restreams_id = intval($restream['id']);

        $snapshot = [
            'restream_id' => $live_restreams_id,
            'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
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

        // Heavier, more complete snapshot (DNS, TLS reachability, disk/inode/container limits),
        // rate limited independently per-destination so repeated failures for the same restream
        // don't spam it, but a different destination failing at the same time still gets its own.
        $snapshotKey = "watchdog_{$live_restreams_id}_{$liveTransmitionHistory_id}";
        $intervalSeconds = self::getConfigInt($objLive, 'restreamDiagnosticSnapshotIntervalSeconds', 300);
        if (function_exists('rl_shouldEmitSnapshot') && rl_shouldEmitSnapshot($snapshotKey, $intervalSeconds)) {
            $richSnapshot = rl_getDiagnosticSnapshot(array(
                'pid' => !empty($state['last_known_pid']) ? intval($state['last_known_pid']) : null,
                'destinationHost' => parse_url(@$restream['stream_url'], PHP_URL_HOST),
            ));
            rl_logEvent('diagnostic_snapshot', array_merge($richSnapshot, array(
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'live_restreams_id' => $live_restreams_id,
                'source' => 'watchdog',
            )));
        }
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

    private static function getConfigString($objLive, $field, $default)
    {
        if (empty($objLive) || !isset($objLive->{$field}) || $objLive->{$field} === '') {
            return $default;
        }
        return (string) $objLive->{$field};
    }

    /**
     * Parses the admin-configured "N,N,N..." backoff sequence (restreamWatchdogBackoffSequenceSeconds)
     * into an int array for computeRestreamBackoffDelaySeconds(). Falls back to the built-in
     * default sequence when the configured value is empty/malformed (never an empty array, which
     * would make the pure function's behavior undefined for the caller).
     */
    private static function getBackoffSequence($objLive)
    {
        $raw = self::getConfigString($objLive, 'restreamWatchdogBackoffSequenceSeconds', '2,5,10,20,30');
        $sequence = array_values(array_filter(array_map('intval', explode(',', $raw)), function ($v) {
            return $v > 0;
        }));
        return !empty($sequence) ? $sequence : [2, 5, 10, 20, 30];
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
