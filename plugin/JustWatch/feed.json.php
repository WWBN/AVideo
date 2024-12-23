<?php

require_once '../../videos/configuration.php';
header('Content-Type: application/json');


$obj = AVideoPlugin::getDataObjectIfEnabled('JustWatch');

if (empty($obj)) {
    forbiddenPage('Plugin is disabled');
}

// Create a new DateTime object for the current date and time in UTC
$date = new DateTime('now', new DateTimeZone('UTC'));
// Format the date in the specified format
$formattedDate = $date->format('Y-m-d\TH:i:sP');

$array = array();
$array['streaming_service'] = array();
$array['streaming_service']['name'] = $config->getWebSiteTitle();
$array['streaming_service']['url'] = $global['webSiteRootURL'];
$array['streaming_service']['application_stores'] = json_decode($obj->application_stores->value);
$array['streaming_service']['application_packages'] = json_decode($obj->application_packages->value);

$array['contents'] = array();

//setRowCount(2);
setRowCount(5000);
$rows = Video::getAllVideosLight();
$addedElements = array();
foreach ($rows as $row) {
    if(in_array($row['id'], $addedElements)){
        continue;
    }
    $addedElements[] = $row['id'];
    $element = array();
    $element['id'] = '' . $row['id'];
    $element['runtime'] = intval($row['duration_in_seconds']);
    $element['release_year'] = JustWatch::getReleaseYear($row['id']);
    $element['crew_members'] = JustWatch::getCrewMembers($row['id']);
    //$element['object_type'] = 'movie';
    $element['object_type'] = 'movie';
    $element['original_title'] = $row['title'];
    $element['original_description'] = $row['description'];

    $element['localized_titles'] = array(array('language' => 'en', 'value' => $element['original_title']));
    $element['localized_descriptions'] = array(array('language' => 'en', 'value' => $element['original_description']));
    $element['offers'] = array(
        array(
            'web_url' => Video::getLinkToVideo($row['id']),
            'quality' => 'hd',
            'monetization_type' => 'free',
            'currency' => $obj->currency,
            'country_iso' => $obj->country_iso,
            'price' => 0,
        )
    );

    $element['images'] = array();
    $posters = Video::getMediaSessionPosters($row['id']);
    foreach ($posters as $key => $value) {
        $element['images'][] = array(
            'url' => $value['url'],
            'image_type' => 'poster',
            'width' => $key,
            'height' => $key,
            'language' => 'en',
        );
    }
    $array['contents'][] = $element;
}

$array['last_modified'] = $formattedDate;

echo _json_encode($array);
