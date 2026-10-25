<?php
function getRestreamsRuning()
{
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "";
    $obj->process = array();

    // Command to list ffmpeg restream processes. Matching on the literal 'ffmpeg -re -rw_timeout'
    // substring is unreliable: the '-re' flag is commented out when the restream command is
    // built (see startRestream() in restreamer.json.php), so it never appears in the real
    // process arguments and this check always returned nothing. Match on the stable
    // live_restreams_id= marker that startRestream() always appends to the input URL instead.
    $command = "ps aux | grep 'ffmpeg' | grep 'live_restreams_id=' | grep -v grep";

    // Execute the command
    exec($command, $output, $return_var);
    $restreams_ids = array();
    $liveTransmitionHistory_ids = array();

    // Check if the command was successful
    if ($return_var === 0) {
        foreach ($output as $line) {
            preg_match('/-i http.*(live|cdn.ypt.me)\/([^\/]+)\/index.m3u8.*/i', $line, $matches);

            // Extract the RTMP destination domain from the command line
            preg_match('/rtmps?:\/\/([^\/:]+)/i', $line, $destMatches);
            $restreamDomain = $destMatches[1];

            preg_match('/live_restreams_id=([0-9]+)/i', $line, $matchesLt);
            preg_match('/liveTransmitionHistory_id=([0-9]+)/i', $line, $matchesLth);

            $live_restreams_id = intval($matchesLt[1]);
            $liveTransmitionHistory_id = intval($matchesLth[1]);
            // Add process info to the response without the command line
            $obj->process[] = array(
                'key' => $matches[2],
                'live_restreams_id' => $live_restreams_id,
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'restream_destination' => $restreamDomain,
                //'line' => $line,
            );
            $restreams_ids[] = $live_restreams_id;
            $liveTransmitionHistory_ids[] = $liveTransmitionHistory_id;
        }

        // Add total connections to the response
        $obj->restreams_ids = $restreams_ids;
        $obj->liveTransmitionHistory_ids = $liveTransmitionHistory_ids;
        $obj->error = false;
    } else {
        $obj->msg = "No processes found using 'ffmpeg' with a live_restreams_id= marker or an error occurred.";
    }
    return $obj;
}


/**
 * True when $commandLine is the FFmpeg restream process for exactly this
 * (live_restreams_id, liveTransmitionHistory_id) pair.
 *
 * Matches on the stable live_restreams_id=/liveTransmitionHistory_id= markers that
 * startRestream() always appends to the FFmpeg input URL, never on the source m3u8 URL itself:
 * the same live can be relayed from different source URLs (CDN, origin :8443, the Docker
 * "live" hostname), so a stop/restart request that resolves a different source URL than the
 * one FFmpeg was launched with must still find (and kill) the running process. Matching on the
 * URL caused "stop" to report "already stopped" while the old relay kept pushing to the
 * destination (TIC incident, 2026-10-25).
 *
 * The ids must match exactly (44 never matches 4 or 441) and only an actual ffmpeg executable
 * is accepted, so shell wrappers (nohup/sh -c), grep and pgrep lines are ignored.
 */
function isRestreamFfmpegCommandLine($commandLine, $live_restreams_id, $liveTransmitionHistory_id)
{
    $live_restreams_id = intval($live_restreams_id);
    $liveTransmitionHistory_id = intval($liveTransmitionHistory_id);
    if ($live_restreams_id <= 0 || $liveTransmitionHistory_id <= 0) {
        return false;
    }
    $commandLine = trim((string) $commandLine);
    if ($commandLine === '') {
        return false;
    }
    $parts = preg_split('/\s+/', $commandLine, 2);
    $executable = strtolower(basename(trim($parts[0], '"\'')));
    if ($executable !== 'ffmpeg' && strpos($executable, 'ffmpeg') !== 0) {
        return false;
    }
    return preg_match('/[?&]live_restreams_id=' . $live_restreams_id . '(?![0-9])/', $commandLine) === 1
        && preg_match('/[?&]liveTransmitionHistory_id=' . $liveTransmitionHistory_id . '(?![0-9])/', $commandLine) === 1;
}

/**
 * Returns the PIDs of every restream FFmpeg process for this pair, parsed from
 * "PID full command line" rows (the output format of `ps -eo pid=,args=` / `pgrep -af`).
 *
 * @param string[] $processLines
 * @return int[]
 */
function findRestreamProcessIds(array $processLines, $live_restreams_id, $liveTransmitionHistory_id)
{
    $pids = array();
    foreach ($processLines as $line) {
        if (!preg_match('/^\s*(\d+)\s+(.+)$/', (string) $line, $matches)) {
            continue;
        }
        if (isRestreamFfmpegCommandLine($matches[2], $live_restreams_id, $liveTransmitionHistory_id)) {
            $pids[] = intval($matches[1]);
        }
    }
    return array_values(array_unique($pids));
}

function isRestreamRuning($live_restreams_id, $liveTransmitionHistory_id){
    $obj = getRestreamsRuning();

    foreach ($obj->process as $key => $value) {
        if($live_restreams_id == $value['live_restreams_id'] AND $liveTransmitionHistory_id == $value['liveTransmitionHistory_id']){
            return $value;
        }
    }
    return false;
}
