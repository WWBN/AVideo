<?php

header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$ShortsObj = AVideoPlugin::getDataObjectIfEnabled("Shorts");
$videos = ['data'=>[], 'draw'=>0, 'recordsTotal'=>0, 'recordsFiltered'=>0];
if(!empty($ShortsObj)){
    $shortMaxDurationInSeconds = intval($ShortsObj->shortMaxDurationInSeconds);

    if(empty($shortMaxDurationInSeconds)){
        $shortMaxDurationInSeconds = 60;
    }

    $sort = @$_POST['sort'];
    $rowCount = @$_REQUEST['rowCount'];

    $videos['draw'] = getCurrentPage();

    //$_POST['sort']['created'] = 'DESC';
    $_POST['sort']['trending'] = 1;
    $_REQUEST['rowCount'] = 12;

    $videos['recordsTotal'] = Video::getTotalVideos(Video::SORT_TYPE_VIEWABLE, false, false, false, true, false,'audio_and_video', $shortMaxDurationInSeconds);
    //getAllVideos($status = Video::SORT_TYPE_VIEWABLE, $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = [], $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $is_serie = null, $type = '', $max_duration_in_seconds=0) {
    $videos['data'] = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, false, false, [], false, false, true, false, null, 'audio_and_video', $shortMaxDurationInSeconds);
    foreach ($videos['data'] as $key => $video) {
        $images = object_to_array(Video::getImageFromFilename($video['filename'], $video['type']));
        $videos['data'][$key]['images'] = $images;
    }
    $videos['recordsFiltered'] = count($videos['data']);

    $_POST['sort'] = $sort;
    $_REQUEST['rowCount'] = $rowCount;
    
}
//var_dump($videos);exit;
echo _json_encode($videos);