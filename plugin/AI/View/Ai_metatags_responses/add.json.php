<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_metatags_responses.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('AI');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Ai_metatags_responses(@$_POST['id']);
$o->setVideoTitles($_POST['videoTitles']);
$o->setKeywords($_POST['keywords']);
$o->setProfessionalDescription($_POST['professionalDescription']);
$o->setCasualDescription($_POST['casualDescription']);
$o->setShortSummary($_POST['shortSummary']);
$o->setMetaDescription($_POST['metaDescription']);
$o->setRrating($_POST['rrating']);
$o->setRratingJustification($_POST['rratingJustification']);
$o->setPrompt_tokens($_POST['prompt_tokens']);
$o->setcompletion_tokens($_POST['completion_tokens']);
$o->setPrice_prompt_tokens($_POST['price_prompt_tokens']);
$o->setPrice_completion_tokens($_POST['price_completion_tokens']);
$o->setAi_responses_id($_POST['ai_responses_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
