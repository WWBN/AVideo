<?php
//header('Content-Type: application/json');
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if(empty($_REQUEST['videos_id'])){
    $_REQUEST['videos_id'] = intval($_REQUEST['videoAutocomplete']);
}
$obj->videos_id = $_REQUEST['videos_id'];

if(!User::isAdmin() && !Video::canEdit($_REQUEST['videos_id'])){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}
forbidIfIsUntrustedRequest('Bookmark::bookmarkSave');

$o = new BookmarkTable(@$_REQUEST['id']);
// An existing bookmark can only be changed by someone who can edit the video it belongs to now,
// not only the destination video (GHSA-w5xw-88j8-fmwp), same check as bookmarkDelete.json.php
$existingVideos_id = $o->getVideos_id();
if (!empty($existingVideos_id) && !User::isAdmin() && !Video::canEdit($existingVideos_id)) {
    forbiddenPage('Bookmark::bookmarkSave: you cannot edit this bookmark', true);
}
$o->setName($_REQUEST['name']);
$o->setTimeInSeconds($_REQUEST['timeInSeconds']);
$o->setVideos_id($_REQUEST['videos_id']);

if($id = $o->save()){
    $obj->error = false;
    $obj->msg = __('Saved');
    $obj->bookmarks = BookmarkTable::getAllFromVideo($_REQUEST['videos_id']);
}


echo json_encode($obj);
