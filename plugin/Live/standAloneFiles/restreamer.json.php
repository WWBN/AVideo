<?php

use Amp\Deferred;
use Amp\Loop;

// These must stay at the very top of the file: a top-level `const` (unlike a function
// declaration) is only defined once the interpreter's linear execution reaches this exact
// line, it is NOT hoisted. Loop::run()/runRestream()/startRestream() below all run before the
// script reaches its own end, so these constants must be defined before that point, not next
// to the startRestream() function definition further down the file (where they used to live
// and were never actually executed before startRestream() needed them, causing an "Undefined
// constant" fatal error on every restream attempt).
const STARTRESTREAM_MAX_TRIES = 6;
const STARTRESTREAM_MAX_SLEEP_SECONDS = 3;
const AUTOMATIC_RESTREAM_INITIAL_WARMUP_SECONDS = 8;
const AUTOMATIC_RESTREAM_PROGRESS_SAMPLE_SECONDS = 4;

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/restreamProfiles.php';
require_once __DIR__ . '/restreamLogging.php';
require_once __DIR__ . '/ffmpegCapabilities.php';

/**
 * ffmpegDetectCapabilities() runs several short subprocesses (-version/-buildconf/-muxers/
 * -protocols/-encoders/-h muxer=fifo); the FFmpeg build never changes between destinations or
 * retries within the same process, so cache the result per binary path instead of re-probing
 * once per destination.
 */
function getFfmpegCapabilitiesCached($ffmpegBinary)
{
    static $cache = array();
    if (!array_key_exists($ffmpegBinary, $cache)) {
        $cache[$ffmpegBinary] = ffmpegDetectCapabilities($ffmpegBinary);
        rl_logEvent('ffmpeg_capabilities', array(
            'ffmpegBinary' => $ffmpegBinary,
            'summary' => ffmpegCapabilitiesSummaryText($cache[$ffmpegBinary]),
            'fifoFullySupported' => ffmpegFifoMuxerFullySupported($cache[$ffmpegBinary]),
        ));
    }
    return $cache[$ffmpegBinary];
}

//pkill -9 -f "rw_timeout.*6196bac40f89f" //When -f is set, the full command line is used for pattern matching.
/**
 * This file intent to restream your lives, you can copy this file in any server with FFMPEG
 * Make sure you add the correct path to this file on the Live plugin restreamerURL parameter
 *
 * If you want to restream to Facebook, make sure your FFMPEG is compiled with openssl support
 * On Ubuntu you can install like this:
 * apt-get -y install build-essential libwebp-dev autoconf automake cmake libtool git checkinstall nasm yasm libass-dev libfreetype6-dev libsdl2-dev libtool libva-dev libvdpau-dev libvorbis-dev libxcb1-dev libxcb-shm0-dev libxcb-xfixes0-dev pkg-config texinfo wget zlib1g-dev libchromaprint-dev frei0r-plugins-dev ladspa-sdk libcaca-dev libcdio-paranoia-dev libcodec2-dev libfontconfig1-dev libfreetype6-dev libfribidi-dev libgme-dev libgsm1-dev libjack-dev libmodplug-dev libmp3lame-dev libopencore-amrnb-dev libopencore-amrwb-dev libopenjp2-7-dev libopenmpt-dev libopus-dev libpulse-dev librsvg2-dev librubberband-dev librtmp-dev libshine-dev libsmbclient-dev libsnappy-dev libsoxr-dev libspeex-dev libssh-dev libtesseract-dev libtheora-dev libtwolame-dev libv4l-dev libvo-amrwbenc-dev libvpx-dev libwavpack-dev libwebp-dev libx264-dev libx265-dev libxvidcore-dev libxml2-dev libzmq3-dev libzvbi-dev liblilv-dev libmysofa-dev libopenal-dev opencl-dev gnutls-dev libfdk-aac-dev
 * git clone https://git.ffmpeg.org/ffmpeg.git ffmpeg && cd ffmpeg
 * ./configure --enable-libwebp  --disable-shared --enable-static --enable-pthreads --enable-gpl --enable-nonfree --enable-libass --enable-libfdk-aac --enable-libfreetype --enable-libmp3lame --enable-libopus --enable-libvorbis --enable-libvpx --enable-libx264 --enable-filters --enable-openssl --enable-runtime-cpudetect --extra-version=patrickz
 * make
 * make install
 */
// optional you can change the log file location here
$logFileLocation = '/var/www/tmp/';

/**
 * $separateRestreams if it is set to true the script will use one FFMPEG command/process for each restream, otherwise will use only one for all streams
 * all in one FFMPEG command will save you CPU and other resources, but will make harder to find issues
 */
$separateRestreams = true;

// optional you can change the default FFMPEG
//$ffmpegBinary = '/usr/bin/ffmpeg';
$ffmpegBinary = '/usr/local/bin/ffmpeg';

/*
 * DO NOT EDIT AFTER THIS LINE
 */

if (!file_exists($ffmpegBinary)) {
    $ffmpegBinary = '/usr/bin/ffmpeg';
    if (!file_exists($ffmpegBinary)) {
        $ffmpegBinary = '/usr/local/bin/ffmpeg';
    }
}

$global_timeLimit = 300;

ini_set("memory_limit", "4G");
ini_set('default_socket_timeout', $global_timeLimit);
set_time_limit($global_timeLimit);
ini_set('max_execution_time', $global_timeLimit);
ignore_user_abort(true);

$logFileLocation = rtrim($logFileLocation, "/") . '/';

header('Content-Type: application/json');

$isATest = false;

$logFile = $logFileLocation . "ffmpeg_restreamer_{users_id}_" . date("Y-m-d-h-i-s") . ".log";

error_log("restreamer.json.php: Starting, about to load functionsStandAlone.php");
error_log("restreamer.json.php: REQUEST=" . json_encode($_REQUEST));
error_log("restreamer.json.php: php://input preview=" . substr(file_get_contents("php://input"), 0, 500));

// This endpoint is called by server-side automatic restream jobs, so those POSTs
// do not have a browser Origin/Referer header. Do not remove this auto-CSRF
// bypass as a suspected security issue: the request is authenticated immediately
// below with the signed restream token/responseToken before ffmpeg can start.
$global['skipAutoCSRFCheck'] = true;

require_once __DIR__ . "/../../../objects/functionsStandAlone.php";

error_log("restreamer.json.php: functionsStandAlone.php loaded successfully");

if (empty($streamerURL)) {
    error_log("Restreamer.json.php FATAL streamerURL not defined");
    die('streamerURL not defined');
}

require_once __DIR__ . "/../../../vendor/autoload.php";

