<?php
header('Content-Type: application/json');
$cachedFile = '../videos/cache/version'.(empty($_GET['version'])?1:0).'.cache';

if (empty($_GET['modified']) && file_exists($cachedFile)) {
    $content = file_get_contents($cachedFile);
    $json = json_decode($content);
    if (!empty($json)) {
        $json->cache = filectime($cachedFile);
        echo json_encode($json);
        exit;
    }
}

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';

$obj = new stdClass();

$obj->id = getPlatformId();
$obj->title = $config->getWebSiteTitle();
$obj->url = $global['webSiteRootURL'];
$obj->language = $config->getLanguage();
$obj->version = $config->getVersion();
$obj->date = date("Y-m-d H:i:s");
$obj->MySQLDate = getMySQLDate();
$obj->version = $config->getVersion();
$obj->plugins = Plugin::getAvailablePluginsBasic();
///getTotalVideos($status = Video::SORT_TYPE_VIEWABLE, $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $type = '')
// SECURITY REVIEW (2026-08-19): reported as disclosing unlisted/group-restricted video counts to
// anonymous callers via ignoreGroup=true/showUnlisted=true — NOT a vulnerability / DO NOT FIX.
// "Unlisted" is security-through-obscurity by design (same threat model as a guessable sequential
// ID), not an access-control boundary; an attacker can already reach the same video by iterating
// /video/{id}/... directly, with no dependency on this endpoint. See Section 23.1 of
// .github/prompts/avideo-security-advisory-triage.prompt.md before re-proposing a fix here.
$obj->totalVideos = Video::getTotalVideos('', false, true, true);
$obj->totalUsers = User::getTotalUsers(true, 'a');
$obj->totalChannels = Channel::getTotalChannels();;
$obj->plugins = Plugin::getAvailablePluginsBasic();
if (empty($_GET['version'])) {
    $obj->videos = [];
    //$_GET['modified'] = "2018-03-13 15:46:57";
    $_REQUEST['rowCount'] = 100;
    // SECURITY REVIEW (2026-08-19): see comment above on totalVideos — same DO NOT FIX reasoning
    // applies to this listing (ignoreGroup=true, showUnlisted=true).
    $videos = Video::getAllVideos('', false, true, [], false, true, true, false, null);

    foreach ($videos as $key => $value) {
        $vid = new stdClass();
        $vid->id = $value['id'];
        $vid->title = $value['title'];
        $vid->clean_title = $value['clean_title'];
        $vid->views_count = $value['views_count'];
        $vid->category_name = $value['category'];
        $vid->likes = $value['likes'];
        $vid->dislikes = $value['dislikes'];
        $vid->modified = $value['videoModified'];
        $vid->duration = $value['duration'];
        //$vid->description = $value['description'];
        $vid->description = '';
        $vid->type = $value['type'];
        $vid->image_url = Video::getImageFromFilename($value['filename']);
        $obj->videos[] = $vid;
    }
}
$obj->totalVideosResponse = is_array($obj->videos)?count($obj->videos):0;
$json = _json_encode($obj);
_file_put_contents($cachedFile, $json);
if(empty($json)){
    echo 'ERROR';
}else{
    echo $json;
}
