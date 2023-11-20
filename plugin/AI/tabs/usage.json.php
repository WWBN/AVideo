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

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

AVideoPlugin::loadPlugin('YPTWallet');

setDefaultSort('sortDate', 'DESC');
$obj = new stdClass();
$obj->msg = '';
$obj->response = Ai_responses::getAllFromVideo($videos_id);

foreach ($obj->response as $key => $value) {
    $obj->response[$key]['total_price'] = YPTWallet::formatCurrency($value['total_price']);
    $obj->response[$key]['price'] = YPTWallet::formatCurrency($value['price']);
}

$obj->error = empty($obj->response) && !is_array($obj->response);
echo _json_encode($obj);