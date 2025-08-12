<?php
require_once __DIR__ . '/../../videos/configuration.php';

$_start = microtime(true);
$fontSize = 18;
$timeLineElementSize = 300;

if (isMobile()) {
    $timeLineElementSize = 150;
    $fontSize = 12;
}
$default_socket_timeout = 4;
$cacheTimeout = 60;
$forceRecreate = false;
if (isCommandLineInterface()) {
    _ob_end_clean();
    _error_log('Commandline: Command line EPG');
    $forceRecreate = true;
    $default_socket_timeout = 10;
}
//$forceRecreate = true;
ini_set('default_socket_timeout', $default_socket_timeout);
set_time_limit($default_socket_timeout * 100);
ini_set('max_execution_time', $default_socket_timeout * 100);

$cacheNameEpgPage = 'epgPage_' . $timeLineElementSize . md5(json_encode($_GET));
if (empty($forceRecreate)) {
    $content = ObjectYPT::getCache($cacheNameEpgPage, $cacheTimeout); // 1 minute
}
if (!empty($content)) {
    echo $content;
    $_end = microtime(true) - $_start;
    echo '<!-- pageCache=' . $_end . ' -->';
    exit;
}
require_once $global['systemRootPath'] . 'objects/EpgParser.php';

$epgs = array();
$minDate = strtotime('+1 year');
$maxDate = 0;
$videos_id = intval(@$_REQUEST['videos_id']);
$_GET['rowCount'] = $_REQUEST['rowCount'] = 500;
$videos = Video::getAllActiveEPGs();
foreach ($videos as $video) {
    if ($video['status'] !== Video::STATUS_ACTIVE || !isValidURL($video['epg_link'])) {
        continue;
    }
    //if($video['id']=='3847'){        var_dump($video['status']);exit;}
    $epgs[] = $video;
}
$timeLineElementMinutes = 30;
$paddingSize = 10;
$minimumWidth = 80;
$minimumWidth1Dot = 50;
$minimumWidthHide = 30;
$minimumSmallFont = $timeLineElementSize;

$minuteSize = $timeLineElementSize / $timeLineElementMinutes;
$secondSize = $minuteSize / 60;

$cacheName = 'epg';