if (!empty($_REQUEST['tokenForAction'])) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = '';
    $obj->time = time();
    $obj->modified = 0;
    $obj->secondsAgo = -1;
    $obj->isActive = false;
    $json = verifyTokenForAction($_REQUEST['tokenForAction']);
    //var_dump($json);exit;
    if (!empty($json) && isset($json->error) && empty($json->error)) {
        $keyword = 'restream_' . md5(basename($json->logFile));
        $obj->keyword = $keyword;
        $obj->error = false;
        error_log("Restreamer.json.php STATUS OK tokenForAction verified action='{$json->action}' live_restreams_id=" . (@$json->live_restreams_id ?? '?') . " liveTransmitionHistory_id=" . (@$json->liveTransmitionHistory_id ?? '?') . " logFile=" . (@$json->logFile ?? '?') . " full=" . json_encode($json));
        switch ($json->action) {
            case 'log':
            case 'logContent':

                $obj->completed = false;
                $obj->logName = str_replace($logFileLocation, '', $json->logFile);
                $obj->logName = preg_replace('/[^a-z0-9_.-]/i', '', $obj->logName);
                $logFile = $logFileLocation . $obj->logName;

                // A manual 'stop' always persists a *.completed marker on this same host (see the
                // 'stop' action below), regardless of whether FFmpeg ran locally or via a remote
                // executor. Check it first so a manually stopped/completed restream is never
                // reported as an unexpected failure just because a remote log endpoint is still
                // reachable and returns an unrelated/ambiguous status.
                if (!empty($obj->logName) && file_exists($logFile . '.completed')) {
                    $completedLogFile = $logFile . '.completed';
                    $obj->completed = true;
                    $obj->modified = @filemtime($completedLogFile);
                    $obj->secondsAgo = $obj->time - $obj->modified;
                    $obj->isActive = false;
                    $obj->remoteLog = false;
                    if ($json->action === 'logContent') {
                        $lines = file($completedLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        $obj->content = $lines === false ? '' : implode("\n", array_slice($lines, -300));
                    }
                    error_log("Restreamer.json.php STATUS OK logName={$obj->logName} restream was manually STOPPED/COMPLETED at " . date('Y-m-d H:i:s', $obj->modified) . " ({$obj->secondsAgo}s ago) - not an error");
                    echo json_encode($obj);
                    exit;
                }

                $resp = getFFMPEGRemoteLog($keyword, $json->restreamStandAloneFFMPEG);
                if (!empty($resp) && empty($resp->error)) {
                    $obj->modified = $resp->modified;
                    $obj->secondsAgo = $resp->secondsAgo;
                    $obj->isActive = $resp->isActive;
                    $obj->remoteLog = true;
                    $obj->resp = $resp;
                    error_log("Restreamer.json.php STATUS " . ($obj->isActive ? 'OK' : 'PROBLEM') . " logName={$obj->logName} ffmpeg is " . ($obj->isActive ? 'ACTIVE' : 'NOT ACTIVE') . " (remote executor) lastUpdate={$obj->secondsAgo}s ago");
                } else if (!empty($obj->logName)) {
                    if (file_exists($logFile)) {
                        $obj->modified = @filemtime($logFile);
                        $obj->secondsAgo = $obj->time - $obj->modified;
                        $obj->isActive = $obj->secondsAgo < 10;
                        $obj->remoteLog = false;
                        if ($json->action === 'logContent') {
                            // Return last 300 lines of the log file
                            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            if ($lines === false) {
                                $obj->content = '';
                            } else {
                                $obj->content = implode("\n", array_slice($lines, -300));
                            }
                        }
                        error_log("Restreamer.json.php STATUS " . ($obj->isActive ? 'OK' : 'PROBLEM') . " logName={$obj->logName} ffmpeg is " . ($obj->isActive ? 'ACTIVE' : 'NOT ACTIVE, likely CRASHED/STALLED') . " (local log) lastUpdate={$obj->secondsAgo}s ago");
                    } else {
                        error_log("Restreamer.json.php STATUS PROBLEM logName={$obj->logName} NO EVIDENCE FOUND - no .completed marker, no remote log, no local log file at {$logFile}");
                    }
                }

                echo json_encode($obj);
                exit;
                break;
            case 'stop':

                $resp = stopFFMPEGRemote($keyword, $json->restreamStandAloneFFMPEG);
                $obj->remoteResponse = $resp;
                $obj->logName = str_replace($logFileLocation, '', $json->logFile);
                $obj->logName = preg_replace('/[^a-z0-9_.-]/i', '', $obj->logName);
                $logFile = $logFileLocation . $obj->logName;
                if (!empty($resp) && empty($resp->error)) {
                    $obj->remoteKill = true;
                } else {
                    $obj->killIfIsRunning = killIfIsRunning($json);
                    $obj->remoteKill = false;
                }

                // Persist a reliable "manually stopped" marker regardless of whether FFmpeg was
                // killed locally or via the remote executor, so a subsequent 'log'/'logContent'
                // check on this host always reports completed=true instead of looking like an
                // unexpected crash. When FFmpeg ran entirely on a remote executor the local .log
                // file may never have existed, so touch() the marker directly in that case.
                if (!empty($obj->logName)) {
                    $completedLogFile = $logFile . '.completed';
                    if (file_exists($logFile)) {
                        rename($logFile, $completedLogFile);
                    } else if (!file_exists($completedLogFile)) {
                        @touch($completedLogFile);
                    }

                    // Structured event: an application-issued stop must never be classified as
                    // an unexpected failure downstream (watchdog/incident tooling). Recover the
                    // correlationId/destination info from the session sidecar file written at
                    // launch time, when available, so this event still correlates with
                    // 'command_prepared'/'launch_result' for the same destination.
                    $sessionInfo = @json_decode((string) @file_get_contents($logFile . '.session.json'));
                    rl_logEvent('destination_stopped', array(
                        'reason' => 'intentional_stop',
                        'classification' => 'killed_by_application',
                        'correlationId' => is_object($sessionInfo) ? ($sessionInfo->correlationId ?? null) : null,
                        'destinationHost' => is_object($sessionInfo) ? ($sessionInfo->destinationHost ?? null) : null,
                        'destinationProtocol' => is_object($sessionInfo) ? ($sessionInfo->destinationProtocol ?? null) : null,
                        'live_restreams_id' => $json->live_restreams_id ?? null,
                        'liveTransmitionHistory_id' => $json->liveTransmitionHistory_id ?? null,
                        'logName' => $obj->logName,
                    ));
                }

                error_log("Restreamer.json.php STATUS OK logName={$obj->logName} stop requested, killed=" . (!empty($obj->remoteKill) || !empty($obj->killIfIsRunning) ? 'yes' : 'no (already stopped)') . " remoteKill=" . ($obj->remoteKill ? 'yes' : 'no'));
                echo json_encode($obj);
                exit;
                break;
            case 'start':
                $robj = $json;
                $robj->type = 'start';
                break;
        }
    } else {
        if (empty($json)) {
            $error = '';
        } else {
            $error = $json->msg;
        }
        //var_dump(!empty($json), isset($json->error), empty($json->error), $json, $_REQUEST['tokenForAction']);exit;
        $obj->msg = 'ERROR on verifyTokenForAction: ' . $json->msg;
        die(json_encode($obj));
    }
}


error_log("Restreamer.json.php start {$streamerURL}");
$whichffmpeg = whichffmpeg();
if ($whichffmpeg !== $ffmpegBinary) {
    error_log("Restreamer.json.php WARNING you are using a different FFMPEG $whichffmpeg!==$ffmpegBinary");
}

$isCommandLine = php_sapi_name() === 'cli';

function _addLastSlash($word)
{
    return $word . (_hasLastSlash($word) ? "" : "/");
}

function _hasLastSlash($word)
{
    return substr($word, -1) === '/';
}

function sanitizeLogFileComponent($value, $default = '0')
{
    if (is_array($value) || is_object($value)) {
        return $default;
    }

    $value = preg_replace('/[^a-z0-9_.-]/i', '', (string) $value);

    if ($value === '') {
        return $default;
    }

    return $value;
}

