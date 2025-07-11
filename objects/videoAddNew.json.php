<?php
//error_reporting(0);
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
allowOrigin();
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    die('{"error":"1 ' . __("Permission denied") . '"}');
}

$msg = '';
$info = $infoObj = '';
require_once 'video.php';

if (!empty($_POST['id'])) {
    if (!Video::canEdit($_POST['id']) && !Permissions::canModerateVideos()) {
        die('{"error":"2 ' . __("Permission denied") . '"}');
    }
}

/*
if (!is_writable("{$global['systemRootPath']}vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer")) {
    die('{"error":"Directory ' . $global['systemRootPath'] . 'vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer not writable, please chmod to 777 "}');
}
 *
 */

_error_log('videoAddNew.json.php: start '.getRealIpAddr().' '.$_SERVER['HTTP_USER_AGENT']);

TimeLogStart(__FILE__);

$obj = new Video($_POST['title'], "", @$_POST['id'], true);

TimeLogEnd(__FILE__, __LINE__);

$obj->setClean_Title(@$_POST['clean_title']);
$audioLinks = ['mp3', 'ogg'];
$videoLinks = ['mp4', 'webm', 'm3u8'];

$startTime = microtime(true);
$rowsPath = array();

function getElapsedTime()
{
    global $startTime;
    $nowTime = microtime(true);
    $formatedTime = number_format($nowTime - $startTime, 2);
    $startTime = $nowTime;
    return $formatedTime;
}

$isNewVideo = empty($_POST['id']);
TimeLogEnd(__FILE__, __LINE__);
if (!_empty($_POST['videoLink'])) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    //var_dump($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));exit;
    $path_parts = pathinfo($_POST['videoLink']);
    //$extension = strtolower(@$path_parts["extension"]);
    $filebasename = explode('?', $path_parts['basename']);
    $extension = getExtension($_POST['videoLink']);

    if (!$isNewVideo) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $title = $obj->getTitle();
        $isNewVideo = empty($title) || preg_match('/^[a-f0-9]{13}$/', $title);
        _error_log('videoAddNew: getTitle ' . $obj->getTitle());
    } else {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    }

    //var_dump($path_parts, $extension);exit;


    if ($isNewVideo) {
        if (!in_array($extension, $audioLinks) && !in_array($extension, $videoLinks)) {
            $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
            $getLinkInfo = $config->getEncoderURL() . "getLinkInfo/" . base64_encode($_POST['videoLink']);
            _error_log('videoAddNew: ' . $getLinkInfo);
            $info = url_get_contents($getLinkInfo, '', 180, true);
            $infoObj = _json_decode($info);
            $paths = Video::getNewVideoFilename();
            $filename = $paths['filename'];
            $filename = $obj->setFilename($filename);
            if (is_object($infoObj)) {
                $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
                $obj->setTitle($infoObj->title);
                $obj->setClean_title($infoObj->title);
                $obj->setDuration($infoObj->duration);
                $obj->setDescription($infoObj->description);
                $imgFile = $global['systemRootPath'] . "videos/{$filename}/{$filename}.jpg";
                _error_log('videoAddNew save image: ' . $imgFile);
                _file_put_contents($imgFile, base64_decode($infoObj->thumbs64));
            }
            $_POST['videoLinkType'] = Video::$videoTypeEmbed;
        } else {
            $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
            $paths = Video::getNewVideoFilename();
            $filename = $paths['filename'];
            $filename = $obj->setFilename($filename);
            $obj->setTitle($path_parts["filename"]);
            $obj->setClean_title($path_parts["filename"]);
            $obj->setDuration("");
            $obj->setDescription(@$_POST['description']);
            $_POST['videoLinkType'] = Video::$videoTypeLinkVideo;
        }
    }

    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setVideoLink($_POST['videoLink']);
    if (isValidURL($_POST['videoLink'])) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $obj->setType(Video::$videoTypeLinkVideo);
    }
    if (empty($_POST['epg_link']) || isValidURL($_POST['epg_link'])) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $obj->setEpg_link($_POST['epg_link']);
    }

    if (in_array($extension, $audioLinks) || in_array($extension, $videoLinks)) {
        if (in_array($extension, $audioLinks)) {
            $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
            $obj->setType(Video::$videoTypeLinkAudio);
        } else {
            $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
            $obj->setType(Video::$videoTypeLinkVideo);
        }
    } elseif (!empty($obj->getType())) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $obj->setType(Video::$videoTypeEmbed);
    }

    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
} elseif (!empty($obj->getType()) && ($obj->getType() == Video::$videoTypeVideo || $obj->getType() == Video::$videoTypeSerie || $obj->getType() == Video::$videoTypeAudio)) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setVideoLink("");
    _error_log('videoAddNew videoAddNew.json.php: setVideoLink false');
}

TimeLogEnd(__FILE__, __LINE__);
if (!empty($_POST['isArticle'])) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setType(Video::$videoTypeArticle);
    $paths = Video::getNewVideoFilename();
    $filename = $paths['filename'];
    $filename = $obj->setFilename($filename);
}

