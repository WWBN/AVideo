<?php

/**
 * Convert ISO 8601 values like PT15M33S
 * to a total value of seconds.
 * 
 * @param string $ISO8601
 */
function ISO8601ToSeconds($ISO8601) {
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

    $toltalSeconds = ($hours * 60 * 60) + ($minutes * 60) + $seconds;

    return $toltalSeconds;
}

function ISO8601ToDuration($ISO8601) {
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


$objo = YouPHPTubePlugin::getObjectDataIfEnabled('BulkEmbed');
if (empty($objo) || ($objo->onlyAdminCanBulkEmbed && !User::isAdmin())) {
    $obj->msg[] = __("Permission denied");
    $obj->msg[] = "Plugin disabled";
} else if (!User::canUpload()) {
    $obj->msg[] = __("Permission denied");
    $obj->msg[] = "User can not upload videos";
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
        //$infoObj = json_decode($info);
        $filename = uniqid("_YPTuniqid_", true);
        $videos = new Video();
        $videos->setFilename($filename);
        $videos->setTitle($value['title']);
        $videos->setDescription($value['description']);
        $videos->setClean_title($value['title']);
        $videos->setDuration(ISO8601ToDuration($value['duration']));
        file_put_contents($global['systemRootPath'] . "videos/{$filename}.jpg", url_get_contents($value['thumbs']));
        $videos->setVideoLink($value['link']);
        $videos->setType('embed');

        $videos->setStatus('a');
        try {
            $resp = $videos->save(true);
        } catch (Exception $exc) {
            try {
                $resp = $videos->save(true);
            } catch (Exception $exc) {
                continue;
            }
        }
        
        if(!empty($resp) && !empty($obj->playListId)){
            $playList = new PlayList($obj->playListId);
            $playList->addVideo($resp, true);
        }

        YouPHPTubePlugin::afterNewVideo($resp);

        YouPHPTubePlugin::saveVideosAddNew($_POST, $resp);

        $obj->msg[] = Video::getVideoLight($resp);
    }

    $obj->error = false;
}
echo json_encode($obj);