function _getLiveKey($token)
{
    global $streamerURL, $isATest;

    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = '';
    $obj->newRestreamsDestination = '';
    $obj->fallbackRestreamsDestination = '';
    $obj->content = '';

    if ($isATest) {
        return $obj;
    }

    $getKeyURL = "{$streamerURL}plugin/Live/view/Live_restreams/getLiveKey.json.php?token={$token}";

    $obj->content = file_get_contents($getKeyURL);
    if (!empty($obj->content)) {
        $json = json_decode($obj->content);
        if (!empty($json) && $json->error === false) {
            $destinations = getAutomaticRestreamDestinationPair($json);
            // Optional, reversible, admin-only diagnostic override (Live plugin setting
            // "restreamForceProtocolForYouTubeTest") to isolate whether a destination's
            // disconnects are specific to RTMPS vs RTMP without touching encoding parameters.
            // No-op unless the admin explicitly set it; see getLiveKey.json.php.
            $destinations = applyRestreamProtocolTestOverride($destinations, $json->forceProtocol ?? '');
            if (!empty($destinations['primary'])) {
                $obj->newRestreamsDestination = $destinations['primary'];
                $obj->fallbackRestreamsDestination = $destinations['fallback'];
                $obj->live_url = isset($json->live_url) ? $json->live_url : '';
                error_log(
                    'Restreamer.json.php _getLiveKey found primary='
                    . redactDestinationForLog($obj->newRestreamsDestination)
                    . ' fallback=' . redactDestinationForLog($obj->fallbackRestreamsDestination)
                    . ' live_url=' . $obj->live_url
                );
                $obj->error = false;
            }
        } else if (!empty($json->rawData)) {
            $rawData = json_decode($json->rawData);
            $obj->msg = $rawData->message;
            error_log("Restreamer.json.php _getLiveKey URL=$getKeyURL rawData {$json->rawData}");
        } else if (!empty($json->msg)) {
            $obj->msg = $json->msg;
            error_log("Restreamer.json.php _getLiveKey URL=$getKeyURL msg " . json_encode($json->msg));
        } else {
            error_log("Restreamer.json.php _getLiveKey URL=$getKeyURL unknown " . json_encode($json));
        }
    }
    return $obj;
}

$errorMessages = array();

if (!$isCommandLine) { // not command line
    if (empty($robj)) {
        $request = file_get_contents("php://input");
        error_log("Restreamer.json.php php://input {$request}");
        if (!empty($request)) {
            $robj = json_decode($request);
            if (empty($robj)) {
                error_log("Restreamer.json.php ERROR could not decode php://input json_error=" . json_last_error_msg());
            } else {
                $robj->type = 'decoded from request';
            }
        }
    }
    if (!empty($robj)) {
        if (!empty($robj->test)) {
            $isATest = true;
            error_log("***Restreamer.json.php this is a test");
        }
        //var_dump($robj->restreamsToken);exit;
        if (!empty($robj->restreamsToken)) {
            $robj->restreamsToken = _object_to_array($robj->restreamsToken);
            $robj->restreamsDestinations = _object_to_array($robj->restreamsDestinations);
            $robj->live_url = array();
            error_log("***Restreamer.json.php using restreamsToken " . json_encode($robj->restreamsToken));
            //var_dump($robj->restreamsToken, $robj->restreamsDestinations);exit;
            if (empty($isATest)) {
                foreach ($robj->restreamsToken as $key => $token) {
                    $newRestreamsDestination = '';
                    $fallbackRestreamsDestination = '';

                    $liveKey = _getLiveKey($token);
                    if (isset($liveKey->live_url)) {
                        $robj->live_url[$key] = $liveKey->live_url;
                    }
                    if (empty($liveKey->error)) {
                        $newRestreamsDestination = $liveKey->newRestreamsDestination;
                        $fallbackRestreamsDestination = $liveKey->fallbackRestreamsDestination;
                    } else {
                        error_log("Restreamer.json.php ERROR try again in 3 seconds " . json_encode($liveKey));
                        sleep(3);
                        $liveKey = _getLiveKey($token);
                        if (isset($liveKey->live_url)) {
                            $robj->live_url[$key] = $liveKey->live_url;
                        }
                        if (empty($liveKey->error)) {
                            $newRestreamsDestination = $liveKey->newRestreamsDestination;
                            $fallbackRestreamsDestination = $liveKey->fallbackRestreamsDestination;
                        } else {
                            if (!is_string($liveKey->msg)) {
                                $msg = json_encode($liveKey->msg);
                            } else {
                                $msg = ($liveKey->msg);
                            }
                            $errorMessages[] = nl2br($msg);
                            if (!empty($liveKey->content)) {
                                $jsonInfo = json_decode($liveKey->content);
                                if (!empty($jsonInfo)) {
                                    if (!empty($jsonInfo->msg)) {
                                        if (!is_string($jsonInfo->msg)) {
                                            $msg = json_encode($jsonInfo->msg);
                                        } else {
                                            $msg = ($jsonInfo->msg);
                                        }
                                        $errorMessages[] = nl2br($msg);
                                    }
                                }
                            }
                        }
                    }

                    if (empty($newRestreamsDestination)) {
                        error_log("Restreamer.json.php ERROR " . json_encode($liveKey));
                        unset($robj->restreamsDestinations[$key]);
                    } else {
                        $robj->restreamsDestinations[$key] = $newRestreamsDestination;
                        if (!empty($fallbackRestreamsDestination)) {
                            $robj->restreamsFallbackDestinations[$key] = $fallbackRestreamsDestination;
                        }
                    }
                }
            }
        }
    }
}
//var_dump($isATest, $robj);exit;
if (empty($robj)) {
    $robj = new stdClass();
    $robj->type = 'empty';
    $robj->token = '';

    // Check if running from the command line
    if (php_sapi_name() === 'cli') {
        $robj->m3u8 = @$argv[1];
        $robj->restreamsDestinations = [@$argv[2]];
        $robj->users_id = 'commandline';
        $robj->logFile = @$argv[3];
    } else {
        // Fallback to URL parameters
        $robj->m3u8 = isset($_REQUEST['m3u8']) ? $_REQUEST['m3u8'] : '';
        $robj->restreamsDestinations = isset($_REQUEST['destinations']) ? explode(',', $_REQUEST['destinations']) : [];
        $robj->users_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 'web';
        $robj->logFile = isset($_REQUEST['logFile']) ? $_REQUEST['logFile'] : '';
    }

    $robj->responseToken = '';
}

error_log("Restreamer.json.php request summary " . json_encode(getRestreamRequestSummary($robj)));

$robj->users_id = sanitizeLogFileComponent(@$robj->users_id, 'web');
$robj->liveTransmitionHistory_id = sanitizeLogFileComponent(@$robj->liveTransmitionHistory_id, '0');

$obj = new stdClass();
$obj->error = true;
$obj->msg = implode(PHP_EOL, $errorMessages);
$obj->streamerURL = $streamerURL;
$obj->type = $robj->type;
$obj->token = $robj->token;
$obj->pid = [];
$obj->logFile = str_replace('{users_id}', $robj->users_id, $logFile);

if (empty($robj->restreamsDestinations) || !is_array($robj->restreamsDestinations)) {
    $errorMessages[] = "There are no restreams Destinations";
    $obj->msg = implode('<br>', $errorMessages);
    error_log("Restreamer.json.php ERROR {$obj->msg} summary=" . json_encode(getRestreamRequestSummary($robj)));
    die(json_encode($obj));
}
error_log("Restreamer.json.php Found " . count($robj->restreamsDestinations) . " destinations: " . json_encode($robj->restreamsDestinations));

