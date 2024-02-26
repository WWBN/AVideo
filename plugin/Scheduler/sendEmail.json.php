<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

header('Content-Type: application/json');


if(empty($_REQUEST['scheduler_commands_id'])){
    forbiddenPage('scheduler_commands_id is empty');
}

if(empty($_REQUEST['token'])){
    forbiddenPage('token is empty');
}

if(!isTokenValid($_REQUEST['token'])){
    if (!isCommandLineInterface() && !User::isAdmin()) {
        forbiddenPage('token is invalid');
    }
}

if(!AVideoPlugin::isEnabledByName('Scheduler')){
    forbiddenPage('Scheduler is disabled');
}

$e = new Scheduler_commands($_REQUEST['scheduler_commands_id']);

$parameters = _json_decode($e->getParameters());
//echo  $e->getParameters();
//var_dump($parameters, $e->getParameters(), json_last_error_msg());exit;
if(empty($parameters)){
    forbiddenPage('paramenters is empty');
}

$parameters = object_to_array($parameters);

if(empty($parameters['emailTo'])){
    forbiddenPage('emailTo is empty');
}

$parameters['emailTo'] = is_email($parameters['emailTo']);
if (empty($parameters['emailTo'])) {
    forbiddenPage('emailTo is invalid');
}

if(emptyHTML($parameters['emailEmailBody'])){
    forbiddenPage('emailEmailBody is empty');
}

if(is_numeric($parameters['emailFrom'])){
    $parameters['emailFromName'] = User::getNameIdentificationById($parameters['emailFrom']);;
    $parameters['emailFrom'] = User::getEmailDb($parameters['emailFrom']);
}

if(empty($parameters['emailFrom']) || !filter_var($parameters['emailFrom'], FILTER_VALIDATE_EMAIL)){
    $parameters['emailFrom'] = $config->getContactEmail();
}

if(empty($parameters['emailFromName'])){
    $parameters['emailFromName'] = '';
}

if(empty($parameters['emailSubject'])){
    $parameters['emailSubject'] = $config->getWebSiteTitle();
}


$obj = new stdClass();
$obj->msg = '';
$obj->parameters = $parameters;
$obj->error = !sendSiteEmail($parameters['emailTo'], $parameters['emailSubject'], $parameters['emailEmailBody'], $parameters['emailFrom'], $parameters['emailFromName']);

die(_json_encode($obj));