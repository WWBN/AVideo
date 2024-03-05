<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

if (empty($_REQUEST['response'])) {
    _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('Response is empty');
}

$objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

if (empty($objAI)) {
    _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('AI plugin is disabled');
}

$token = AI::getTokenFromRequest();

if (empty($token)) {
    _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('invalid token');
}

if (empty($token->ai_responses_id)) {
    _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('invalid ai_responses_id');
}

$jsonDecoded = new stdClass();
$jsonDecoded->error = true;
$jsonDecoded->msg = '';
$jsonDecoded->type = $_REQUEST['type'];
$jsonDecoded->token = $token;

_error_log('Start line=' . __LINE__ . ' type=' . $_REQUEST['type']);

switch ($_REQUEST['type']) {
    case AI::$typeTranslation:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        if (!empty($_REQUEST['response']['vtt'])) {
            _error_log('Start line=' . __LINE__);
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
                _error_log('Start line=' . __LINE__);
                //$jsonDecoded->lines[] = __LINE__;
                $paths = Ai_transcribe_responses::getVTTPaths($token->videos_id, $_REQUEST['response']['lang']);
                $jsonDecoded->vttsaved = file_put_contents($paths['path'], $_REQUEST['response']['vtt']);
                //Video::clearCache($token->videos_id);
                clearCache();
            }

            $jsonDecoded->error = false;
            //$jsonDecoded->lines[] = __LINE__;
        }
        break;
    case AI::$typeBasic:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        if (!empty($_REQUEST['response'])) {
            _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
            //$jsonDecoded->lines[] = __LINE__;
            $o = new Ai_metatags_responses(0);
            $o->setVideoTitles($_REQUEST['response']['videoTitles']);
            $o->setKeywords($_REQUEST['response']['keywords']);
            $o->setProfessionalDescription($_REQUEST['response']['professionalDescription']);
            $o->setCasualDescription($_REQUEST['response']['casualDescription']);
            $o->setShortSummary($_REQUEST['response']['shortSummary']);
            $o->setMetaDescription($_REQUEST['response']['metaDescription']);
            $o->setRrating($_REQUEST['response']['rrating']);
            $o->setRratingJustification($_REQUEST['response']['rratingJustification']);
            $o->setPrompt_tokens($_REQUEST['prompt_tokens']);
            $o->setcompletion_tokens($_REQUEST['completion_tokens']);
            $o->setPrice_prompt_tokens($_REQUEST['price_prompt_tokens']);
            $o->setPrice_completion_tokens($_REQUEST['price_completion_tokens']);
            $o->setAi_responses_id($token->ai_responses_id);
            $jsonDecoded->Ai_metatags_responses = $o->save();

            $jsonDecoded->error = false;
        }
        break;
    case AI::$typeTranscription:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        if (!empty($_REQUEST['response'])) {
            _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
            $o = new Ai_transcribe_responses(0);
            $o->setVtt($_REQUEST['response']['vtt']);
            $o->setLanguage($_REQUEST['response']['language']);
            $o->setDuration($_REQUEST['response']['duration']);
            $o->setText($_REQUEST['response']['text']);
            $o->setTotal_price($_REQUEST['response']['total_price']);
            $o->setSize_in_bytes($_REQUEST['response']['size_in_bytes']);
            $o->setMp3_url($_REQUEST['mp3']);
            $o->setAi_responses_id($token->ai_responses_id);
            $jsonDecoded->Ai_transcribe_responses = $o->save();

            $jsonDecoded->vttsaved = false;
            if (!empty($_REQUEST['response']['vtt']) && !empty($jsonDecoded->Ai_transcribe_responses)) {
                _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
                //$jsonDecoded->lines[] = __LINE__;
                $paths = Ai_transcribe_responses::getVTTPaths($token->videos_id, $_REQUEST['response']['language']);
                if (!empty($paths['path'])) {
                    $jsonDecoded->vttsaved = file_put_contents($paths['path'], $_REQUEST['response']['vtt']);
                } else {
                    _error_log("VTTFile Path is empty videos_id={$token->videos_id}, language={$_REQUEST['response']['language']} " . json_encode($paths));
                }
            }
            $jsonDecoded->error = false;
        }
        break;
    case AI::$typeShorts:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        if (!empty($_REQUEST['response']) && !empty($_REQUEST['response']['shorts'])) {
            _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__ . json_encode($_REQUEST['response']));
            $shorts = $_REQUEST['response']['shorts'];
            if (!empty($shorts)) {
                $o = new Ai_responses_json(0);
                $o->setResponse($shorts);
                $o->setAi_type(AI::$typeShorts);
                $o->setAi_responses_id($token->ai_responses_id);
                $jsonDecoded->shorts = $shorts;
                $jsonDecoded->Ai_responses_json = $o->save();
                $jsonDecoded->error = empty($jsonDecoded->Ai_responses_json);
            }else{
                _error_log('AI: shorts ERROR' . basename(__FILE__) . ' line=' . __LINE__);
            }
        }else{
            _error_log('AI: ERROR ' . basename(__FILE__) . ' line=' . __LINE__ . json_encode($_REQUEST));
        }
        break;

    default:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        $jsonDecoded->msg = 'Type not found';
        break;
}

if ($jsonDecoded->error) {
    error_log($global['lastQuery'] . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}

if ($jsonDecoded->vttsaved) {
    Video::clearCache($token->videos_id);
}

_error_log('You received a new translation ' . json_encode(debug_backtrace()));
sendSocketMessageToUsers_id(['type' => $_REQUEST['type']], $token->users_id, 'aiSocketMessage');

$r = json_encode($jsonDecoded);
_error_log($r);

echo $r;