if (empty($_POST['id'])) {
    if (!empty($_POST['videoLinkType'])) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $obj->setType($_POST['videoLinkType']);
    }
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setAutoStatus(Video::STATUS_DRAFT);
}

TimeLogEnd(__FILE__, __LINE__);
$obj->setNext_videos_id($_POST['next_videos_id']);
if (isset($_POST['description'])) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setDescription($_POST['description']);
}
if (empty($advancedCustomUser->userCanNotChangeCategory) || Permissions::canModerateVideos()) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setCategories_id($_POST['categories_id']);
}

if (empty($advancedCustomUser->userCanNotChangeUserGroup) || Permissions::canModerateVideos()) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    if (_empty($_REQUEST['public'])) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $obj->setVideoGroups(empty($_POST['videoGroups']) ? [] : $_POST['videoGroups']);
    } else if (!empty($obj->getId())) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        UserGroups::deleteGroupsFromVideo($obj->getId());
        $obj->setVideoGroups([]);
        //var_dump($obj->getId(), Video::getUserGroups($obj->getId()));exit;
    }
}

$rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
$externalOptions = new stdClass();

$externalOptionsOriginal = [];
if (!empty($obj->getExternalOptions())) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $externalOptionsOriginal = json_decode($obj->getExternalOptions());
    if (!empty($externalOptionsOriginal) && is_object($externalOptionsOriginal)) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        foreach ($externalOptionsOriginal as $key => $value) {
            $externalOptions->$key = $value;
        }
    }
}

$externalOptionsPost = json_decode(@$_POST['externalOptions']);
if (!empty($externalOptionsPost) && is_object($externalOptionsPost)) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    foreach ($externalOptionsPost as $key => $value) {
        $externalOptions->$key = $value;
    }
}

TimeLogEnd(__FILE__, __LINE__);
$obj->setCan_download(@$_POST['can_download']);
$obj->setCan_share(@$_POST['can_share']);
$obj->setOnly_for_paid(@$_POST['only_for_paid']);
$obj->setVideo_password(@$_POST['video_password']);
$obj->setTrailer1(@$_POST['trailer1']);
$obj->setRrating(@$_POST['rrating']);
$obj->setMade_for_kids(@$_POST['made_for_kids']);
$obj->setExternalOptions($externalOptions);

if (!empty($_REQUEST['users_id_company'])) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setUsers_id_company(@$_REQUEST['users_id_company']);
}

if ($advancedCustomUser->userCanChangeVideoOwner || Permissions::canModerateVideos() || Users_affiliations::isUserAffiliateOrCompanyToEachOther($obj->getUsers_id(), $_POST['users_id'])) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->setUsers_id($_POST['users_id']);
}
if (Permissions::canAdminVideos()) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    if (!empty($_REQUEST['created'])) {
        $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
        $obj->setCreated($_REQUEST['created']);
    }
}

TimeLogEnd(__FILE__, __LINE__);
$resp = $obj->save(true);

if (!empty($resp)) {
    if (!empty($_POST['videoStatus'])) {
        $found = false;
        foreach ($statusThatTheUserCanUpdate as $key => $value) {
            if ($_POST['videoStatus'] == $value[0]) {
                $found = true;
            }
        }
        if ($found) {
            $obj = new Video('', '', @$_POST['id'], true);
            $obj->setStatus(@$_POST['videoStatus']);
        }
    }
}else{
    $msg = $global['lastBeforeSaveVideoMessage'];
}

if (isset($_REQUEST['playlists_id'])) {
    if (!PlayLists::canAddVideoOnPlaylist($resp)) {
        Playlists::addVideo($resp, $_REQUEST['playlists_id']);
    }
}

// if is a new embed video
if (empty($_POST['id']) && ($obj->getType() == Video::$videoTypeEmbed || $obj->getType() == Video::$videoTypeLinkVideo)) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    AVideoPlugin::afterNewVideo($resp);
}

$rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
if (Permissions::canAdminVideos()) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    $obj->updateViewsCount($_REQUEST['views_count']);
}

$rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());

_error_log('Saving video start');
AVideoPlugin::saveVideosAddNew($_POST, $resp);
_error_log('Saving video end');

$rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
TimeLogEnd(__FILE__, __LINE__);
$obj = new stdClass();

$obj->status = !empty($resp);
$obj->msg = $msg;
$obj->info = json_encode($info);
$obj->infoObj = json_encode($infoObj);
$obj->videos_id = intval($resp);
$obj->video = Video::getVideoLight($obj->videos_id, true);
$obj->isNewVideo = $isNewVideo;
if ($obj->video['status'] == Video::STATUS_ACTIVE) {
    $rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
    _error_log('clearFirstPageCache start');
    $obj->clearFirstPageCache = clearFirstPageCache();
    _error_log('clearFirstPageCache end');
    //clearAllUsersSessionCache();
}
// it cannot clear async otherwise it will cause issues on the videos manager list.
$obj->clearCache = Video::clearCache($obj->videos_id, false, false, false);
$rowsPath[] = array('line' => __LINE__, 'ElapsedTime' => getElapsedTime());
$obj->rowsPath = $rowsPath;
TimeLogEnd(__FILE__, __LINE__);
_error_log('video add new done end');
echo json_encode($obj);
