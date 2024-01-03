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

TimeLogStart(__FILE__);

$obj = new Video($_POST['title'], "", @$_POST['id'], true);

TimeLogEnd(__FILE__, __LINE__);

$obj->setClean_Title(@$_POST['clean_title']);
$audioLinks = ['mp3', 'ogg'];
$videoLinks = ['mp4', 'webm', 'm3u8'];
TimeLogEnd(__FILE__, __LINE__);
if (!empty($_POST['videoLink'])) {
    //var_dump($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));exit;
    $path_parts = pathinfo($_POST['videoLink']);
    //$extension = strtolower(@$path_parts["extension"]);
    $filebasename = explode('?', $path_parts['basename']);
    $extension = getExtension($_POST['videoLink']);
    //var_dump($path_parts, $extension);exit;
    if (empty($_POST['id']) && !(in_array($extension, $audioLinks) || in_array($extension, $videoLinks))) {
        $getLinkInfo = $config->getEncoderURL() . "getLinkInfo/" . base64_encode($_POST['videoLink']);
        _error_log('videoAddNew: ' . $getLinkInfo);
        $info = url_get_contents($getLinkInfo, '', 180, true);
        $infoObj = _json_decode($info);
        $paths = Video::getNewVideoFilename();
        $filename = $paths['filename'];
        $filename = $obj->setFilename($filename);
        if (is_object($infoObj)) {
            $obj->setTitle($infoObj->title);
            $obj->setClean_title($infoObj->title);
            $obj->setDuration($infoObj->duration);
            $obj->setDescription($infoObj->description);
            $imgFile = $global['systemRootPath'] . "videos/{$filename}/{$filename}.jpg";
            _error_log('videoAddNew save image: ' . $imgFile);
            _file_put_contents($imgFile, base64_decode($infoObj->thumbs64));
        }
        $_POST['videoLinkType'] = "embed";
    } elseif (empty($_POST['id'])) {
        $paths = Video::getNewVideoFilename();
        $filename = $paths['filename'];
        $filename = $obj->setFilename($filename);
        $obj->setTitle($path_parts["filename"]);
        $obj->setClean_title($path_parts["filename"]);
        $obj->setDuration("");
        $obj->setDescription(@$_POST['description']);
        $_POST['videoLinkType'] = "linkVideo";
    }
    $obj->setVideoLink($_POST['videoLink']);
    if (empty($_POST['epg_link']) || isValidURL($_POST['epg_link'])) {
        $obj->setEpg_link($_POST['epg_link']);
    }

    if (in_array($extension, $audioLinks) || in_array($extension, $videoLinks)) {
        if (in_array($extension, $audioLinks)) {
            $obj->setType('linkAudio');
        } else {
            $obj->setType('linkVideo');
        }
    } elseif (!empty($obj->getType())) {
        $obj->setType('embed');
    }

    if (!empty($_POST['videoLinkType'])) {
        $obj->setType($_POST['videoLinkType']);
    }
    if (empty($_POST['id'])) {
        $obj->setAutoStatus(Video::$statusActive);
    }
} elseif (!empty($obj->getType()) && ($obj->getType() == 'video' || $obj->getType() == 'serie' || $obj->getType() == 'audio')) {
    $obj->setVideoLink("");
}

TimeLogEnd(__FILE__, __LINE__);
if (!empty($_POST['isArticle'])) {
    $obj->setType("article");
    if (empty($_POST['id'])) {
        $obj->setAutoStatus(Video::$statusActive);
    }
    $paths = Video::getNewVideoFilename();
    $filename = $paths['filename'];
    $filename = $obj->setFilename($filename);
}
TimeLogEnd(__FILE__, __LINE__);
$obj->setNext_videos_id($_POST['next_videos_id']);
if (isset($_POST['description'])) {
    $obj->setDescription($_POST['description']);
}
if (empty($advancedCustomUser->userCanNotChangeCategory) || Permissions::canModerateVideos()) {
    $obj->setCategories_id($_POST['categories_id']);
}

if (empty($advancedCustomUser->userCanNotChangeUserGroup) || Permissions::canModerateVideos()) {
    if (_empty($_REQUEST['public'])) {
        $obj->setVideoGroups(empty($_POST['videoGroups']) ? [] : $_POST['videoGroups']);
    } else if (!empty($obj->getId())) {
        UserGroups::deleteGroupsFromVideo($obj->getId());
        $obj->setVideoGroups([]);
        //var_dump($obj->getId(), Video::getUserGroups($obj->getId()));exit;
    }
}

$externalOptions = new stdClass();

$externalOptionsOriginal = [];
if (!empty($obj->getExternalOptions())) {
    $externalOptionsOriginal = json_decode($obj->getExternalOptions());
    if (!empty($externalOptionsOriginal) && is_object($externalOptionsOriginal)) {
        foreach ($externalOptionsOriginal as $key => $value) {
            $externalOptions->$key = $value;
        }
    }
}

$externalOptionsPost = json_decode(@$_POST['externalOptions']);
if (!empty($externalOptionsPost) && is_object($externalOptionsPost)) {
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
$obj->setExternalOptions($externalOptions);

if (!empty($_REQUEST['users_id_company'])) {
    $obj->setUsers_id_company(@$_REQUEST['users_id_company']);
}

if ($advancedCustomUser->userCanChangeVideoOwner || Permissions::canModerateVideos() || Users_affiliations::isUserAffiliateOrCompanyToEachOther($obj->getUsers_id(), $_POST['users_id'])) {
    $obj->setUsers_id($_POST['users_id']);
}
if (Permissions::canAdminVideos()) {
    if (!empty($_REQUEST['created'])) {
        $obj->setCreated($_REQUEST['created']);
    }
}

TimeLogEnd(__FILE__, __LINE__);
$resp = $obj->save(true);
// if is a new embed video
if (empty($_POST['id']) && ($obj->getType() == 'embed' || $obj->getType() == 'linkVideo')) {
    AVideoPlugin::afterNewVideo($resp);
}

if (Permissions::canAdminVideos()) {
    $obj->updateViewsCount($_REQUEST['views_count']);
}

AVideoPlugin::saveVideosAddNew($_POST, $resp);

TimeLogEnd(__FILE__, __LINE__);
$obj = new stdClass();

$obj->status = !empty($resp);
$obj->msg = $msg;
$obj->info = json_encode($info);
$obj->infoObj = json_encode($infoObj);
$obj->videos_id = intval($resp);
$obj->video = Video::getVideoLight($obj->videos_id, true);
if ($obj->video['status'] == Video::$statusActive) {
    $obj->clearFirstPageCache = clearFirstPageCache();
    //clearAllUsersSessionCache();
}

TimeLogEnd(__FILE__, __LINE__);
echo json_encode($obj);
