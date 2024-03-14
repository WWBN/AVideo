<?php
require_once '../../../videos/configuration.php';

header('Content-Type: application/json');

$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}

if(!AI::canUseAI()){
    forbiddenPage('You cannot use AI');
}


setRowCount(100);
$video = new Video('', '', $videos_id);
$obj = new stdClass();
$obj->error = false;
$obj->msg = '';
$obj->currentTitle = $video->getTitle();
$obj->currentDescription = $video->getDescription();
$obj->currentRating = $video->getRrating();


$externalOptions = _json_decode($video->getExternalOptions());
$SEO = @$externalOptions->SEO;
$obj->currentShortSummary = '';
$obj->currentMetaDescription = '';
if (!empty($SEO)) {
    $obj->currentShortSummary = $SEO->ShortSummary;
    $obj->currentMetaDescription = $SEO->MetaDescription;
}

$obj->currentTags = '';

$VideoTags = AVideoPlugin::isEnabledByName('VideoTags');
if (!empty($VideoTags)) {
    $rows = VideoTags::getArrayFromVideosId($videos_id);
    $obj->currentTags = implode(', ', $rows);
}

echo _json_encode($obj);