if (!$isCommandLine) {
    // check the token
    if (empty($obj->token)) {
        $errorMessages[] = "Token is empty";
        $obj->msg = implode(PHP_EOL, $errorMessages);
        error_log("Restreamer.json.php ERROR {$obj->msg} summary=" . json_encode(getRestreamRequestSummary($robj)));
        die(json_encode($obj));
    }

    $verifyTokenURL = "{$obj->streamerURL}plugin/Live/verifyToken.json.php?token={$obj->token}";

    error_log("Restreamer.json.php verifying token {$verifyTokenURL}");

    $arrContextOptions = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ];

    $content = file_get_contents($verifyTokenURL, false, stream_context_create($arrContextOptions));

    //error_log("Restreamer.json.php verification respond content {$content}");
    $json = json_decode($content);

    if (empty($json)) {
        $errorMessages[] = "Could not verify token";
        $obj->msg = implode(PHP_EOL, $errorMessages);
        error_log("Restreamer.json.php empty json ERROR {$obj->msg} ({$verifyTokenURL}) content=" . json_encode($content));
        die(json_encode($obj));
    } elseif (!empty($json->error)) {
        $errorMessages[] = "Token is invalid";
        $obj->msg = implode(PHP_EOL, $errorMessages);
        error_log("Restreamer.json.php json error ERROR {$obj->msg} ({$verifyTokenURL}) " . json_encode($json));
        die(json_encode($obj));
    }
}
$robj->logFile = $obj->logFile;
//var_dump($robj);exit;
if (function_exists('_mysql_close')) {
    _mysql_close();
}
session_write_close();
error_log("Restreamer.json.php starting async ");
$runRestreamResult = [];
Loop::run(function () use (&$runRestreamResult) {
    global $robj;
    runRestream($robj)->onResolve(function (Throwable $error = null, $result = null) use (&$runRestreamResult) {
        if ($error) {
            error_log("Restreamer.json.php runRestream: asyncOperation1 fail -> " . $error->getMessage());
        } else {
            error_log("Restreamer.json.php runRestream: asyncOperation1 result -> " . json_encode($result));
            $runRestreamResult = $result;
        }
    });
});
error_log("Restreamer.json.php finish async ");
// runRestream() returns false per destination that failed to start (lock held, source not
// ready, exception, etc.) — surface that instead of always reporting an empty pid array.
$obj->pid = $runRestreamResult;
$obj->error = empty($runRestreamResult) || in_array(false, $runRestreamResult, true);
if ($obj->error) {
    $errorMessages[] = "FFmpeg did not start for one or more destinations, check the server error log for 'startRestream'";
    $obj->msg = implode('<br>', $errorMessages);
}
die(json_encode($obj));

function runRestream($robj)
{
    $m3u8 = $robj->m3u8;
    error_log("runRestream " . json_encode($robj));
    error_log("runRestream summary " . json_encode(getRestreamRequestSummary($robj)));
    $restreamsDestinations = $robj->restreamsDestinations;
    $logFile = $robj->logFile;
    $users_id = $robj->users_id;
    $responseToken = $robj->responseToken;
    global $separateRestreams;
    killIfIsRunning($robj);
    $pid = array();
    $deferred = new Deferred();
    if (empty($separateRestreams)) {
        error_log("Restreamer.json.php runRestream all in one command ");
        try {
            $robj->correlationId = rl_newCorrelationId();
            $pid[] = startRestream($m3u8, $restreamsDestinations, $logFile, $robj);
        } catch (\Throwable $th) {
            error_log("Restreamer.json.php runRestream FATAL while starting all destinations in one command: " . $th->getMessage());
        }
    } else {
        error_log("Restreamer.json.php runRestream separateRestreams " . count($restreamsDestinations));
        foreach ($restreamsDestinations as $key => $value) {
            sleep(5);
            $robj->live_restreams_id = $key;
            $robj->correlationId = rl_newCorrelationId();
            $logKey = sanitizeLogFileComponent($key, '0');
            $historyId = sanitizeLogFileComponent($robj->liveTransmitionHistory_id, '0');
            // clearCommandURL() now requires a full scheme+host URL and would reject a bare
            // hostname (always returning ''); sanitizeLogFileComponent() already strips it down
            // to safe filename characters, so pass the extracted host straight to it.
            $host = sanitizeLogFileComponent(parse_url($value, PHP_URL_HOST), 'unknown');
            error_log("Restreamer.json.php runRestream starting destination key={$key} historyId={$historyId} host={$host}");
            // A fatal error/exception starting ONE destination must never prevent the remaining
            // destinations from being attempted (see the "Undefined constant" crash that took
            // down every destination in the same run before this try/catch existed).
            try {
                $fallbackDestination = isset($robj->restreamsFallbackDestinations[$key])
                    ? $robj->restreamsFallbackDestinations[$key]
                    : '';
                $pid[] = startRestream(
                    $m3u8,
                    [$value],
                    str_replace(".log", "_{$logKey}_{$historyId}_{$host}.log", $logFile),
                    $robj,
                    1,
                    null,
                    $fallbackDestination
                );
            } catch (\Throwable $th) {
                error_log("Restreamer.json.php runRestream FATAL while starting destination key={$key} host={$host}: " . $th->getMessage());
                $pid[] = false;
            }
        }
    }
    $deferred->resolve($pid);
    return $deferred->promise();
}

function notifyStreamer($robj)
{
    if (!empty($robj->doNotNotifyStreamer)) {
        return false;
    }
    global $streamerURL;
    $restreamerURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $m3u8 = $robj->m3u8;
    error_log("notifyStreamer " . json_encode($robj));
    $restreamsDestinations = $robj->restreamsDestinations;
    $logFile = $robj->logFile;
    $users_id = $robj->users_id;
    $responseToken = $robj->responseToken;

    $data_string = json_encode(
        array(
            'm3u8' => $m3u8,
            'restreamsDestinations' => $restreamsDestinations,
            'logFile' => $logFile,
            'users_id' => $users_id,
            'responseToken' => $responseToken,
            'restreamerURL' => $restreamerURL,
            'live_restreams_id' => $robj->live_restreams_id,
            'live_url' => $robj->live_url,
        )
    );
    error_log("Restreamer.json.php notifyStreamer {$data_string}");

    $url = "{$streamerURL}plugin/Live/view/Live_restreams_logs/add.json.php";
    return postToURL($url, $data_string);
}

function verifyTokenForAction($token)
{
    global $streamerURL;
    $data_string = json_encode(array('token' => $token));
    error_log("Restreamer.json.php verifyTokenForAction {$data_string}");

    $url = "{$streamerURL}plugin/Live/view/Live_restreams/verifyTokenForAction.json.php";
    //var_dump($url);//exit;
    return postToURL($url, $data_string);
}

function postToURL($url, $data_string, $timeLimit = 10)
{
    global $global_timeLimit;
    try {
        set_time_limit($timeLimit);
        //open connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeLimit);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeLimit / 2); //timeout in seconds
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
            ]
        );
        //$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $output = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        set_time_limit($global_timeLimit);
        $httpCode = @$info['http_code'];
        $isHttpOk = empty($curlErrno) && $httpCode >= 200 && $httpCode < 300;
        error_log("Restreamer.json.php postToURL " . ($isHttpOk ? 'OK' : 'FAILED') . " " . json_encode(array('url' => $url, 'http_code' => $httpCode, 'curl_errno' => $curlErrno, 'curl_error' => $curlError, 'outputPreview' => substr((string) $output, 0, 500))));
        //var_dump($url, $data_string, $output);//exit;
        $json = json_decode($output);
        if ($output === false || !empty($curlErrno)) {
            error_log("Restreamer.json.php postToURL ERROR curl failed, request never reached {$url} " . json_encode(array('curl_errno' => $curlErrno, 'curl_error' => $curlError)));
            return false;
        }
        if (empty($json) && !empty($output)) {
            error_log("Restreamer.json.php postToURL ERROR invalid json response from {$url} json_error=" . json_last_error_msg());
        }
        return $json;
    } catch (Exception $exc) {
        error_log("Restreamer.json.php postToURL ERROR " . $exc->getTraceAsString());
    }
    set_time_limit($global_timeLimit);
    return false;
}

function _isURL200($url, $forceRecheck = false)
{
    global $global_timeLimit;
    set_time_limit(5);
    error_log("_isURL200 checking URL {$url}");

    // Create a stream context that ignores SSL certificate verification
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    ));

    // Use the created context with file_get_contents
    $headers = @get_headers($url, 1, $context);
    if (!is_array($headers)) {
        $headers = [$headers];
    }

    $result = false;
    foreach ($headers as $key => $value) {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        if (!is_string($value)) {
            $value = json_encode($value);
        }

        if (
            strpos($value, '200') !== false ||
            strpos($value, '302') !== false ||
            strpos($value, '304') !== false
        ) {
            $result = true;
            break;
        } else {
            error_log("_isURL200 header {$key}: " . $value);
        }
    }
    error_log("_isURL200 result " . json_encode(array('url' => $url, 'result' => $result, 'headers' => $headers)));
    set_time_limit($global_timeLimit);

    return $result;
}

