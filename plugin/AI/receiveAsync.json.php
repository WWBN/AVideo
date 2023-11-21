<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

if(empty($_REQUEST['response'])){
    forbiddenPage('Response is empty');
}

$objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

if (empty($objAI)) {
    forbiddenPage('AI plugin is disabled');
}

$token = AI::getTokenFromRequest();

if(empty($token)){
    forbiddenPage('invalid token');
}

if(empty($token->ai_responses_id)){
    forbiddenPage('invalid ai_responses_id');
}

$jsonDecoded = new stdClass();
$jsonDecoded->error = true;
$jsonDecoded->msg = '';

if ($_REQUEST['type']=='translation' && !empty($_REQUEST['response']['vtt'])) {
    //$jsonDecoded->lines[] = __LINE__;
    $o = new Ai_transcribe_responses(0);
    $o->setVtt($_REQUEST['response']['vtt']);
    $o->setLanguage($_REQUEST['response']['lang']);
    $o->setText($_REQUEST['response']['text']);
    $o->setTotal_price($_REQUEST['response']['total_price']);
    $o->setSize_in_bytes(strlen($_REQUEST['response']['vtt']));
    $o->setAi_responses_id($token->ai_responses_id);
    $jsonDecoded->Ai_transcribe_responses = $o->save();

    $jsonDecoded->vttsaved = false;
    if (!empty($jsonDecoded->Ai_transcribe_responses)) {
        //$jsonDecoded->lines[] = __LINE__;
        $paths = Ai_transcribe_responses::getVTTPaths($token->videos_id, $_REQUEST['response']['lang']);
        //$jsonDecoded->paths = $paths;
        if (!file_exists($paths['path'])) {
            //$jsonDecoded->lines[] = __LINE__;
            $jsonDecoded->vttsaved = file_put_contents($paths['path'], $_REQUEST['response']['vtt']);
        }
    }
    
    $jsonDecoded->error = false;
    //$jsonDecoded->lines[] = __LINE__;
}

_error_log(json_encode($jsonDecoded));