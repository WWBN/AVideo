<?php

require_once '../../videos/configuration.php';
header('Content-Type: application/json');


$obj = AVideoPlugin::getDataObjectIfEnabled('JustWatch');

if(empty($obj)){
    forbiddenPage('Plugin is disabled');
}

// Create a new DateTime object for the current date and time in UTC
$date = new DateTime('now', new DateTimeZone('UTC'));
// Format the date in the specified format
$formattedDate = $date->format('Y-m-d\TH:i:sP');

$array = array();
$array['streaming_service'] = array();
$array['streaming_service']['name'] = $obj->streaming_service_name;
$array['streaming_service']['url'] = $obj->streaming_service_url;
$array['streaming_service']['application_stores'] = json_decode($obj->application_stores->value);
$array['streaming_service']['application_packages'] = json_decode($obj->streaming_service_url->value);

$array['contents'] = array();

$rows = Video::getAllVideosLight();
foreach ($rows as $row) {
    $element = array();
    $element['id'] = $row['id'];
    $element['object_type'] = 'movie';
    $element['original_title'] = $row['title'];
    $element['original_description'] = $row['description'];
    $element['images'] = array();
    $posters = Video::getMediaSessionPosters($row['id']);
    foreach ($posters as $key => $value) {

    $element['images'][] = array(
        'url' => $value['url'],
        'image_type' => 'poster',
        'width' => $key,
        'height' => $key,
        //'language' => 'en',
    );
    }
}


$array['last_modified'] = $formattedDate;


?>
