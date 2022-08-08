<?php
require_once '../../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/EpgParser.php';

$epgs = array();

$epgs[] = 'https://dy9x7so5g8z3m.cloudfront.net/xmltv/19647868?scheduleId=188';
$epgs[] = 'https://app2.evrideo.com/api/reports/epg?channelUid=8808fff1-2262-4c92-8cf4-359adbb692ca&durationHours=96&outputType=xmlTv2&minDurationSecs=300&encodingCodePage=65001&wrapTextInCDATA=false';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/a+_cinema_15.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/americana_television_72.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/blk_cinema_network_256.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/balle_balle_227.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/box_cinema_254.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/box_playlist_63.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/britbash_147.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/christian_life_124.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/cinema_india_257.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/comedy_classics_80.xml';
$epgs[] = 'https://sotalcloud.blob.core.windows.net/publicstorage/xmltv/cowboy_classics_58.xml';

$fontSize = 18;
$timeLineElementMinutes = 30;
$timeLineElementSize = 150;
$paddingSize = 10;
$minimumWidth = 10;

$minuteSize = $timeLineElementSize / $timeLineElementMinutes;
$secondSize = $minuteSize / 60;

$cacheName = 'epg';

$epgData = ObjectYPT::getCache($cacheName, 3600); // 1 hour