$cacheName = '/channelsList_' . md5(json_encode($_GET));
if (empty($forceRecreate)) {
    $channelsList = getEPGCache($cacheName);
    if (!empty($channelsList)) {
        $channelsList = json_decode($channelsList);
    }
}
//var_dump($cacheName, $channelsList);exit;
$_MaxDaysFromNow = strtotime('+24 hours');
$errorMessages = [];
if ($forceRecreate || empty($channelsList)) {
    if (isCommandLineInterface()) {
        _error_log('Commandline: Command line EPG line: ' . __LINE__);
        _error_log('Commandline: Command line EPG epgs count: ' . count($epgs));
    }
    $channelsList = array();
    $totalEPGs = count($epgs);
    $countEPGsProgress = 0;
    foreach ($epgs as $epg) {
        $this_videos_id = $epg['id'];
        $programCacheName = '/program_' . md5($epg['epg_link']);
        $countEPGsProgress++;
        if (empty($forceRecreate)) {
            $programData = getEPGCache($programCacheName);
            if (!empty($programData)) {
                $programData = json_decode($programData);
            }
        }
        if ($forceRecreate || empty($programData)) {
            //_error_log("EPG program expired creating again videos_id={$this_videos_id} " . $programCacheName);
            //var_dump($epg['epg_link']);exit;
            $Parser = new \buibr\xmlepg\EpgParser();
            $Parser->setURL($epg['epg_link']);
            $Parser->temp_dir = getCacheDir();
            try {
                if (isCommandLineInterface()) {
                    _error_log("Commandline: [{$countEPGsProgress}/{$totalEPGs}] parsing {$epg['epg_link']} Command line EPG line:" . __LINE__);
                }
                $Parser->parseURL();
                $epgData = $Parser->getEpgdata();
                $channels = $Parser->getChannels();
                //var_dump($channels, $epgData);
                //$Parser->setTargetTimeZone('Europe/Skopje');
                // $Parser->setChannelfilter('prosiebenmaxx.de'); //optional
                // $Parser->setIgnoreDescr('Keine Details verfügbar.'); //optional
                foreach ($channels as $key => $value) {
                    $usedStartDate = array();
                    $channels[$key]['epgData'] = array();
                    foreach ($epgData as $key2 => $program) {
                        if ($program['channel'] != $value['id']) {
                            _error_log("EPG program channel mismatch: {$program['channel']} != {$value['id']}");
                            continue;
                        }
                        // do not process the same start date
                        if(in_array($program['start'], $usedStartDate)){
                            _error_log("EPG program start date already processed: {$program['start']}");
                            continue;
                        }
                        $usedStartDate[] = $program['start'];
                        $timeWillStart = strtotime($program['start']);
                        if ($timeWillStart > $_MaxDaysFromNow) {
                            unset($epgData[$key2]);
                            _error_log("EPG program start time exceeds max limit: {$program['start']}");
                            continue;
                        }
                        $minutes = getDurationInMinutes(date('Y-m-d 00:00:00'), $program['stop']);
                        //var_dump(date('Y-m-d 00:00:00'), $program['stop'], $minutes);
                        if ($minutes > 0) {
                            $channels[$key]['epgData'][] = $program;
                            setMinDate($program['start']);
                            setMaxDate($program['stop']);
                        }
                        unset($epgData[$key2]);
                    }
                    //var_dump($channels[$key]);
                    if (!empty($channels[$key])) {
                        usort($channels[$key]['epgData'], "cmpPrograms");
                        $channels[$key]['videos_id'] = $this_videos_id;
                        $channels[$key]['description'] = $epg['description'];
                        $channels[$key]['images'] = Video::getImageFromID($this_videos_id);
                        if (!empty($epg['title'])) {
                            $channels[$key]['display-name'] = safeString($epg['title']);
                        }
                        //var_dump($forceRecreate, $channelsList);
                        $channelsList[] = $channels[$key];
                        //var_dump($channelsList[0]);exit;
                    }
                }
                $file = setEPGCache($programCacheName, $channelsList);
                _error_log("EPG program cache created videos_id={$this_videos_id} {$programCacheName}" . json_encode($file));
            } catch (Exception $e) {
                $error = new \RuntimeException($e);
                $eMessage = "{$epg['title']}: videos_id={$this_videos_id} epg_link={$epg['epg_link']} ";
                $errorMessages[] = $eMessage;
                _error_log("EPG program ERROR " . $eMessage . ' ' . $error->getMessage());
            }
        } else {
            $channelsList = object_to_array($programData);
            foreach ($channelsList as $program) {
                foreach ($program["epgData"] as $epg) {
                    setMinDate($epg['start']);
                    setMaxDate($epg['stop']);
                }
            }
        }
    }
    if (isCommandLineInterface()) {
        _error_log('Commandline: Command line EPG line:' . __LINE__);
    }
    usort($channelsList, "cmpChannels");
} else {
    if (isCommandLineInterface()) {
        _error_log('Commandline: EPG cache detected line: ' . __LINE__);
    }
    //$channelsList = object_to_array($channelsList);
    //$channelsList = json_decode($channelsList);
}

$rowCount = getRowCount();
$current = getCurrentPage();

$offset = $rowCount*($current-1);
$length = $rowCount;
$channelsList = array_slice(
    $channelsList,
    $offset,
    $length
);

if (isCommandLineInterface()) {
    _error_log('Commandline: EPG done line: ' . __LINE__);
    echo PHP_EOL . implode(PHP_EOL, $errorMessages) . PHP_EOL;
    exit;
}

function getEPGCacheFolder() {
    $videos_dir = getVideosDir();
    $path = "{$videos_dir}EPGCache" . DIRECTORY_SEPARATOR;
    return $path;
}

function setEPGCache($name, $content) {
    if (!is_string($content)) {
        $content = json_encode($content);
    }
    $path = getEPGCacheFolder();
    make_path($path);
    $filename = $path . md5($name) . '.cache';
    //var_dump($path, $filename);exit;
    return file_put_contents($filename, $content);
}

