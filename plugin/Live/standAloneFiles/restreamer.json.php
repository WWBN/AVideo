<?php

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
$separateRestreams = false;

// optional you can change the default FFMPEG
//$ffmpegBinary = '/usr/bin/ffmpeg';
$ffmpegBinary = '/usr/local/bin/ffmpeg';

/*
 * DO NOT EDIT AFTER THIS LINE
 */

if(!file_exists($ffmpegBinary)){
    $ffmpegBinary = '/usr/bin/ffmpeg';
    if(!file_exists($ffmpegBinary)){
        $ffmpegBinary = '/usr/local/bin/ffmpeg';
    }
}

set_time_limit(300);
ini_set('max_execution_time', 300);

$logFileLocation = rtrim($logFileLocation, "/") . '/';
$logFile = $logFileLocation . "ffmpeg_{users_id}_" . date("Y-m-d-h-i-s") . ".log";

header('Content-Type: application/json');
$configFile = '../../../videos/configuration.php';

if (file_exists($configFile)) {
    include_once $configFile;
    $streamerURL = $global['webSiteRootURL'];
}

error_log("Restreamer.json.php start");
$whichffmpeg = whichffmpeg();
if($whichffmpeg!==$ffmpegBinary){
    error_log("Restreamer.json.php WARNING you are using a different FFMPEG $whichffmpeg!==$ffmpegBinary");
}

$request = file_get_contents("php://input");
error_log("Restreamer.json.php php://input {$request}");
$robj = json_decode($request);

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->streamerURL = $streamerURL;
$obj->token = $robj->token;
$obj->pid = array();
$obj->logFile = str_replace('{users_id}', $robj->users_id, $logFile);


if (empty($robj->restreamsDestinations) || !is_array($robj->restreamsDestinations)) {
    $obj->msg = "There are no restreams Destinations";
    error_log("Restreamer.json.php ERROR {$obj->msg}");
    die(json_encode($obj));
}
error_log("Restreamer.json.php Found " . count($robj->restreamsDestinations) . " destinations: " . json_encode($robj->restreamsDestinations));

// check the token
if (empty($obj->token)) {
    $obj->msg = "Token is empty";
    error_log("Restreamer.json.php ERROR {$obj->msg}");
    die(json_encode($obj));
}

$verifyTokenURL = "{$obj->streamerURL}plugin/Live/verifyToken.json.php?token={$obj->token}";

error_log("Restreamer.json.php verifying token {$verifyTokenURL}");

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);  

$content = file_get_contents($verifyTokenURL, false, stream_context_create($arrContextOptions));

error_log("Restreamer.json.php verification respond content {$content}");
$json = json_decode($content);

if (empty($json)) {
    $obj->msg = "Could not verify token";
    error_log("Restreamer.json.php ERROR {$obj->msg} ({$verifyTokenURL}) ");
    die(json_encode($obj));
} else if (!empty($json->error)) {
    $obj->msg = "Token is invalid";
    error_log("Restreamer.json.php ERROR {$obj->msg} ({$verifyTokenURL}) " . json_encode($json));
    die(json_encode($obj));
}
error_log("Restreamer.json.php token is correct");

ignore_user_abort(true);
ob_start();
header("Connection: close");
@header("Content-Length: " . ob_get_length());
ob_end_flush();
flush();

if (empty($separateRestreams)) {
    error_log("Restreamer.json.php all in one command ");
    $obj->pid[] = startRestream($robj->m3u8, $robj->restreamsDestinations, $obj->logFile);
} else {
    error_log("Restreamer.json.php separateRestreams " . count($robj->restreamsDestinations));
    foreach ($robj->restreamsDestinations as $key => $value) {
        sleep(0.5);
        $obj->pid[] = startRestream($robj->m3u8, array($value), str_replace(".log", "_{$key}.log", $obj->logFile));
    }
}
$obj->error = false;

error_log("Restreamer.json.php finish " . json_encode($obj));
die(json_encode($obj));

function clearCommandURL($url) {
    return preg_replace('/[^0-9a-z:.\/_&?=-]/i', "", $url);
}

function isURL200($url) {
    error_log("Restreamer.json.php checking URL {$url}");
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("Restreamer.json.php URL {$url} return code {$httpcode}");
    return $httpcode == 200;
}

