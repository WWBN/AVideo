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

_error_log('Start line='.__LINE__);
if ($_REQUEST['type']==AI::$typeTranslation && !empty($_REQUEST['response']['vtt'])) {
    _error_log('Start line='.__LINE__);
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
        _error_log('Start line='.__LINE__);
        //$jsonDecoded->lines[] = __LINE__;
        $paths = Ai_transcribe_responses::getVTTPaths($token->videos_id, $_REQUEST['response']['lang']);
        $jsonDecoded->vttsaved = file_put_contents($paths['path'], $_REQUEST['response']['vtt']);
        //Video::clearCache($token->videos_id);
        clearCache();
    }
    
    $jsonDecoded->error = false;
    //$jsonDecoded->lines[] = __LINE__;
    sendSocketMessageToUsers_id('You received a new translation '.$_REQUEST['response']['lang'], $token->users_id, 'aiNewTranslationAvailable');
}

_error_log(json_encode($jsonDecoded));