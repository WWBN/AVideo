<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$video = new Video('', '', $videos_id);

$ai = Ai_responses::getLatest($videos_id);

if (empty($ai)) {
    forbiddenPage('AI Response not found');
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->msgs = array();
$obj->videos_id = $videos_id;
$obj->ai = $ai;

$videoTitles = $ai['videoTitles'];
$json = json_decode($videoTitles);
if (!empty($json)) {
    $title = $json[0];
    if (!empty($title)) {
        $video->setTitle($title);
        $video->setClean_title($title);
        $obj->msgs[] = "Video title added [{$title}]";
    } else {
        $obj->msgs[] = 'This title is empty';
    }
} else {
    $obj->msgs[] = 'Could not get the titles options';
}

if (!empty($ai['professionalDescription'])) {
    $video->setDescription($ai['professionalDescription']);
    $obj->msgs[] = "Video description added";
} else {
    $obj->msgs[] = 'Professional Description is empty';
}

if (!empty($ai['rrating'])) {
    $video->setRrating($ai['rrating']);
    $obj->msgs[] = "Video rrating added [{$ai['rrating']}]";
} else {
    $obj->msgs[] = 'Rating is empty';
}

$id = $video->save();
$obj->msgs[] = "Video saved [{$id}]";
$obj->error = empty($id);

$ShortSummary = $ai['shortSummary'];
$MetaDescription = $ai['metaDescription'];
$id = CustomizeAdvanced::setShortSummaryAndMetaDescriptionVideo($obj->videos_id, $ShortSummary, $MetaDescription);

$VideoTags = AVideoPlugin::isEnabledByName('VideoTags');
if (!empty($VideoTags)) {
    $keywords = $ai['keywords'];
    $json = json_decode($keywords);
    if (!empty($json)) {
        $tags_types_id = AI::getTagTypeId();
        foreach ($json as $keyword) {
            $id = VideoTags::add($keyword, $tags_types_id, $obj->videos_id);
            $obj->msgs[] = "Keywords save ".json_encode($id);
        }
    } else {
        $obj->msgs[] = 'Could not get the keywords options';
    }
} else {
    $obj->msgs[] = 'VideoTags plugin is disabled';
}

if (!empty($ai['vtt'])) {
    $file = AI::getFirstVTTFile($videos_id);
    if (!mb_check_encoding($ai['vtt'], 'UTF-8')) {
        $ai['vtt'] = mb_convert_encoding($ai['vtt'], 'UTF-8');
    }
    $utf8_bom = "\xEF\xBB\xBF";
    $id = file_put_contents($file, $utf8_bom . $ai['vtt']);
    $obj->msgs[] = "vtt saved [{$id}]";
} else {
    $obj->msgs[] = 'Transcription is empty';
}



echo json_encode($obj);
