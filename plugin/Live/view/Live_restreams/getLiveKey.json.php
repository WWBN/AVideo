<?php
require_once '../../../../videos/configuration.php';
if(!AVideoPlugin::isEnabledByName('Live')){
    forbiddenPage('Live plugin is disabled');
}
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

if(empty($_REQUEST['live_restreams_id'])){
    forbiddenPage('live_restreams_id cannot be empty');
}

$Live_restreams = new Live_restreams($_REQUEST['live_restreams_id']);

if(empty($Live_restreams->getName())){
    forbiddenPage('Name not found for live_restreams_id='.$_REQUEST['live_restreams_id']);
}

if($Live_restreams->getUsers_id() !==User::getId() && !User::isAdmin() && !isCommandLineInterface()){
    forbiddenPage('You have no access to this restream');
}

$parameters = $Live_restreams->getParameters();
if(empty($parameters)){
    forbiddenPage('Restream parameters not present');
}

$parametersJson = json_decode($parameters);
if(empty($parametersJson) || empty($parametersJson->{'restream.ypt.me'})){
    $response = new stdClass();
    $response->error = false;
    $response->msg = '';
    $response->stream_key = $Live_restreams->getStream_key();
    $response->stream_url = $Live_restreams->getStream_url();
    $response->provider = 'Local';
    $response->subtitle = $Live_restreams->getName();
    $response->http_code = 200;
}else{
    $lt = LiveTransmition::getFromDbByUser($Live_restreams->getUsers_id());

    $url = 'https://restream.ypt.me/get.php';
    $array = array(
        'title'=> $lt['title'],
        'description'=> $lt['description'],
        'parameters64'=> base64_encode(json_encode($parametersJson->{'restream.ypt.me'})),
    );
    
    if(!empty($_REQUEST['live_schedule_id'])){
        $ls = new live_schedule($_REQUEST['live_schedule_id']);
    
        if(!empty($ls->getTitle())){
            $array['title'] = $ls->getTitle();
        }
        if(!empty($ls->getDescription())){
            $array['description'] = $ls->getDescription();
        }
    }
    
    $response = postVariables($url, $array, false);
}

echo $response;

?>