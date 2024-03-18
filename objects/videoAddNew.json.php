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

$rowsPath = array();

TimeLogEnd(__FILE__, __LINE__);
if (!_empty($_POST['videoLink'])) {
    $rowsPath[] = __LINE__;
    //var_dump($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));exit;
    $path_parts = pathinfo($_POST['videoLink']);
    //$extension = strtolower(@$path_parts["extension"]);
    $filebasename = explode('?', $path_parts['basename']);
    $extension = getExtension($_POST['videoLink']);
    //var_dump($path_parts, $extension);exit;
    if (empty($_POST['id']) && !(in_array($extension, $audioLinks) || in_array($extension, $videoLinks))) {
        $rowsPath[] = __LINE__;
        $getLinkInfo = $config->getEncoderURL() . "getLinkInfo/" . base64_encode($_POST['videoLink']);
        _error_log('videoAddNew: ' . $getLinkInfo);
        $info = url_get_contents($getLinkInfo, '', 180, true);
        $infoObj = _json_decode($info);
        $paths = Video::getNewVideoFilename();
        $filename = $paths['filename'];
        $filename = $obj->setFilename($filename);
        if (is_object($infoObj)) {
            $rowsPath[] = __LINE__;
            $obj->setTitle($infoObj->title);
            $obj->setClean_title($infoObj->title);
            $obj->setDuration($infoObj->duration);
            $obj->setDescription($infoObj->description);
            $imgFile = $global['systemRootPath'] . "videos/{$filename}/{$filename}.jpg";
            _error_log('videoAddNew save image: ' . $imgFile);
            _file_put_contents($imgFile, base64_decode($infoObj->thumbs64));
        }
        $_POST['videoLinkType'] = Video::$videoTypeEmbed;
    } elseif (empty($_POST['id'])) {
        $rowsPath[] = __LINE__;
        $paths = Video::getNewVideoFilename();
        $filename = $paths['filename'];
        $filename = $obj->setFilename($filename);
        $obj->setTitle($path_parts["filename"]);
        $obj->setClean_title($path_parts["filename"]);
        $obj->setDuration("");
        $obj->setDescription(@$_POST['description']);
        $_POST['videoLinkType'] = Video::$videoTypeLinkVideo;
    }
    $rowsPath[] = __LINE__;
    $obj->setVideoLink($_POST['videoLink']);
    if(isValidURL($_POST['videoLink'])){
        $rowsPath[] = __LINE__;
        $obj->setType(Video::$videoTypeLinkVideo);
    }
    if (empty($_POST['epg_link']) || isValidURL($_POST['epg_link'])) {
        $rowsPath[] = __LINE__;
        $obj->setEpg_link($_POST['epg_link']);
    }

    if (in_array($extension, $audioLinks) || in_array($extension, $videoLinks)) {
        if (in_array($extension, $audioLinks)) {
            $rowsPath[] = __LINE__;
            $obj->setType(Video::$videoTypeLinkAudio);
        } else {
            $rowsPath[] = __LINE__;
            $obj->setType(Video::$videoTypeLinkVideo);
        }
    } elseif (!empty($obj->getType())) {
        $rowsPath[] = __LINE__;
        $obj->setType(Video::$videoTypeEmbed);
    }

    $rowsPath[] = __LINE__;
} elseif (!empty($obj->getType()) && ($obj->getType() == Video::$videoTypeVideo || $obj->getType() == Video::$videoTypeSerie || $obj->getType() == Video::$videoTypeAudio)) {
    $rowsPath[] = __LINE__;
    $obj->setVideoLink("");
}

if (empty($_POST['id'])) {
    if (!empty($_POST['videoLinkType'])) {
        $rowsPath[] = __LINE__;
        $obj->setType($_POST['videoLinkType']);
    }
    $rowsPath[] = __LINE__;
    $obj->setAutoStatus(Video::$statusActive);
}

TimeLogEnd(__FILE__, __LINE__);
if (!empty($_POST['isArticle'])) {
    $rowsPath[] = __LINE__;
    $obj->setType(Video::$videoTypeArticle);
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
    $rowsPath[] = __LINE__;
    $obj->setDescription($_POST['description']);
}
if (empty($advancedCustomUser->userCanNotChangeCategory) || Permissions::canModerateVideos()) {
    $rowsPath[] = __LINE__;
    $obj->setCategories_id($_POST['categories_id']);
}

if (empty($advancedCustomUser->userCanNotChangeUserGroup) || Permissions::canModerateVideos()) {
    $rowsPath[] = __LINE__;
    if (_empty($_REQUEST['public'])) {
        $rowsPath[] = __LINE__;
        $obj->setVideoGroups(empty($_POST['videoGroups']) ? [] : $_POST['videoGroups']);
    } else if (!empty($obj->getId())) {
        $rowsPath[] = __LINE__;
        UserGroups::deleteGroupsFromVideo($obj->getId());
        $obj->setVideoGroups([]);
        //var_dump($obj->getId(), Video::getUserGroups($obj->getId()));exit;
    }
}

$rowsPath[] = __LINE__;
$externalOptions = new stdClass();

$externalOptionsOriginal = [];
if (!empty($obj->getExternalOptions())) {
    $rowsPath[] = __LINE__;
    $externalOptionsOriginal = json_decode($obj->getExternalOptions());
    if (!empty($externalOptionsOriginal) && is_object($externalOptionsOriginal)) {
        $rowsPath[] = __LINE__;
        foreach ($externalOptionsOriginal as $key => $value) {
            $externalOptions->$key = $value;
        }
    }
}

$externalOptionsPost = json_decode(@$_POST['externalOptions']);
if (!empty($externalOptionsPost) && is_object($externalOptionsPost)) {
    $rowsPath[] = __LINE__;
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
    $rowsPath[] = __LINE__;
    $obj->setUsers_id_company(@$_REQUEST['users_id_company']);
}

if ($advancedCustomUser->userCanChangeVideoOwner || Permissions::canModerateVideos() || Users_affiliations::isUserAffiliateOrCompanyToEachOther($obj->getUsers_id(), $_POST['users_id'])) {
    $rowsPath[] = __LINE__;
    $obj->setUsers_id($_POST['users_id']);
}
if (Permissions::canAdminVideos()) {
    $rowsPath[] = __LINE__;
    if (!empty($_REQUEST['created'])) {
        $rowsPath[] = __LINE__;
        $obj->setCreated($_REQUEST['created']);
    }
}

TimeLogEnd(__FILE__, __LINE__);
$resp = $obj->save(true);
// if is a new embed video
if (empty($_POST['id']) && ($obj->getType() == Video::$videoTypeEmbed || $obj->getType() == Video::$videoTypeLinkVideo)) {
    $rowsPath[] = __LINE__;
    AVideoPlugin::afterNewVideo($resp);
}

if (Permissions::canAdminVideos()) {
    $rowsPath[] = __LINE__;
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
    $rowsPath[] = __LINE__;
    $obj->clearFirstPageCache = clearFirstPageCache();
    //clearAllUsersSessionCache();
}
$obj->rowsPath = $rowsPath;
TimeLogEnd(__FILE__, __LINE__);
echo json_encode($obj);
