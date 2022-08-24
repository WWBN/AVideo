<?php
require_once '../../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/EpgParser.php';

$epgs = array();
$videos = Video::getAllActiveEPGs();
foreach ($videos as $video) {
    if (!isValidURL($video['epg_link'])) {
        continue;
    }
    $epgs[] = $video;
}

$_MaxDaysFromNow = strtotime('+24 hours');

$channelsList = array();
foreach ($epgs as $epg) {
    $this_videos_id = $epg['id'];
    $programCacheName = '/program_' . md5($epg['epg_link']);
    $timeout = 600; //10 min
    $programData = ObjectYPT::getCache($programCacheName, $timeout);
    if (empty($programData)) {
        _error_log("EPG program expired creating again videos_id={$this_videos_id} " . $programCacheName);
        //var_dump($epg['epg_link']);exit;
        $Parser = new \buibr\xmlepg\EpgParser();
        $Parser->setURL($epg['epg_link']);
        $Parser->temp_dir = getCacheDir();
        try {
            $Parser->parseURL();
            $epgData = $Parser->getEpgdata();
            $channels = $Parser->getChannels();
            foreach ($channels as $key => $value) {
                $channels[$key]['epgData'] = array();
                foreach ($epgData as $key2 => $program) {
                    if ($program['channel'] != $value['id']) {
                        continue;
                    }
                    $timeWillStart = strtotime($program['start']);
                    if ($timeWillStart > $_MaxDaysFromNow) {
                        unset($epgData[$key2]);
                        continue;
                    }
                    $minutes = getDurationInMinutes(date('Y-m-d 00:00:00'), $program['stop']);
                    //var_dump(date('Y-m-d 00:00:00'), $program['stop'], $minutes);
                    if ($minutes > 0) {
                        $channels[$key]['epgData'][] = $program;
                    }
                    unset($epgData[$key2]);
                }
                //var_dump($channels[$key]);
                if (!empty($channels[$key])) {
                    usort($channels[$key]['epgData'], "cmpPrograms");
                    $channels[$key]['videos_id'] = $this_videos_id;
                    if (!empty($epg['title'])) {
                        $channels[$key]['display-name'] = safeString($epg['title']);
                    }
                    $channelsList[] = $channels[$key];
                    //var_dump($channelsList[0]);exit;
                }
            }
            $file = ObjectYPT::setCache($programCacheName, $channelsList);
            _error_log("EPG program cache created videos_id={$this_videos_id} " . json_encode($file));
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
usort($channelsList, "cmpChannels");
header('Content-Type: application/json');
echo json_encode($channelsList);
exit;
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