$channelsList = array();
if (empty($epgData)) {
    foreach ($epgs as $epg) {
        //var_dump($epg, $_channels);exit;
        $Parser = new \buibr\xmlepg\EpgParser();
        $Parser->setURL($epg);
        $Parser->temp_dir = getCacheDir();
        try {
            $Parser->parseURL();
            $epgData = $Parser->getEpgdata();
            $channels = $Parser->getChannels();

            //$Parser->setTargetTimeZone('Europe/Skopje');
            // $Parser->setChannelfilter('prosiebenmaxx.de'); //optional
            // $Parser->setIgnoreDescr('Keine Details verfügbar.'); //optional
            foreach ($channels as $key => $value) {
                $channels[$key]['epgData'] = array();
                foreach ($epgData as $key2 => $program) {
                    if ($program['channel'] != $value['id']) {
                        continue;
                    }
                    $minutes = getDurationInMinutes(date('Y-d-m 00:00:00'), $program['stop']);
                    if ($minutes > 0) {
                        $channels[$key]['epgData'][] = $program;
                        setMinDate($program['start']);
                        setMaxDate($program['stop']);
                    }
                    unset($epgData[$key2]);
                }
                usort($channels[$key]['epgData'], "cmpPrograms");
                $channelsList[] = $channels[$key];
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
    usort($channelsList, "cmpChannels");
    
    $_channels = $channelsList;
    $_channelsMinDate = $minDate;
    $_channelsMaxDate = $maxDate;
    $epgData = new stdClass();
    $epgData->channels = $_channels;
    $epgData->channelsMinDate = $_channelsMinDate;
    $epgData->channelsMaxDate = $_channelsMaxDate;
    
    //var_dump($epgData);exit;
    ObjectYPT::setCache($cacheName, $epgData);
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
    //var_dump(date('Y-m-d H:i:s',$timeStart), date('Y-m-d H:i:s',$timeStop));
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
    global $minuteSize, $Date;
    $channel = object_to_array($channel);    
    $displayname = $channel['display-name'];
    $channelId = $channel['id'];
    $firstProgram = $channel['epgData'][0];
    ?>
    <div class="programs">
        <div class="header">
            <?php echo $displayname; ?>
        </div>
        <div class="list">
            <?php
            foreach ($channel['epgData'] as $key => $program) {
                $minutesSinceZeroTime = getDurationInMinutes("{$Date} 00:00:00", $program['start']);
                if ($minutesSinceZeroTime < 0) {
                    continue;
                }
                $minutes = getDurationInMinutes($program['start'], $program['stop']);
                $left = ($minuteSize * $minutesSinceZeroTime) + $timeLineElementSize;
                $width = ($minuteSize * $minutes);
                if ($width <= $minimumWidth) {
                    $text = "<!-- too small $width -->";
                } else {
                    $startTime = date('m-d H:i', strtotime($program['start']));
                    $stopTime = date('m-d H:i', strtotime($program['stop']));
                    $text = "{$program['title']}<div><small class=\"duration\">{$minutes} Min</small></div>";
                }

                echo "<div style=\"width: {$width}px; left: {$left}px;\" start=\"{$program['start']}\" stop=\"{$program['stop']}\" minutes=\"{$minutes}\" minutesSinceZeroTime=\"{$minutesSinceZeroTime}\">{$text}</div>";
            }
            ?>
        </div>
    </div>
    <?php
}

$Date = date('Y-m-d');

$minutes = getDurationInMinutes(date('Y-d-m 00:00:00'), date('Y-d-m H:i:s'));
$positionNow = ($minuteSize * $minutes) + $timeLineElementSize;

$bgColors = array('#feceea', '#fef1d2', '#a9fdd8', '#d7f8ff', '#cec5fa');

//var_dump($minuteSize, $minutes,$positionNow);exit;
?><!DOCTYPE html>
<html>
    <head>
        <title>EPG</title>

        <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <style>
            body{
                background-color: #777;
                font-size: <?php echo $fontSize; ?>px;
            }
            div.list > div,
            div.header{
                width: <?php echo $timeLineElementSize; ?>px;
                padding: 5px <?php echo $paddingSize; ?>px;
                text-align: center;
                align-content: center;
                overflow: hidden;
                height: <?php echo ($fontSize*3)-2 ;?>px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                background-color: #FFF;
            }
            div.timeline > div.list > div{
                border-right: solid #777 1px;
            }
            div.header{
                position: absolute;
                left: 0;
                font-weight: bolder;
                background-color: #FFF;
                z-index: 10;
                padding: 0 10px;
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
                height: <?php echo ($fontSize*3);?>px;
                margin: 0;
            }
            body > div.container-fluid{
                padding: 0;
            }
            .duration{
                font-size: 0.8em;
            }
            <?php
            foreach ($bgColors as $key => $value) {
                $n = $key + 1;
                echo "div.programs > div.list > div:nth-child({$n}n){"
                . "background-color: {$value}77;"
                . "color: #FFF;"
                //. "font-weight: bolder;"
                . "text-shadow: 1px 1px 0 {$value},"
                        . "2px 0 0 #000,"
                        . "0 2px 0 #000,"
                        . "-2px -2px 0 #000, "
                        . "-2px 0 0 #000, "
                        . "0 -2px 0 #000, "
                        . "-2px -2px 2px #000, "
                        . "2px 2px 2px #000;"
                . "}";
            }
            ?>

        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="timeline">
                <div class="list">
                    <?php
                    $lastStopDate = $epgData->channelsMaxDate;
                    //var_dump($lastStopDate);exit;
                    $maxDate = 0;
                    $count = 0;
                    $countElements = 0;
                    while ($lastStopDate > $maxDate && $count < 600) {
                        $tomorrowDate = date('Y-m-d', strtotime("+{$count} days"));
                        for ($i = 0; $i < 24; $i++) {
                            $hour = $i;
                            $amPm = 'AM';
                            if ($i > 12) {
                                $hour = $i - 12;
                                $amPm = 'PM';
                            }
                            $hour = sprintf("%02d", $hour);

                            for ($j = 0; $j < 60; $j += $timeLineElementMinutes) {
                                $minutes = sprintf("%02d", $j);
                                $text = "<small>{$tomorrowDate}</small><div>{$hour}:{$minutes} {$amPm}</div>";
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
                foreach ($epgData->channels as $channel) {
                    createEPG($channel);
                }
                ?>
            </div>
        </div>
        <div id="positionNow" style="left: <?php echo $positionNow; ?>px;"></div>
        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('view/bootstrap/js/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                setInterval(function () {
                    var left = parseFloat($('#positionNow').css("left"));
                    var newLeft = (left +<?php echo $secondSize; ?>);
                    $('#positionNow').css("left", newLeft + 'px');
                    //console.log('positionNow', newLeft);
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
                    $(document).scrollLeft($('#positionNow').position().left -<?php echo $timeLineElementSize + 50; ?>);
                }, 1000);
            });
        </script>
    </body>
</html>