function _make_path($path)
{
    $created = false;
    if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
        $path = pathinfo($path, PATHINFO_DIRNAME);
    }
    if (!is_dir($path)) {
        $created = mkdir($path, 0755, true);
        if (!$created) {
            error_log('_make_path: could not create the dir ' . json_encode($path));
        }
    } else {
        $created = true;
    }
    return $created;
}

// Manual "start" (via getAction.json.php) is a synchronous HTTP request end-to-end, so this
// retry loop directly blocks the caller's browser/AJAX call for as long as it runs. Keep the
// ceiling and per-try backoff small so a source that is not ready fails fast (a few seconds)
// instead of hanging the request for minutes (the previous 20-tries/growing-sleep(1..19)
// ceiling could block for 190+ seconds, well past the default_socket_timeout=60s used by
// getAction.json.php's url_get_contents() call, causing a blank/"pending forever" response and
// tying up an Apache worker the whole time). See the top of the file for the constants used here
// (STARTRESTREAM_MAX_TRIES, STARTRESTREAM_MAX_SLEEP_SECONDS, AUTOMATIC_RESTREAM_INITIAL_WARMUP_SECONDS,
// AUTOMATIC_RESTREAM_PROGRESS_SAMPLE_SECONDS).

function startRestream($m3u8, $restreamsDestinations, $logFile, $robj, $tries = 1, $startTime = null, $fallbackDestination = '')
{
    global $json;
    global $ffmpegBinary, $isATest;

    if ($startTime === null) {
        $startTime = microtime(true);
    }
    $originalM3u8 = $m3u8;

    error_log("Restreamer.json.php startRestream enter " . json_encode(array('tries' => $tries, 'm3u8' => $m3u8, 'destinationsCount' => is_array($restreamsDestinations) ? count($restreamsDestinations) : 0, 'logFile' => $logFile, 'summary' => getRestreamRequestSummary($robj))));

    // 🔒 Lock file to avoid duplicate restreams
    $lockFilePath = "/tmp/restream_lock_{$robj->live_restreams_id}_{$robj->liveTransmitionHistory_id}.lock";
    $lockFileHandle = fopen($lockFilePath, 'c');
    if (!$lockFileHandle || !flock($lockFileHandle, LOCK_EX | LOCK_NB)) {
        error_log("Restreamer.json.php startRestream LOCKED: another instance is already running for this stream. {$lockFilePath}");
        return false;
    }

    // On Docker, rewrite the public domain to the internal Compose service hostname ("live") so
    // this request stays inside the docker network instead of hairpinning out to the public
    // domain (which often fails/hangs to reach from inside a container). Read SERVER_NAME from
    // docker_vars.json (written by deploy/apache/docker-entrypoint) instead of a fixed domain, so
    // this works for any Docker install, not just this one. On bare metal the file doesn't exist,
    // so $m3u8 is left untouched (matches the already-working bare-metal behavior).
    $dockerVarsFile = '/var/www/docker_vars.json';
    if (file_exists($dockerVarsFile)) {
        $dockerVars = json_decode(file_get_contents($dockerVarsFile));
        if (!empty($dockerVars->SERVER_NAME)) {
            $m3u8 = str_replace($dockerVars->SERVER_NAME, 'live', $m3u8);
        }
    }
    if (empty($restreamsDestinations)) {
        error_log("Restreamer.json.php startRestream ERROR empty restreamsDestinations");
        return false;
    }

    // Check if restream is already running
    if (function_exists('isRestreamRuning')) {
        $restream = isRestreamRuning($robj->live_restreams_id, $robj->liveTransmitionHistory_id);
        if (!empty($restream)) {
            error_log("Restreamer.json.php startRestream ERROR it is already running " . json_encode($restream));
            return false;
        }
    }
    error_log("Restreamer.json.php startRestream success " . json_encode(array($robj->live_restreams_id, $robj->liveTransmitionHistory_id)));

    $m3u8 = _addQueryStringParameter($m3u8, 'live_restreams_id', $robj->live_restreams_id);
    $m3u8 = _addQueryStringParameter($m3u8, 'liveTransmitionHistory_id', $robj->liveTransmitionHistory_id);

    $m3u8 = clearCommandURL($m3u8);
    if (empty($m3u8)) {
        error_log('Restreamer.json.php startRestream ERROR invalid source URL');
        flock($lockFileHandle, LOCK_UN);
        fclose($lockFileHandle);
        @unlink($lockFilePath);
        return false;
    }

    if ($tries === 1) {
        sleep(3);
    }
    if (!$isATest && function_exists('_isURL200') && !_isURL200($m3u8, true)) {
        if ($tries > STARTRESTREAM_MAX_TRIES) {
            $elapsed = round(microtime(true) - $startTime, 1);
            error_log("Restreamer.json.php startRestream GAVE UP after {$tries} tries ({$elapsed}s elapsed): the source m3u8 never became ready, check whether the live source is actually broadcasting. m3u8={$m3u8} summary=" . json_encode(getRestreamRequestSummary($robj)));
            return false;
        }
        if ($tries === 1) {
            error_log("Restreamer.json.php startRestream " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
        }
        error_log("Restreamer.json.php startRestream URL ($m3u8) is NOT ready. trying again ({$tries}/" . STARTRESTREAM_MAX_TRIES . ")");

        // 🔓 Release lock
        flock($lockFileHandle, LOCK_UN);
        fclose($lockFileHandle);
        @unlink($lockFilePath);
        sleep(min($tries, STARTRESTREAM_MAX_SLEEP_SECONDS));
        return startRestream($originalM3u8, $restreamsDestinations, $logFile, $robj, $tries + 1, $startTime, $fallbackDestination);
    }

    error_log("Restreamer.json.php startRestream _isURL200 tries= " . json_encode($tries));

    // Check FFmpeg version
    $ffmpegVersionOutput = [];
    exec("$ffmpegBinary -version", $ffmpegVersionOutput);
    preg_match('/ffmpeg version ([0-9]+)\./', $ffmpegVersionOutput[0] ?? '', $matches);
    $ffmpegMajorVersion = isset($matches[1]) ? (int)$matches[1] : 0;

    // Structured, rate-limited version/build diagnostics: captured once per marker window (not
    // once per destination) since the FFmpeg/OpenSSL build is the same for every destination on
    // this host and does not change between restream attempts.
    if (rl_shouldEmitSnapshot('ffmpeg_build_info', 3600)) {
        $opensslVersionOutput = rl_safeExec('openssl version');
        rl_logEvent('ffmpeg_build_info', array(
            'ffmpegVersionLine' => $ffmpegVersionOutput[0] ?? 'unavailable',
            'ffmpegMajorVersion' => $ffmpegMajorVersion,
            'ffmpegHasOpenssl' => stripos(implode(' ', $ffmpegVersionOutput), 'openssl') !== false,
            'ffmpegHasGnutls' => stripos(implode(' ', $ffmpegVersionOutput), 'gnutls') !== false,
            'opensslVersion' => $opensslVersionOutput !== null ? $opensslVersionOutput : 'unavailable',
        ));
    }

    // Disable reconnect_on_network_error for FFmpeg versions below 6
    $disableReconnectOnNetworkError = ($ffmpegMajorVersion < 6);
    $userAgent = 'AVideoRestreamer';

    // Restream FIFO passthrough resiliency layer (opt-in, see restreamProfiles.php's own
    // module docblock for the full eligibility policy). Config always comes from the sanitized
    // verifyToken.json.php response (global $json, already fetched above), re-sanitized here
    // defensively rather than trusted as-is.
    $restreamFifoConfig = sanitizeRestreamFifoConfig(isset($json->restreamFifo) ? (array) $json->restreamFifo : array());
    // Capability detection only reflects the FFmpeg build on THIS (local) host. When this
    // request is configured to dispatch to a separate remote FFMPEG executor host, the actual
    // process may run on a different, unprobed build - so FIFO is skipped there and the legacy
    // output path is used unconditionally. Documented residual limitation, not a bug: extending
    // capability detection to a remote executor host is future work.
    $restreamFifoRemoteExecConfigured = !empty($json->restreamStandAloneFFMPEG);
    $ffmpegCapabilities = ($restreamFifoConfig['enabled'] && !$restreamFifoRemoteExecConfigured)
        ? getFfmpegCapabilitiesCached($ffmpegBinary)
        : null;

    $FFMPEGcommand = "{$ffmpegBinary} -hide_banner -y -v info "
        // . "-re "
        . "-rw_timeout 120000000 "                 // 120s em microssegundos
        . "-timeout 120000000 "                    // pode ser ignorado por alguns protocolos
        . "-reconnect 1 -reconnect_streamed 1 "
        . "-reconnect_at_eof 1 -reconnect_delay_max 10 "
        . ($disableReconnectOnNetworkError ? "" : "-reconnect_on_network_error 1 ")
        . "-http_persistent 1 -multiple_requests 1 " // mantém conexões HTTP ativas entre segmentos
        . "-probesize 100M -analyzeduration 300M "
        . "-thread_queue_size 8192 "
        . "-fflags +genpts ";

    if (filter_var($m3u8, FILTER_VALIDATE_URL)) {
        $FFMPEGcommand .= " -user_agent \"{$userAgent}\"";
    }

    $FFMPEGcommand .= " -i \"{$m3u8}\" ";

    // Resolve output resolution from the request (set by Live plugin config)
    $restreamResolution = $robj->restream_resolution ?? '720';
    $isPassthrough = ($restreamResolution === 'passthrough' || $restreamResolution === 0 || $restreamResolution === '0');

    if ($isPassthrough) {
        error_log("Restreamer.json.php resolution=passthrough (stream copy, no transcoding)");
        // ===== PASSTHROUGH — no re-encoding, lowest CPU usage =====
        $FFMPEGComplement =
            " -max_muxing_queue_size 8192 "
            . " -c:v copy -c:a copy "
            . "{outputTail}";
    } else {
        $restreamResolution = (int) $restreamResolution;
        // Video encoding is resolved per destination below. YouTube, Facebook and
        // Twitch do not recommend the same bitrate/profile for every resolution.
        $FFMPEGComplement =
            " -vsync cfr "
            . " -max_muxing_queue_size 8192 "
            . " {audioConfig}"
            . " {videoConfig}"
            . " {outputTail}";
    }

    $primaryDestinationForFallback = '';
    if (count($restreamsDestinations) > 1) {
        $command = $FFMPEGcommand;
        foreach ($restreamsDestinations as $value) {
            $value = clearCommandURL($value);
            if (empty($value)) {
                error_log('Restreamer.json.php startRestream skipping invalid destination URL');
                continue;
            }
            $audioConfig = $isPassthrough ? '' : getAudioConfiguration($value);
            $videoConfig = $isPassthrough ? '' : getRestreamVideoConfiguration($value, $restreamResolution);
            if (!$isPassthrough) {
                $profile = getRestreamVideoProfile($value, $restreamResolution);
                error_log('Restreamer.json.php destination profile ' . json_encode($profile));
            }
            $tcurl = buildRtmpTcurl($value);
            $tls_verify = getRestreamTlsOptions($value, $tcurl);
            if (shouldUseRestreamFifoForDestination($value, $restreamFifoConfig, $ffmpegCapabilities)) {
                $destInfoForFifoLog = getDestinationHostPort($value);
                $outputTail = getRestreamFifoOutputTail($value, $tcurl, $restreamFifoConfig);
                rl_logEvent('fifo_output_enabled', array('destinationHost' => $destInfoForFifoLog['host']));
            } else {
                $outputTail = getRestreamOutputTail($value, $tls_verify);
            }

            $command .= str_replace(
                array('{audioConfig}', '{videoConfig}', '{outputTail}'),
                array($audioConfig, $videoConfig, $outputTail),
                $FFMPEGComplement
            );
        }
    } else {
        $dst = clearCommandURL($restreamsDestinations[0]);
        if (empty($dst)) {
            error_log('Restreamer.json.php startRestream ERROR invalid destination URL');
            flock($lockFileHandle, LOCK_UN);
            fclose($lockFileHandle);
            @unlink($lockFilePath);
            return false;
        }
        $primaryDestinationForFallback = $dst;

        $audioConfig = $isPassthrough ? '' : getAudioConfiguration($dst);
        $videoConfig = $isPassthrough ? '' : getRestreamVideoConfiguration($dst, $restreamResolution);
        if (!$isPassthrough) {
            $profile = getRestreamVideoProfile($dst, $restreamResolution);
            error_log('Restreamer.json.php destination profile ' . json_encode($profile));
        }

        $tcurl = buildRtmpTcurl($dst);
        $tls_verify = getRestreamTlsOptions($dst, $tcurl);
        if (shouldUseRestreamFifoForDestination($dst, $restreamFifoConfig, $ffmpegCapabilities)) {
            $destInfoForFifoLog = getDestinationHostPort($dst);
            $outputTail = getRestreamFifoOutputTail($dst, $tcurl, $restreamFifoConfig);
            rl_logEvent('fifo_output_enabled', array('destinationHost' => $destInfoForFifoLog['host']));
        } else {
            $outputTail = getRestreamOutputTail($dst, $tls_verify);
        }

        $command = $FFMPEGcommand;
        $command .= str_replace(
            array('{audioConfig}', '{videoConfig}', '{outputTail}'),
            array($audioConfig, $videoConfig, $outputTail),
            $FFMPEGComplement
        );
    }

    // Accepts either the legacy "-f flv" output or the FIFO-wrapped "-f fifo -fifo_format flv"
    // output (see getRestreamFifoOutputTail()) - a command matching neither shape never reached
    // a valid output tail builder and must not be launched.
    $isFifoCommand = preg_match('/-f fifo\b/i', (string) $command) && preg_match('/-fifo_format flv\b/i', (string) $command);
    if (empty($command) || (!preg_match("/-f flv/i", $command) && !$isFifoCommand)) {
        error_log("Restreamer.json.php startRestream ERROR command is empty ");
    } else {
        error_log("Restreamer.json.php startRestream startRestream, check the file ($logFile) for the log");
        _make_path($logFile);

        // Never persist a raw credential (stream key/token) to disk: the log file is servable
        // via the 'logContent' action, so redact before writing, not after.
        $redactedCommand = redactSecretsInText($command);
        file_put_contents($logFile, $redactedCommand . PHP_EOL);

        $destinationForDiagnostics = !empty($primaryDestinationForFallback)
            ? $primaryDestinationForFallback
            : (is_array($restreamsDestinations) && !empty($restreamsDestinations) ? array_values($restreamsDestinations)[0] : '');
        $destInfo = getDestinationHostPort($destinationForDiagnostics);
        $correlationId = !empty($robj->correlationId) ? $robj->correlationId : rl_newCorrelationId();

        // Persist a small sidecar with the correlation id + destination host/port so a later,
        // separate HTTP request ('stop', or LiveRestreamWatchdog's next poll cycle) can recover
        // it and keep every lifecycle event for this destination correlated together.
        @file_put_contents($logFile . '.session.json', json_encode(array(
            'correlationId' => $correlationId,
            'destinationHost' => $destInfo['host'],
            'destinationProtocol' => $destInfo['protocol'],
            'live_restreams_id' => $robj->live_restreams_id ?? null,
            'liveTransmitionHistory_id' => $robj->liveTransmitionHistory_id ?? null,
        )));

        rl_logEvent('command_prepared', array(
            'correlationId' => $correlationId,
            'live_restreams_id' => $robj->live_restreams_id ?? null,
            'liveTransmitionHistory_id' => $robj->liveTransmitionHistory_id ?? null,
            'destinationHost' => $destInfo['host'],
            'destinationPort' => $destInfo['port'],
            'destinationProtocol' => $destInfo['protocol'],
            'destinationCount' => is_array($restreamsDestinations) ? count($restreamsDestinations) : 1,
            'resolution' => $restreamResolution,
            'commandRedacted' => $redactedCommand,
        ));

        if (!empty($destInfo['host'])) {
            $resolvedIp = @gethostbyname($destInfo['host']);
            rl_logEvent('dns_lookup', array(
                'correlationId' => $correlationId,
                'destinationHost' => $destInfo['host'],
                'resolvedIp' => ($resolvedIp !== $destInfo['host']) ? $resolvedIp : null,
                'resolutionFailed' => ($resolvedIp === $destInfo['host']),
            ));
        }

        $launch = null;
        if (empty($isATest)) {
            $keyword = 'restream_' . md5(basename($logFile));
            $robj->keyword = $keyword;
            $safeLogFile = escapeshellarg($logFile);
            $launch = new stdClass();
            $launch->keyword = $keyword;
            $launch->accepted = null;
            $launch->remote = false;
            $launch->restreamStandAloneFFMPEG = false;
            $launch->pid = 0;
            $launch->logFile = $logFile;
            rl_logEvent('connection_starting', array(
                'correlationId' => $correlationId,
                'destinationHost' => $destInfo['host'],
                'destinationProtocol' => $destInfo['protocol'],
                'keyword' => $keyword,
            ));
            // use remote ffmpeg here
            if (function_exists('execFFMPEGAsyncOrRemote')) {
                $restreamStandAloneFFMPEG = isset($json->restreamStandAloneFFMPEG) ? $json->restreamStandAloneFFMPEG : false;
                $launch->restreamStandAloneFFMPEG = $restreamStandAloneFFMPEG;
                if (function_exists('buildFFMPEGRemoteURL')) {
                    $launch->remote = buildFFMPEGRemoteURL(
                        array('log' => 1, 'keyword' => $keyword),
                        '',
                        $restreamStandAloneFFMPEG
                    ) !== false;
                }
                $execResponse = execFFMPEGAsyncOrRemote($command . ' > ' . $safeLogFile . ' 2>&1 ', $keyword, '', $restreamStandAloneFFMPEG);
                if ($launch->remote) {
                    $remoteResponse = json_decode((string) $execResponse);
                    if (is_object($remoteResponse) && isset($remoteResponse->error)) {
                        $launch->accepted = empty($remoteResponse->error);
                    }
                } else {
                    $launch->accepted = is_numeric($execResponse) && intval($execResponse) > 0;
                    $launch->pid = $launch->accepted ? intval($execResponse) : 0;
                }
                error_log("Restreamer.json.php startRestream launch response " . json_encode(array(
                    'keyword' => $keyword,
                    'remote' => $launch->remote,
                    'accepted' => $launch->accepted,
                )));
                rl_logEvent('launch_result', array(
                    'correlationId' => $correlationId,
                    'destinationHost' => $destInfo['host'],
                    'destinationProtocol' => $destInfo['protocol'],
                    'keyword' => $keyword,
                    'remote' => $launch->remote,
                    'accepted' => $launch->accepted,
                    'pid' => $launch->pid,
                ));
            } else {
                // Fallback: execute directly
                exec($command . ' > ' . $safeLogFile . ' 2>&1 &', $execOutput, $execReturn);
                $launch->accepted = $execReturn === 0;
                error_log("Restreamer.json.php startRestream exec fallback response " . json_encode(array('return' => $execReturn, 'output' => $execOutput)));
                rl_logEvent('launch_result', array(
                    'correlationId' => $correlationId,
                    'destinationHost' => $destInfo['host'],
                    'destinationProtocol' => $destInfo['protocol'],
                    'remote' => false,
                    'accepted' => $launch->accepted,
                ));
            }


            if (
                !empty($fallbackDestination)
                && shouldAttemptAutomaticRestreamFallback($primaryDestinationForFallback, $fallbackDestination, true)
            ) {
                // The manual "Start" button (getAction.json.php) is a synchronous HTTP request
                // bound by url_get_contents()'s ~60s default_socket_timeout (see the
                // STARTRESTREAM_MAX_TRIES comment above) - the normal launch path here already
                // has a 300s curl timeout (Live::sendRestream()), so only that path can afford the
                // blocking 8s+4s probe below. The manual-start path still gets the immediate
                // accept-failure signal, just not the blocking warmup/sample wait.
                $allowBlockingProbe = (@$robj->type !== 'start');
                $initialConnectionFailed = automaticRestreamInitialConnectionFailed($launch, $robj, $allowBlockingProbe);
                if (shouldAttemptAutomaticRestreamFallback($primaryDestinationForFallback, $fallbackDestination, $initialConnectionFailed)) {
                    error_log(
                        'Restreamer.json.php startRestream RTMPS failed during the initial connection window; '
                        . 'retrying the matching YouTube destination over RTMP'
                    );
                    stopAutomaticRestreamLaunch($launch, $robj);
                    $robj->restreamsDestinations[$robj->live_restreams_id] = $fallbackDestination;

                    flock($lockFileHandle, LOCK_UN);
                    fclose($lockFileHandle);
                    @unlink($lockFilePath);

                    return startRestream(
                        $originalM3u8,
                        array($fallbackDestination),
                        $logFile,
                        $robj,
                        $tries,
                        $startTime,
                        ''
                    );
                }
            }
        }
        error_log("Restreamer.json.php startRestream finish");
    }

    $robj->logFile = $logFile;
    notifyStreamer($robj);
    // 🔓 Release lock
    flock($lockFileHandle, LOCK_UN);
    fclose($lockFileHandle);
    @unlink($lockFilePath);
    return true;
}

function automaticRestreamInitialConnectionFailed($launch, $robj, $allowBlockingProbe = true)
{
    if (!is_object($launch)) {
        return false;
    }
    if ($launch->accepted === false) {
        return true;
    }
    if (!$allowBlockingProbe) {
        return false;
    }

    sleep(AUTOMATIC_RESTREAM_INITIAL_WARMUP_SECONDS);
    $firstSample = getAutomaticRestreamLaunchSample($launch, $robj);
    sleep(AUTOMATIC_RESTREAM_PROGRESS_SAMPLE_SECONDS);
    $secondSample = getAutomaticRestreamLaunchSample($launch, $robj);

    return automaticRestreamLaunchSamplesIndicateFailure($firstSample, $secondSample);
}

function getAutomaticRestreamLaunchSample($launch, $robj)
{
    $sample = array(
        'known' => false,
        'running' => false,
        'modified' => 0,
    );

    if (!empty($launch->remote)) {
        if (!function_exists('getFFMPEGRemoteLog')) {
            return $sample;
        }
        $status = getFFMPEGRemoteLog($launch->keyword, $launch->restreamStandAloneFFMPEG);
        if (!is_object($status)) {
            error_log('Restreamer.json.php RTMPS initial status is unknown because the remote executor did not respond');
            return $sample;
        }

        $sample['known'] = true;
        $sample['running'] = empty($status->error) && !empty($status->isActive);
        $sample['modified'] = isset($status->modified) ? intval($status->modified) : 0;
        return $sample;
    }

    $logFile = isset($launch->logFile) ? $launch->logFile : '';
    $logExists = !empty($logFile) && file_exists($logFile);
    $sample['known'] = true;
    $sample['modified'] = $logExists ? intval(@filemtime($logFile)) : 0;

    if (!empty($launch->pid)) {
        $sample['running'] = isRestreamProcessIdRunning($launch->pid);
    } else {
        $process = getProcess($robj);
        $logIsFresh = $logExists && (time() - $sample['modified']) < 10;
        $sample['running'] = !empty($process) || $logIsFresh;
    }

    return $sample;
}

function isRestreamProcessIdRunning($pid)
{
    $pid = intval($pid);
    if ($pid <= 0) {
        return false;
    }

    $stat = @file_get_contents("/proc/{$pid}/stat");
    if ($stat !== false) {
        if (preg_match('/^\d+\s+\(.+\)\s+([A-Z])\s/', $stat, $matches)) {
            return $matches[1] !== 'Z';
        }
        return true;
    }

    if (function_exists('posix_kill')) {
        return @posix_kill($pid, 0);
    }

    return false;
}

function stopAutomaticRestreamLaunch($launch, $robj)
{
    if (!is_object($launch)) {
        return false;
    }

    if (!empty($launch->remote)) {
        if (function_exists('stopFFMPEGRemote')) {
            stopFFMPEGRemote($launch->keyword, $launch->restreamStandAloneFFMPEG);
            return true;
        }
        return false;
    }

    if (!empty($launch->pid)) {
        $pid = intval($launch->pid);
        exec("kill -9 {$pid} 2>&1", $output, $returnVar);
        return $returnVar === 0;
    }

    return killIfIsRunning($robj);
}

function getRestreamRequestSummary($robj)
{
    $summary = new stdClass();
    $summary->emptyObject = empty($robj);
    if (empty($robj)) {
        return $summary;
    }
    $summary->type = @$robj->type;
    $summary->users_id = @$robj->users_id;
    $summary->liveTransmitionHistory_id = @$robj->liveTransmitionHistory_id;
    $summary->live_restreams_id = @$robj->live_restreams_id;
    $summary->hasToken = !empty($robj->token);
    $summary->hasResponseToken = !empty($robj->responseToken);
    $summary->hasRestreamsToken = !empty($robj->restreamsToken);
    $summary->m3u8 = @$robj->m3u8;
    $summary->destinationsCount = empty($robj->restreamsDestinations) || !is_array($robj->restreamsDestinations) ? 0 : count($robj->restreamsDestinations);
    $summary->destinationIds = empty($robj->restreamsDestinations) || !is_array($robj->restreamsDestinations) ? [] : array_keys($robj->restreamsDestinations);
    $summary->logFile = @$robj->logFile;
    $summary->test = !empty($robj->test);
    return $summary;
}

function buildRtmpTcurl(string $url): string
{
    $p = parse_url($url);
    if (empty($p['scheme']) || empty($p['host'])) return $url;

    $scheme = $p['scheme'];
    $host   = $p['host'];
    $port   = isset($p['port']) ? (':' . $p['port']) : '';
    $path = $p['path'] ?? '/';
    $dir = rtrim(dirname($path), '/');
    if ($dir === '.' || $dir === '') {
        $basePath = '/';
    } else {
        $basePath = $dir . '/';
    }

    return "{$scheme}://{$host}{$port}{$basePath}";
}

$isOpenSSLEnabled = null;

function isOpenSSLEnabled()
{
    global $isOpenSSLEnabled, $ffmpegBinary;
    if (isset($isOpenSSLEnabled)) {
        return $isOpenSSLEnabled;
    }
    exec("{$ffmpegBinary} 2>&1", $output, $return_var);
    foreach ($output as $value) {
        if (preg_match("/configuration:.*--enable-openssl/i", $value)) {
            $isOpenSSLEnabled = true;
            return $isOpenSSLEnabled;
        }
    }
    $isOpenSSLEnabled = false;
    return $isOpenSSLEnabled;
}

function whichffmpeg()
{
    exec("which ffmpeg 2>&1", $output, $return_var);
    return @$output[0];
}

function getProcess($robj)
{
    global $ffmpegBinary;

    $m3u8 = clearCommandURL($robj->m3u8);
    $liveTransmitionHistory_id = intval($robj->liveTransmitionHistory_id);
    $live_restreams_id = intval($robj->live_restreams_id);

    $patternExtra = '';
    $patternExtraRegex = '';
    if (!empty($live_restreams_id)) {
        $patternExtra .= "live_restreams_id={$live_restreams_id}";
        $patternExtraRegex .= preg_quote("live_restreams_id={$live_restreams_id}", '/');
    }
    if (!empty($liveTransmitionHistory_id)) {
        $patternExtra .= ".*liveTransmitionHistory_id={$liveTransmitionHistory_id}";
        $patternExtraRegex .= ".*" . preg_quote("liveTransmitionHistory_id={$liveTransmitionHistory_id}", '/');
    }

    // --------- First check using `pgrep` ----------
    $pgrepPattern = preg_quote($ffmpegBinary, '/') . ".*" . preg_quote($m3u8, '/') . ".*{$patternExtraRegex}";
    exec("pgrep -af " . escapeshellarg($pgrepPattern), $pgrepOutput);
    foreach ($pgrepOutput as $line) {
        if (preg_match('/\bpgrep\s+-af\b/i', $line)) {
            continue;
        }
        if (preg_match('/^(\d+)\s+(.*)$/', trim($line), $matches)) {
            error_log("Restreamer:getProcess [pgrep] found process: {$line}");
            return [$matches[1], $matches[2]];
        }
    }

    // --------- Fallback check using `ps` ----------
    exec("ps -ax 2>&1", $psOutput);
    $pattern = "/^([0-9]+).*" . preg_quote($ffmpegBinary, '/') . ".*" . preg_quote($m3u8, '/') . ".*{$patternExtraRegex}/i";
    foreach ($psOutput as $line) {
        if (preg_match('/\bpgrep\s+-af\b/i', $line)) {
            continue;
        }
        if (preg_match($pattern, trim($line), $matches)) {
            error_log("Restreamer:getProcess [ps] found process: {$line}");
            return [$matches[1], $line];
        }
    }

    error_log("Restreamer:getProcess no matching process found using pgrep or ps.");
    return false;
}

function killIfIsRunning($robj)
{
    $process = getProcess($robj);
    //error_log("Restreamer.json.php killIfIsRunning checking if there is a process running for {$m3u8} ");
    if (!empty($process) && is_array($process)) {
        error_log("Restreamer.json.php killIfIsRunning there is a process running " . json_encode($process));
        $pid = intval($process[0]); // Fixed: use index 0 for PID
        if ($pid > 0) {
            error_log("Restreamer.json.php killIfIsRunning killing {$pid} ");
            // Sanitize PID to prevent command injection
            $safePid = filter_var($pid, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));
            if ($safePid !== false) {
                exec("kill -9 " . escapeshellarg($safePid) . " 2>&1", $output, $return_var);
                sleep(1);
            }
        }
        return true;
    } else {
        //error_log("Restreamer.json.php killIfIsRunning there is not a process running for {$command} ");
    }
    return false;
}

function replaceSlashesForPregMatch($str)
{
    return str_replace('/', '.', $str);
}

function _object_to_array($obj)
{
    //only process if it's an object or array being passed to the function
    if (is_object($obj) || is_array($obj)) {
        $ret = (array) $obj;
        foreach ($ret as &$item) {
            //recursively process EACH element regardless of type
            $item = _object_to_array($item);
        }
        return $ret;
    }
    //otherwise (i.e. for scalar values) return without modification
    else {
        return $obj;
    }
}

function _addQueryStringParameter($url, $varname, $value)
{
    $parsedUrl = parse_url($url);
    if (empty($parsedUrl['host'])) {
        return "";
    }
    $query = [];

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
    }
    $query[$varname] = $value;
    $path = $parsedUrl['path'] ?? '';
    $query = !empty($query) ? '?' . http_build_query($query) : '';

    $port = '';
    if (!empty($parsedUrl['port']) && $parsedUrl['port'] != '80') {
        $port = ":{$parsedUrl['port']}";
    }

    if (empty($parsedUrl['scheme'])) {
        $scheme = '';
    } else {
        $scheme = "{$parsedUrl['scheme']}:";
    }
    return $scheme . '//' . $parsedUrl['host'] . $port . $path . $query;
}
