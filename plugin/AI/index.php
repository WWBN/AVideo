<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

if (empty($objAI)) {
    forbiddenPage('AI plugin is disabled');
}

$videos_id = getVideos_id();

if (empty($videos_id)) {
    forbiddenPage('Videos ID is empty');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('Cannot edit this video');
}

$aiURL = AI::getMetadataURL();

if (!_empty($_REQUEST['translation'])) {
    $obj = AI::getVideoTranslationMetadata($videos_id, $_REQUEST['lang'], $_REQUEST['langName']);
} else if (_empty($_REQUEST['transcription'])) {
    $obj = AI::getVideoBasicMetadata($videos_id);
} else {
    $obj = AI::getVideoTranscriptionMetadata($videos_id);
}

if ($obj->error) {
    forbiddenPage($obj->msg);
}

$json = $obj->response;
$json['AccessToken'] = $objAI->AccessToken;
//echo json_encode($obj);exit;

if (empty($json['AccessToken'])) {
    forbiddenPage('Invalid AccessToken');
}

$content = postVariables($aiURL, $json, false, 600);
$jsonDecoded = json_decode($content);

if (empty($content)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "Oops! Our system took a bit longer than expected to process your request. 
    Please try again in a few moments. We apologize for any inconvenience and appreciate your patience.";
}

if (empty($jsonDecoded)) {
    $jsonDecoded = new stdClass();
    $jsonDecoded->error = true;
    $jsonDecoded->msg = "Some how we got an error in the response";
    $jsonDecoded->content = $content;
}

//$jsonDecoded->lines = array();
//$jsonDecoded->json = $json;
$jsonDecoded->aiURL = $aiURL;

$o = new Ai_responses(0);
$o->setElapsedTime($jsonDecoded->elapsedTime);
$o->setVideos_id($videos_id);
$o->setPrice($jsonDecoded->payment->howmuch);
$jsonDecoded->Ai_responses = $o->save();

if (!empty($jsonDecoded->Ai_responses)) {
    if ($jsonDecoded->type=='basic' && !empty($jsonDecoded->response->response)) {
        //$jsonDecoded->lines[] = __LINE__;
        $o = new Ai_metatags_responses(0);
        $o->setVideoTitles($jsonDecoded->response->response->videoTitles);
        $o->setKeywords($jsonDecoded->response->response->keywords);
        $o->setProfessionalDescription($jsonDecoded->response->response->professionalDescription);
        $o->setCasualDescription($jsonDecoded->response->response->casualDescription);
        $o->setShortSummary($jsonDecoded->response->response->shortSummary);
        $o->setMetaDescription($jsonDecoded->response->response->metaDescription);
        $o->setRrating($jsonDecoded->response->response->rrating);
        $o->setRratingJustification($jsonDecoded->response->response->rratingJustification);
        $o->setPrompt_tokens($jsonDecoded->response->prompt_tokens);
        $o->setcompletion_tokens($jsonDecoded->response->completion_tokens);
        $o->setPrice_prompt_tokens($jsonDecoded->response->price_prompt_tokens);
        $o->setPrice_completion_tokens($jsonDecoded->response->price_completion_tokens);
        $o->setAi_responses_id($jsonDecoded->Ai_responses);
        $jsonDecoded->Ai_metatags_responses = $o->save();
    } else  if ($jsonDecoded->type=='transcription' && !empty($jsonDecoded->transcribe)) {
        //$jsonDecoded->lines[] = __LINE__;
        $o = new Ai_transcribe_responses(0);
        $o->setVtt($jsonDecoded->transcribe->vtt);
        $o->setLanguage($jsonDecoded->transcribe->language);
        $o->setDuration($jsonDecoded->transcribe->duration);
        $o->setText($jsonDecoded->transcribe->text);
        $o->setTotal_price($jsonDecoded->transcribe->total_price);
        $o->setSize_in_bytes($jsonDecoded->transcribe->size_in_bytes);
        $o->setMp3_url($jsonDecoded->mp3);
        $o->setAi_responses_id($jsonDecoded->Ai_responses);
        $jsonDecoded->Ai_transcribe_responses = $o->save();

        $jsonDecoded->vttsaved = false;
        if (!empty($jsonDecoded->transcribe->vtt) && !empty($jsonDecoded->Ai_transcribe_responses)) {
            //$jsonDecoded->lines[] = __LINE__;
            $paths = Ai_transcribe_responses::getVTTPaths($videos_id);
            if (!file_exists($paths['path'])) {
                //$jsonDecoded->lines[] = __LINE__;
                $jsonDecoded->vttsaved = file_put_contents($paths['path'], $jsonDecoded->transcribe->vtt);
            }
        }
    } else  if ($jsonDecoded->type==AI::$typeTranslation && !empty($jsonDecoded->response->response)) {
        //$jsonDecoded->lines[] = __LINE__;
        $o = new Ai_transcribe_responses(0);
        $o->setVtt($jsonDecoded->response->response->vtt);
        $o->setLanguage($jsonDecoded->response->response->lang);
        $o->setText($jsonDecoded->response->response->text);
        $o->setTotal_price($jsonDecoded->response->total_price);
        $o->setSize_in_bytes(strlen($jsonDecoded->response->response->vtt));
        $o->setAi_responses_id($jsonDecoded->Ai_responses);
        $jsonDecoded->Ai_transcribe_responses = $o->save();

        $jsonDecoded->vttsaved = false;
        if (!empty($jsonDecoded->response->response->vtt) && !empty($jsonDecoded->Ai_transcribe_responses)) {
            //$jsonDecoded->lines[] = __LINE__;
            $paths = Ai_transcribe_responses::getVTTPaths($videos_id, $jsonDecoded->response->response->lang);
            //$jsonDecoded->paths = $paths;
            if (!file_exists($paths['path'])) {
                //$jsonDecoded->lines[] = __LINE__;
                $jsonDecoded->vttsaved = file_put_contents($paths['path'], $jsonDecoded->response->response->vtt);
            }
        }
        //$jsonDecoded->lines[] = __LINE__;
    }
}

echo json_encode($jsonDecoded);