function getEPGCache($name) {
    $path = getEPGCacheFolder();
    make_path($path);
    $filename = $path . md5($name) . '.cache';
    if (time() - filemtime($filename) > 86400) { // older than 24 hours
        return false;
    }
    //var_dump($filename);
    return file_get_contents($filename);
}

if (!empty($_REQUEST['json'])) {
    header('Content-Type: application/json');
    echo json_encode($channelsList);
    exit;
}

//var_dump($epgData);exit;
//var_dump($channelsList);exit;
function cmpPrograms($a, $b) {
    $AStartTime = strtotime($a['start']);
    $BStartTime = strtotime($b['start']);
    if ($AStartTime == $BStartTime) {
        return 0;
    }
    return ($AStartTime < $BStartTime) ? -1 : 1;
}

function cmpChannels($a, $b) {
    return strcasecmp($a['display-name'], $b['display-name']);
}

function getDurationInMinutes($start, $stop) {
    $timeStart = strtotime($start);
    $timeStop = strtotime($stop);
    //var_dump(date('Y-m-d H:i:s',$timeStart), date('Y-m-d H:i:s',$timeStop), $start, $stop);
    $seconds = $timeStop - $timeStart;

    $minutes = intval($seconds / 60);
    return $minutes;
}

function setMaxDate($date) {
    global $maxDate;

    if (empty($maxDate)) {
        $maxDate = 0;
    }

    $newDate = strtotime($date);
    if ($newDate > $maxDate) {
        $maxDate = $newDate;
    }
}

function setMinDate($date) {
    global $minDate;

    if (empty($minDate)) {
        $minDate = strtotime('+30 days');
    }

    $newDate = strtotime($date);
    if ($newDate < $minDate) {
        $minDate = $newDate;
    }
}

function createEPG($channel) {
    global $minuteSize, $Date, $minimumSmallFont, $minimumWidth, $minimumWidthHide, $minimumWidth1Dot, $videos_id;
    $channel = object_to_array($channel);
    $displayname = $channel['display-name'];
    $channelId = $channel['id'];
    $this_videos_id = $channel['videos_id'];
    $firstProgram = $channel['epgData'][0];

    $class = '';
    if (!empty($this_videos_id) && $this_videos_id == $videos_id) {
        $class = 'active';
    }

    $link = Video::getLinkToVideo($this_videos_id);

    //var_dump($channel);exit;
    ?>
    <a href="<?php echo $link; ?>" target="_top">
        <div class="programs <?php echo $class; ?>" id="video_<?php echo $this_videos_id; ?>" >
            <div class="header">
                <?php echo $displayname; ?>
            </div>
            <div class="list">
                <?php
                $nowTime = time();
                foreach ($channel['epgData'] as $key => $program) {
                    $minutesSinceZeroTime = getDurationInMinutes("{$Date} 00:00:00", $program['start']);
                    if ($minutesSinceZeroTime < 0) {
                        continue;
                    }
                    $_stopTime = strtotime($program['stop']);
                    /*
                      if($_stopTime<$nowTime){
                      continue;
                      }
                     *
                     */
                    $minutes = getDurationInMinutes($program['start'], $program['stop']);
                    /**
                     * @var int $timeLineElementSize
                     */
                    $left = ($minuteSize * $minutesSinceZeroTime) + $timeLineElementSize;
                    $width = ($minuteSize * $minutes);
                    $pclass = '';
                    if ($width <= $minimumWidthHide) {
                        $text = '';
                    } else if ($width <= $minimumWidth1Dot) {
                        $text = "<abbr title=\"{$program['title']}\">.</abbr>";
                    } else if ($width <= $minimumWidth) {
                        $text = "<abbr title=\"{$program['title']}\"><small class=\"duration\">{$minutes} Min</small></abbr>";
                    } else if ($width <= $minimumSmallFont) {
                        $text = "<small class=\"small-font\">{$program['title']}<div><small class=\"duration\">{$minutes} Min</small></div></small>";
                    } else {
                        $startTime = date('m-d H:i', strtotime($program['start']));
                        $stopTime = date('m-d H:i', $_stopTime);
                        $text = "{$program['title']}<div><small class=\"duration\">{$minutes} Min</small></div>";
                    }
                    if ($_stopTime < $nowTime) {
                        $pclass = 'finished';
                    }
                    echo "<div style=\"width: {$width}px; left: {$left}px;\" start=\"{$program['start']}\" stop=\"{$program['stop']}\" minutes=\"{$minutes}\" minutesSinceZeroTime=\"{$minutesSinceZeroTime}\" class=\"{$pclass}\">"
                    . "{$text}"
                    . "</div>";
                }
                ?>
            </div>
        </div>
    </a>
    <?php
}

