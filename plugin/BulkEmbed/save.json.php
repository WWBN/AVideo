<?php

/**
 * Convert ISO 8601 values like PT15M33S
 * to a total value of seconds.
 * 
 * @param string $ISO8601
 */
function ISO8601ToSeconds($ISO8601)
{
    preg_match('/\d{1,2}[H]/', $ISO8601, $hours);
    preg_match('/\d{1,2}[M]/', $ISO8601, $minutes);
    preg_match('/\d{1,2}[S]/', $ISO8601, $seconds);

    $duration = [
        'hours' => $hours ? $hours[0] : 0,
        'minutes' => $minutes ? $minutes[0] : 0,
        'seconds' => $seconds ? $seconds[0] : 0,
    ];

    $hours = substr($duration['hours'], 0, -1);
    $minutes = substr($duration['minutes'], 0, -1);
    $seconds = substr($duration['seconds'], 0, -1);

    $hours = intval(@$hours);
    $minutes = intval(@$minutes);
    $seconds = intval(@$seconds);

    $totalSeconds = ($hours * 60 * 60) + ($minutes * 60) + $seconds;

    return $totalSeconds;
}

function ISO8601ToDuration($ISO8601)
{
    $seconds = ISO8601ToSeconds($ISO8601);
    return secondsToVideoTime($seconds);
}

//error_reporting(0);
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = array();
$obj->playListId = 0;

$objo = AVideoPlugin::getObjectDataIfEnabled('BulkEmbed');

if(!BulkEmbed::canBulkEmbed()){
    $obj->msg[] = __("Permission denied");
    $obj->msg[] = "Plugin disabled";
} else if (!empty($_POST['itemsToSave'])) {

    if (!empty($_POST['playListName'])) {
        require_once $global['systemRootPath'] . 'objects/playlist.php';
        $playList = new PlayList(0);
        $playList->loadFromName($_POST['playListName']);
        $playList->setName($_POST['playListName']);
        $playList->setStatus('private');
        $playList->setUsers_id(User::getId());
        $obj->playListId = $playList->save();
    }

    foreach ($_POST['itemsToSave'] as $value) {
        foreach ($value as $key => $value2) {
            $value[$key] = xss_esc($value2);
        }
        //$info = url_get_contents($config->getEncoderURL() . "getLinkInfo/" . base64_encode($value));
        //$infoObj = _json_decode($info);
        $paths = Video::getNewVideoFilename();
        $filename = $paths['filename'];
        $videos = new Video();
        $videos->setFilename($filename);
        $videos->setTitle($value['title']);
        $videos->setDescription($value['description']);
        $videos->setClean_title($value['title']);
        $videos->setDuration(ISO8601ToDuration($value['duration']));
        
        // Set the original video date if available in the form data
        if (!empty($value['date']) && $objo->useOriginalYoutubeDate) {
            $videos->setCreated($value['date']); // Set the original creation date of the video
        }

        $poster = Video::getPathToFile("{$paths['filename']}.jpg");
        $thumbs = $value['thumbs'];
        if (!empty($thumbs)) {
            $contentThumbs = url_get_contents($thumbs);
            if (!empty($contentThumbs)) {
                make_path($poster);
                $bytes = file_put_contents($poster, $contentThumbs);
                _error_log("thumbs={$thumbs} poster=$poster bytes=$bytes strlen=" . strlen($contentThumbs));
            } else {
                _error_log("ERROR thumbs={$thumbs} poster=$poster");
            }
        } else {
            _error_log("ERROR thumbs={$thumbs} poster=$poster");
        }
        $videos->setVideoLink($value['link']);
        $videos->setType('embed');

        $videos->setStatus('a');
        try {
            $resp = $videos->save(true);
        } catch (Exception $exc) {
            _error_log("First save attempt failed: " . $exc->getMessage());
            try {
                $resp = $videos->save(true);
            } catch (Exception $exc) {
                _error_log("Second save attempt failed: " . $exc->getMessage());
                continue;  // Skip to the next video if saving fails
            }
        }
        

        if (!empty($resp) && !empty($obj->playListId)) {
            $playList = new PlayList($obj->playListId);
            $playList->addVideo($resp, true);
        }

        AVideoPlugin::afterNewVideo($resp);

        AVideoPlugin::saveVideosAddNew($_POST, $resp);

        $obj->msg[] = array('video'=>Video::getVideoLight($resp), 'value'=>$value, 'videos_id'=>$resp);
    }

    $obj->error = false;
}
echo json_encode($obj);
