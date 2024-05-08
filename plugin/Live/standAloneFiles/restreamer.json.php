<?php

use Amp\Deferred;
use Amp\Loop;

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
$streamerURL = "https://demo.avideo.com/"; // change it to your streamer URL
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

ini_set("memory_limit", -1);
ini_set('default_socket_timeout', $global_timeLimit);
set_time_limit($global_timeLimit);
ini_set('max_execution_time', $global_timeLimit);
ini_set("memory_limit", "-1");

$logFileLocation = rtrim($logFileLocation, "/") . '/';

header('Content-Type: application/json');

$isATest = false;

$logFile = $logFileLocation . "ffmpeg_restreamer_{users_id}_" . date("Y-m-d-h-i-s") . ".log";

$configFile = dirname(__FILE__) . '/../../../videos/configuration.php';

if (file_exists($configFile)) {
    $doNotIncludeConfig = 1;
    include_once $configFile;
    $streamerURL = $global['webSiteRootURL'];
    error_log("Restreamer.json.php is using local configuration");
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
        $obj->error = false;
        error_log("Restreamer.json.php token verified " . json_encode($json));
        switch ($json->action) {
            case 'log':
                $obj->logName = str_replace($logFileLocation, '', $json->logFile);
                $obj->logName = preg_replace('/[^a-z0-9_.-]/i', '', $obj->logName);                
                if(!empty($obj->logName)){
                    $logFile = $logFileLocation . $obj->logName;
                    if(file_exists($logFile)){
                        $obj->modified = @filemtime($logFile);
                        $obj->secondsAgo = $obj->time - $obj->modified;
                        $obj->isActive = $obj->secondsAgo < 10;
                    }
                }

                echo json_encode($obj);
                exit;
                break;
            case 'stop':
                $obj->killIfIsRunning = killIfIsRunning($json);
                $obj->logName = str_replace($logFileLocation, '', $json->logFile);
                $obj->logName = preg_replace('/[^a-z0-9_.-]/i', '', $obj->logName);
                $logFile = $logFileLocation . $obj->logName;
                unlink($logFile);
                echo json_encode($obj);
                exit;
                break;
            case 'start':
                $robj = $json;
                $robj->type = 'start';
                break;
        }
    } else {
        if(empty($json)){
            $error = '';
        }else{
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

function _addLastSlash($word) {
    return $word . (_hasLastSlash($word) ? "" : "/");
}

function _hasLastSlash($word) {
    return substr($word, -1) === '/';
}

function _getLiveKey($token) {
    global $streamerURL, $isATest;
    
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = '';
    $obj->newRestreamsDestination = '';
    $obj->content = '';
    
    if ($isATest) {
        return $obj;
    }
    $obj->content = file_get_contents("{$streamerURL}plugin/Live/view/Live_restreams/getLiveKey.json.php?token={$token}");
    if (!empty($obj->content)) {
        $json = json_decode($obj->content);
        if (!empty($json) && $json->error === false) {
            if (!empty($json->stream_key) && !empty($json->stream_url)) {
                $newRestreamsDestination = _addLastSlash($json->stream_url) . $json->stream_key;
                error_log("Restreamer.json.php _getLiveKey found $newRestreamsDestination");
                $obj->newRestreamsDestination = $newRestreamsDestination;
                $obj->error = false;
            } 
        }else if(!empty($json->rawData)){
            $rawData = json_decode($json->rawData);
            $obj->msg = $rawData->message;
        }
    }
    return $obj;
}

$errorMessages = array();

if (!$isCommandLine) { // not command line
    if(empty($robj)){
        $request = file_get_contents("php://input");
        error_log("Restreamer.json.php php://input {$request}");
        $robj = json_decode($request);
        $robj->type = 'decoded from request';
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
            error_log("***Restreamer.json.php using restreamsToken ". json_encode($robj->restreamsToken));
            //var_dump($robj->restreamsToken, $robj->restreamsDestinations);exit;
            if (empty($isATest)) {
                foreach ($robj->restreamsToken as $key => $token) {
                    
                    $liveKey = _getLiveKey($token);                    
                    if(empty($liveKey->error)){
                        $newRestreamsDestination = $liveKey->newRestreamsDestination;
                    }else{
                        error_log("Restreamer.json.php ERROR try again in 3 seconds");
                        sleep(3);
                        $liveKey = _getLiveKey($token);                    
                        if(empty($liveKey->error)){
                            $newRestreamsDestination = $liveKey->newRestreamsDestination;
                        }else{                            
                            $errorMessages[] = $liveKey->msg;
                        }
                    }
                    
                    if (empty($newRestreamsDestination)) {
                        error_log("Restreamer.json.php ERROR ". json_encode($liveKey));
                        unset($robj->restreamsDestinations[$key]);
                    } else {
                        $robj->restreamsDestinations[$key] = $newRestreamsDestination;
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
    $robj->m3u8 = $argv[1];
    $robj->restreamsDestinations = [$argv[2]];
    $robj->users_id = 'commandline';
    $robj->logFile = @$argv[3];
    $robj->responseToken = '';
}

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
    error_log("Restreamer.json.php ERROR {$obj->msg}");
    die(json_encode($obj));
}
error_log("Restreamer.json.php Found " . count($robj->restreamsDestinations) . " destinations: " . json_encode($robj->restreamsDestinations));

if (!$isCommandLine) {
    // check the token
    if (empty($obj->token)) {
    $errorMessages[] = "Token is empty";
    $obj->msg = implode(PHP_EOL, $errorMessages);
        error_log("Restreamer.json.php ERROR {$obj->msg}");
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
        error_log("Restreamer.json.php empty json ERROR {$obj->msg} ({$verifyTokenURL}) ");
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
Loop::run(function () {
    global $robj;
    runRestream($robj)->onResolve(function (Throwable $error = null, $result = null) {
        if ($error) {
            error_log("Restreamer.json.php runRestream: asyncOperation1 fail -> " . $error->getMessage());
        } else {
            error_log("Restreamer.json.php runRestream: asyncOperation1 result -> " . json_encode($result));
        }
    });
});
error_log("Restreamer.json.php finish async ");
$obj->error = false;
die(json_encode($obj));

function runRestream($robj) {
    $m3u8 = $robj->m3u8;
    error_log("runRestream ".json_encode($robj));
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
        $pid[] = startRestream($m3u8, $restreamsDestinations, $logFile, $robj);
    } else {
        error_log("Restreamer.json.php runRestream separateRestreams " . count($restreamsDestinations));
        foreach ($restreamsDestinations as $key => $value) {
            sleep(5);
            $robj->live_restreams_id = $key;
            $host = clearCommandURL(parse_url($value, PHP_URL_HOST));
            $pid[] = startRestream($m3u8, [$value], str_replace(".log", "_{$key}_{$robj->liveTransmitionHistory_id}_{$host}.log", $logFile), $robj);
        }
    }
    $deferred->resolve($pid);
    return $deferred->promise();
}

function notifyStreamer($robj) {
    global $streamerURL;
    $restreamerURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $m3u8 = $robj->m3u8;
    error_log("notifyStreamer ".json_encode($robj));
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
                'live_restreams_id' => $robj->live_restreams_id,)
    );
    error_log("Restreamer.json.php notifyStreamer {$data_string}");

    $url = "{$streamerURL}plugin/Live/view/Live_restreams_logs/add.json.php";
    return postToURL($url, $data_string);
}

function verifyTokenForAction($token) {
    global $streamerURL;
    $data_string = json_encode(array('token' => $token));
    error_log("Restreamer.json.php verifyTokenForAction {$data_string}");

    $url = "{$streamerURL}plugin/Live/view/Live_restreams/verifyTokenForAction.json.php";
    //var_dump($url);//exit;
    return postToURL($url, $data_string);
}

function postToURL($url, $data_string, $timeLimit = 10) {
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
        curl_setopt($ch, CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string),
                ]
        );
        //$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $output = curl_exec($ch);
        curl_close($ch);
        set_time_limit($global_timeLimit);
        //var_dump($url, $data_string, $output);//exit;
        return json_decode($output);
    } catch (Exception $exc) {
        error_log("Restreamer.json.php postToURL ERROR " . $exc->getTraceAsString());
    }
    set_time_limit($global_timeLimit);
    return false;
}

function clearCommandURL($url) {
    return preg_replace('/[^0-9a-z:.\/_&?=-]/i', "", $url);
}

function _isURL200($url, $forceRecheck = false) {
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
    foreach ($headers as $value) {
        if (
                strpos($value, '200') ||
                strpos($value, '302') ||
                strpos($value, '304')
        ) {
            $result = true;
            break;
        } else {
            error_log('_isURL200: '.$value);
        }
    }
    set_time_limit($global_timeLimit);

    return $result;
}

function _make_path($path) {
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

function startRestream($m3u8, $restreamsDestinations, $logFile, $robj, $tries = 1) {
    global $ffmpegBinary, $isATest;
    $m3u8 = str_replace('vlu.me', 'live', $m3u8);
    if (empty($restreamsDestinations)) {
        error_log("Restreamer.json.php startRestream ERROR empty restreamsDestinations");
        return false;
    }

    $m3u8 = _addQueryStringParameter($m3u8, 'live_restreams_id', $robj->live_restreams_id);
    $m3u8 = _addQueryStringParameter($m3u8, 'liveTransmitionHistory_id', $robj->liveTransmitionHistory_id);

    $m3u8 = clearCommandURL($m3u8);

    if ($tries === 1) {
        sleep(3);
    }
    if (!$isATest && function_exists('_isURL200') && !_isURL200($m3u8, true)) {
        if ($tries > 20) {
            error_log("Restreamer.json.php startRestream tried too many times, we could not find your stream URL");
            return false;
        }
        if ($tries === 1) {
            error_log("Restreamer.json.php startRestream " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
        }
        error_log("Restreamer.json.php startRestream URL ($m3u8) is NOT ready. trying again ({$tries})");
        sleep($tries);
        return startRestream($m3u8, $restreamsDestinations, $logFile, $robj, $tries + 1);
    }

    error_log("Restreamer.json.php startRestream _isURL200 tries= " . json_encode($tries));
    //sleep(5);
    /*
      $command = "ffmpeg -i {$m3u8} ";
      foreach ($restreamsDestinations as $value) {
      $value = clearCommandURL($value);
      $command .= ' -max_muxing_queue_size 1024 -f flv "' . $value . '" ';
      }
     *
     */
    
      
    $FFMPEGcommand = "{$ffmpegBinary} -re -rw_timeout 30000000 -reconnect 1 -reconnect_streamed 1 -reconnect_delay_max 30 -y -i \"{$m3u8}\" ";
    $FFMPEGComplement = " -max_muxing_queue_size 1024 "
    . '{audioConfig}'
    . "-c:v libx264 "
    . "-pix_fmt yuv420p "  
    //. "-vf \"scale=-2:720,format=yuv420p\" "
    . "-r 30 -g 60 "
    . "-tune zerolatency "
    . "-x264-params \"nal-hrd=cbr\" " // Ensure constant bitrate for compatibility with social media platforms
    . "-b:v 6000k " // Set constant video bitrate
    . "-minrate 6000k -maxrate 6000k -bufsize 6000k " // Set buffer size to match the bitrate
    . "-preset veryfast "
    . "-f flv "
    . "-fflags +genpts " // Ensure smooth playback
    . "-strict -2 " // Allow non-compliant AAC audio
    . "-reconnect 1 " // Enable reconnection in case of a broken pipe
    . "-reconnect_at_eof 1 " // Reconnect at the end of file
    . "-reconnect_streamed 1 " // Reconnect for streamed media
    . "-reconnect_delay_max 30 " // Maximum delay between reconnection attempts
    . "\"{restreamsDestinations}\"";

    if (count($restreamsDestinations) > 1) {
        //$command = "{$ffmpegBinary} -re -i \"{$m3u8}\" ";
        $command = $FFMPEGcommand;
        foreach ($restreamsDestinations as $value) {
            if (!isOpenSSLEnabled() && preg_match("/rtpms:/i", $value)) {
                error_log("Restreamer.json.php startRestream ERROR #1 FFMPEG openssl is not enabled, ignoring $value ");
                continue;
            }            
            $audioConfig = getAudioConfiguration($value);            
            $value = clearCommandURL($value);
            $command .= str_replace(array('{audioConfig}', '{restreamsDestinations}'), array($audioConfig, $value), $FFMPEGComplement);
        }
    } else {
        if (!isOpenSSLEnabled() && preg_match("/rtpms:/i", $restreamsDestinations[0])) {
            error_log("Restreamer.json.php startRestream ERROR #2 FFMPEG openssl is not enabled, ignoring {$restreamsDestinations[0]} ");
        } else {
            $audioConfig = getAudioConfiguration($restreamsDestinations[0]);
            //$command = "ffmpeg -re -i \"{$m3u8}\" -max_muxing_queue_size 1024 -acodec copy -bsf:a aac_adtstoasc -vcodec copy -f flv \"{$restreamsDestinations[0]}\"";
            $command = $FFMPEGcommand;
            $command .= str_replace(array('{audioConfig}', '{restreamsDestinations}'), array($audioConfig, $restreamsDestinations[0]), $FFMPEGComplement);
        }
    }
    if (empty($command) || !preg_match("/-f flv/i", $command)) {
        error_log("Restreamer.json.php startRestream ERROR command is empty ");
    } else {
        error_log("Restreamer.json.php startRestream startRestream, check the file ($logFile) for the log");
        _make_path($logFile);
        file_put_contents($logFile, $command . PHP_EOL);
        if (empty($isATest)) {
            exec('nohup ' . $command . '  2>> ' . $logFile . ' > /dev/null &');
        }
        error_log("Restreamer.json.php startRestream finish");
    }
    $robj->logFile = $logFile;
    notifyStreamer($robj);
    return true;
}

function getAudioConfiguration($source){
    if(preg_match("/facebook.com/i", $source)){
        $audioConfig = '-c:a copy -bsf:a aac_adtstoasc -ac 1 -ar 44100 -b:a 128k ';
    }else if(preg_match("/youtube.com/i", $source)){
        $audioConfig = '-c:a aac -b:a 128k ';
    }else{
        $audioConfig = '-c:a copy ';
    }
    
    return $audioConfig;
}

$isOpenSSLEnabled = null;

function isOpenSSLEnabled() {
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

function whichffmpeg() {
    exec("which ffmpeg 2>&1", $output, $return_var);
    return @$output[0];
}

function getProcess($robj) {
    $m3u8 = $robj->m3u8;
    $m3u8 = clearCommandURL($m3u8);
    $liveTransmitionHistory_id = intval($robj->liveTransmitionHistory_id);
    $live_restreams_id = intval($robj->live_restreams_id);

    if (!empty($live_restreams_id)) {
        $m3u8 .= ".*live_restreams_id={$live_restreams_id}";
    }
    if (!empty($liveTransmitionHistory_id)) {
        $m3u8 .= ".*liveTransmitionHistory_id={$liveTransmitionHistory_id}";
    }

    global $ffmpegBinary;
    exec("ps -ax 2>&1", $output, $return_var);
    //error_log("Restreamer.json.php:getProcess ". json_encode($output)); 
    $pattern = "/^([0-9]+).*" . replaceSlashesForPregMatch($ffmpegBinary) . ".*" . replaceSlashesForPregMatch($m3u8) . "/i";
    foreach ($output as $value) {
        //error_log("Restreamer.json.php:getProcess {$pattern}");
        if (preg_match($pattern, trim($value), $matches)) {
            error_log("Restreamer.json.php:getProcess found " . json_encode($value));
            return $matches;
        }
    }
    error_log("Restreamer.json.php:getProcess NOT found {$pattern}");
    return false;
}

function killIfIsRunning($robj) {
    $process = getProcess($robj);
    //error_log("Restreamer.json.php killIfIsRunning checking if there is a process running for {$m3u8} ");
    if (!empty($process)) {
        error_log("Restreamer.json.php killIfIsRunning there is a process running " . json_encode($process));
        $pid = intval($process[1]);
        if (!empty($pid)) {
            error_log("Restreamer.json.php killIfIsRunning killing {$pid} ");
            exec("kill -9 {$pid} 2>&1", $output, $return_var);
            sleep(1);
        }
        return true;
    } else {
        //error_log("Restreamer.json.php killIfIsRunning there is not a process running for {$command} ");
    }
    return false;
}

function replaceSlashesForPregMatch($str) {
    return str_replace('/', '.', $str);
}

function _object_to_array($obj) {
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

function _addQueryStringParameter($url, $varname, $value) {
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