$Date = date('Y-m-d');

$minutesSince0Time = getDurationInMinutes(date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'));
//var_dump(date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'), $minutesSince0Time);exit;
$positionNow = ($minuteSize * $minutesSince0Time) + $timeLineElementSize;

//$bgColors = array('#feceea', '#fef1d2', '#a9fdd8', '#d7f8ff', '#cec5fa');

$bgColors = array('#222222', '#333333', '#444444', '#555555');
_ob_start();
//var_dump($minuteSize, $minutes,$positionNow);exit;
$animateJson = '{scrollLeft: $(\'#positionNow\').position().left - '.($timeLineElementSize + 50);
if (!empty($videos_id)) {
    $animateJson .= ', scrollTop: ($(\'#video_'.$videos_id.'\').offset().top) - 100';
}
$animateJson .= '}';
?><!DOCTYPE html>
<html>
    <head>
        <title>EPG</title>

        <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <style>
            *{
                text-transform: uppercase;
            }
            body{
                background-color: #000000AA;
                font-size: <?php echo $fontSize; ?>px;
            }
            div.list > div,
            div.header{
                width: <?php echo $timeLineElementSize; ?>px;
                padding: 5px <?php echo $paddingSize; ?>px;
                text-align: center;
                align-content: center;
                overflow: hidden;
                height: <?php echo ($fontSize * 4) - 2; ?>px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                background-color: #000000;
                color: #FFF;
                cursor: pointer;
            }
            div.timeline > div.list > div,
            .programs > div.list > div{
                border-right: solid #777 1px;
                display:grid;
            }
            div.header{
                position: absolute;
                left: 0;
                font-weight: bolder;
                z-index: 10;
                padding: 0 10px;
                display:flex;
                align-items:center;
                border: solid #CCC 1px;
                border-width: 0 1pz 1px 0;
            }
            div.timeline{
                z-index: 20;
            }
            div.list{
                margin-left: <?php echo $timeLineElementSize; ?>px;
            }
            div.list > div{
                position: absolute;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                line-height: 1.1;
            }
            #positionNow{
                position: absolute;
                top: 0;
                background-color: #FF000077;
                width: 5px;
                height: 100%;
                z-index: 5;
            }
            .timeline, #programsList, .programs{
                position: relative;
                height: <?php echo ($fontSize * 4); ?>px;
                margin: 0;
            }
            body > div.container-fluid{
                padding: 0;
            }
            .duration{
                font-size: 0.8em;
                opacity: 0.7;
            }
            .programs{
                opacity: 0.7;
            }
            .programs.active, .programs:hover{
                opacity: 1;
            }
            .programs.active .list > div, .programs.active .header{
                border: 1px solid green;
                animation: glowBox 1s infinite alternate;
            }
            .programs:hover .list > div, .programs:hover .header{
                border: 1px solid blue;
            }
            .glowBox{
                animation: glowBox 1s infinite alternate;
            }

            @keyframes glowBox {
                from {
                    color: #DFD;
                    box-shadow:
                        0 0 1px #050,
                        0 0 2px #070,
                        0 0 3px #670,
                        0 0 4px #670;
                }
                to {
                    color: #FFF;
                    box-shadow:
                        0 0 2px #020,
                        0 0 5px #090,
                        0 0 10px #0F0,
                        0 0 15px #BF0,
                        0 0 20px #B6FF00;
                }
            }
            <?php
            foreach ($bgColors as $key => $value) {
                $n = $key + 1;
                echo "div.programs > div.list > div:nth-child({$n}n){"
                . "background-color: {$value};"
                . "color: #FFF;"
                //. "font-weight: bolder;"
                . "text-shadow: 1px 1px 5px {$value},"
                . "2px 0 5px #000,"
                . "0 2px 5px #000,"
                . "-2px -2px 5px #000, "
                . "-2px 0 5px #000, "
                . "0 -2px 5px #000, "
                . "-2px -2px 5px #000, "
                . "2px 2px 5px #000;"
                . "}";
            }
            ?>
            .finished{
                opacity: 0.4;
                background-color: #00000077 !important;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="timeline">
                <div class="list">
                    <?php
                    $lastStopDate = $maxDate;
                    //var_dump($lastStopDate);exit;
                    $maxDate = 0;
                    $count = 0;
                    $countElements = 0;
                    while ($lastStopDate > $maxDate && $count < 600) {
                        $tomorrowDate = date('Y-m-d', strtotime("+{$count} days"));
                        for ($i = 0; $i < 24; $i++) {
                            $hour = $i;
                            $amPm = 'AM';
                            if ($i === 12) {
                                $amPm = 'PM';
                            } else if ($i > 12) {
                                $hour = $i - 12;
                                $amPm = 'PM';
                            }
                            $hour = sprintf("%02d", $hour);

                            for ($j = 0; $j < 60; $j += $timeLineElementMinutes) {
                                $minutes = sprintf("%02d", $j);
                                if (empty($count)) {
                                    $text = "<div>{$hour}:{$minutes} {$amPm}</div>";
                                } else {
                                    $text = "<small>{$tomorrowDate}</small><div>{$hour}:{$minutes} {$amPm}</div>";
                                }
                                $left = ($countElements * $timeLineElementSize) + $timeLineElementSize;
                                echo "<div style=\"width: {$timeLineElementSize}px; left: {$left}px;\">{$text}</div>";

                                setMaxDate("$tomorrowDate $i:$minutes:00");
                                $countElements++;
                                if ($lastStopDate < $maxDate) {
                                    break;
                                }
                            }
                            if ($lastStopDate < $maxDate) {
                                break;
                            }
                        }
                        $count++;
                    }
                    ?>
                </div>
            </div>
            <div id="programsList">
                <?php
                $cstartTotal = microtime(true);
                foreach ($channelsList as $key => $channel) {
                    $cstart = microtime(true);
                    createEPG($channel);
                    $cend = microtime(true) - $cstart;
                    echo PHP_EOL . "<!-- {$key}=>{$cend} seconds -->" . PHP_EOL;
                }
                $cendTotal = microtime(true) - $cstartTotal;
                ?>
            </div>
            <?php
            echo PHP_EOL . "<!-- programsListTotal=>{$cendTotal} seconds -->" . PHP_EOL;
            ?>
        </div>
        <div id="positionNow" style="left: <?php echo $positionNow; ?>px;"></div>
        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('view/bootstrap/js/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                setPositionNow();
                setInterval(function () {
                    setPositionNow();
                }, 1000);
                $(window).scroll(function () {
                    $('div.header').css({
                        'left': $(this).scrollLeft()
                    });
                    $('#positionNow, div.timeline').css({
                        'top': $(this).scrollTop()
                    });
                });
                setTimeout(function () {
                    goToPositionNow();
                }, 1000);
            }
            );
            function setPositionNow() {
                var left = parseFloat($('#positionNow').css("left"));
                var newLeft = (left +<?php echo $secondSize; ?>);
                $('#positionNow').css("left", newLeft + 'px');
            }

            function goToPositionNow() {
                $('html, body').animate(<?php echo $animateJson; ?>, 1000);
            }
        </script>
    </body>
</html>
<!-- <?php echo date('Y-m-d H:i:s'); ?> -->
<!-- <?php echo date_default_timezone_get(); ?> -->
<!-- minutesSince0Time=<?php echo $minutesSince0Time; ?> -->
<?php
$_end = microtime(true) - $_start;
?>
<!-- seconds to complete=<?php echo $_end; ?> -->
<!-- videos_id=<?php echo $videos_id; ?> -->
<?php
$content = _ob_get_clean();
ObjectYPT::setCache($cacheNameEpgPage, $content); // 1 hour
echo $content;
?>