function startRestream($m3u8, $restreamsDestinations, $logFile, $tries = 1) {
    global $ffmpegBinary;
    if (empty($restreamsDestinations)) {
        error_log("Restreamer.json.php ERROR empty restreamsDestinations");
        return false;
    }
    $m3u8 = clearCommandURL($m3u8);

    killIfIsRunning($m3u8);
    
    if (!isURL200($m3u8)) {
        if ($tries > 10) {
            error_log("Restreamer.json.php tried too many times, we could not find your stream URL");
            return false;
        }
        error_log("Restreamer.json.php URL is not ready. trying again ({$tries})");
        sleep($tries);
        return startRestream($m3u8, $restreamsDestinations, $logFile, $tries + 1);
    }
    /*
      $command = "ffmpeg -i {$m3u8} ";
      foreach ($restreamsDestinations as $value) {
      $value = clearCommandURL($value);
      $command .= ' -max_muxing_queue_size 1024 -f flv "' . $value . '" ';
      }
     * 
     */
    if (count($restreamsDestinations) > 1) {
        $command = "{$ffmpegBinary} -re -i \"{$m3u8}\" ";
        foreach ($restreamsDestinations as $value) {
            if(!isOpenSSLEnabled() && preg_match("/rtpms:/i", $value)){
                error_log("Restreamer.json.php ERROR #1 FFMPEG openssl is not enabled, ignoring $value ");
                continue;
            }
            $value = clearCommandURL($value);
            $command .= ' -max_muxing_queue_size 1024 -acodec copy -bsf:a aac_adtstoasc -vcodec copy -f flv "' . $value . '" ';
        }
    } else {
        if(!isOpenSSLEnabled() && preg_match("/rtpms:/i", $restreamsDestinations[0])){
            error_log("Restreamer.json.php ERROR #2 FFMPEG openssl is not enabled, ignoring {$restreamsDestinations[0]} ");
        }else{
            $command = "ffmpeg -re -i \"{$m3u8}\" -max_muxing_queue_size 1024 -acodec copy -bsf:a aac_adtstoasc -vcodec copy -f flv \"{$restreamsDestinations[0]}\"";
        }
    }
    if(empty($command) || !preg_match("/-f flv/i", $command)){
        error_log("Restreamer.json.php ERROR command is empty ");
    }else{
        error_log("Restreamer.json.php startRestream {$command}, check the file ($logFile) for the log");
        exec('echo \'' . $command . PHP_EOL . '\'  > ' . $logFile);
        exec('nohup ' . $command . '  2>> ' . $logFile . ' > /dev/null &');
        error_log("Restreamer.json.php startRestream finish");
    }
    return true;
}

$isOpenSSLEnabled = null;
function isOpenSSLEnabled() {
    global $isOpenSSLEnabled, $ffmpegBinary;
    if(isset($isOpenSSLEnabled)){
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

function getProcess($m3u8){
    $m3u8 = clearCommandURL($m3u8);
    global $ffmpegBinary;
    exec("ps -ax 2>&1", $output, $return_var);
    //error_log("Restreamer.json.php:getProcess ". json_encode($output));
    foreach ($output as $value) {
        $pattern = "/^([0-9]+).*".replaceSlashesForPregMatch($ffmpegBinary).".*".replaceSlashesForPregMatch($m3u8)."/i";
        //error_log("Restreamer.json.php:getProcess {$pattern}");
        if (preg_match($pattern, trim($value), $matches)) {
            return $matches;
        }
    }
    return false;
}

function killIfIsRunning($m3u8){
    $process = getProcess($m3u8);
    error_log("Restreamer.json.php killIfIsRunning checking if there is a process running for {$m3u8} ");
    if(!empty($process)){
        error_log("Restreamer.json.php killIfIsRunning there is a process running for {$m3u8} ". json_encode($process));
        $pid = intval($process[1]);
        if(!empty($pid)){
            error_log("Restreamer.json.php killIfIsRunning killing {$pid} ");
            exec("kill -9 {$pid} 2>&1", $output, $return_var);
            sleep(1);
        }
        return true;
    }else{
        error_log("Restreamer.json.php killIfIsRunning there is not a process running for {$m3u8} ");
    }
    return false;
}

function replaceSlashesForPregMatch($str){
    return str_replace('/', '.', $str);
}
