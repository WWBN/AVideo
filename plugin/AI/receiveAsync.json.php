<?php
$global['bypassSameDomainCheck'] = 1;
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

if (empty($_REQUEST['response'])) {
    rateLimitedLog('AI-receiveAsync-empty-response', 'AI: ' . basename(__FILE__) . ' line=' . __LINE__ . ' type=' . ($_REQUEST['type'] ?? 'unknown'));
    forbiddenPage('Response is empty');
}

$objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

if (empty($objAI)) {
    rateLimitedLog('AI-receiveAsync-plugin-disabled', 'AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('AI plugin is disabled');
}

$token = AI::getTokenFromRequest();

if (empty($token)) {
    rateLimitedLog('AI-receiveAsync-invalid-token-' . md5(json_encode([$_REQUEST['type'] ?? '', $_REQUEST['ai_responses_id'] ?? '', getRealIpAddr()])), 'AI: ' . basename(__FILE__) . ' line=' . __LINE__ . ' invalid token type=' . ($_REQUEST['type'] ?? 'unknown'));
    forbiddenPage('invalid token');
}

if (empty($token->ai_responses_id)) {
    rateLimitedLog('AI-receiveAsync-empty-ai-responses-id', 'AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('invalid ai_responses_id');
}

$jsonDecoded = new stdClass();
$jsonDecoded->error = true;
$jsonDecoded->msg = '';
$jsonDecoded->type = $_REQUEST['type'];
$jsonDecoded->token = $token;

switch ($_REQUEST['type']) {
    case AI::$typeTranslation:
        if (!empty($_REQUEST['response']['vtt'])) {
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
                $jsonDecoded->vttsaved = file_put_contents($paths['path'], $_REQUEST['response']['vtt']);
                //Video::clearCache($token->videos_id);
                clearCache();
            }

            $jsonDecoded->error = false;
            //$jsonDecoded->lines[] = __LINE__;
        }
        break;
    case AI::$typeBasic:
        if (!empty($_REQUEST['response'])) {
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
        if (!empty($_REQUEST['response'])) {
            $jsonDecoded = Ai_transcribe_responses::saveVTT(
                $_REQUEST['response']['vtt'],
                $_REQUEST['response']['language'],
                $_REQUEST['response']['duration'],
                $_REQUEST['response']['text'],
                $_REQUEST['response']['total_price'],
                $_REQUEST['response']['size_in_bytes'],
                $_REQUEST['response']['mp3'],
                $jsonDecoded
            );
        }
        break;
    case AI::$typeShorts:
        if (!empty($_REQUEST['response']) && !empty($_REQUEST['response']['shorts'])) {
            $shorts = $_REQUEST['response']['shorts'];
            if (!empty($shorts)) {
                $o = new Ai_responses_json(0);
                $o->setResponse($_REQUEST['response']);
                $o->setAi_type(AI::$typeShorts);
                $o->setAi_responses_id($token->ai_responses_id);
                $jsonDecoded->shorts = $shorts;
                $jsonDecoded->Ai_responses_json = $o->save();
                $jsonDecoded->error = empty($jsonDecoded->Ai_responses_json);
            } else {
                rateLimitedLog('AI-receiveAsync-shorts-empty-' . md5(json_encode([$token->ai_responses_id ?? 0, $token->videos_id ?? 0])), 'AI: shorts ERROR ' . basename(__FILE__) . ' line=' . __LINE__);
            }
        } else {
            rateLimitedLog('AI-receiveAsync-shorts-invalid-' . md5(json_encode([$token->ai_responses_id ?? 0, $token->videos_id ?? 0, $_REQUEST['type'] ?? ''])), 'AI: ERROR ' . basename(__FILE__) . ' line=' . __LINE__ . ' invalid shorts payload');
        }
        break;
    case AI::$typeDubbing:
        if (!empty($_REQUEST['relativeFile'])) {
            require_once __DIR__ . '/../../plugin/VideoHLS/HLSAudioManager.php';
            $mp3URL = AI::getMetadataURL() . $_REQUEST['relativeFile'];
            $vttURL = AI::getMetadataURL() . $_REQUEST['relativeFileVTT'];

            $language = 'Default';
            foreach (AI::DubbingLANGS as $key => $value) {
                if ($value['code'] == $_REQUEST['language']) {
                    $language = $value['name'];
                    break;
                }
            }

            $jsonDecoded->addAudioTrack = HLSAudioManager::addAudioTrack($token->videos_id, $mp3URL, $language);
            $jsonDecoded->error = empty($jsonDecoded->addAudioTrack);
            $jsonDecoded->response = $_REQUEST['response'];
            $vtt = file_get_contents($vttURL);
            $jsonDecoded = Ai_transcribe_responses::saveVTT(
                $vtt,
                $language,
                0,
                $vtt,
                0,
                strlen($vtt),
                $mp3URL,
                $jsonDecoded
            );

            $o = new Ai_responses_json(0);
            $o->setResponse($jsonDecoded);
            $o->setAi_type(AI::$typeDubbing);
            $o->setAi_responses_id($token->ai_responses_id);
            $jsonDecoded->Ai_transcribe_responses = $o->save();
            //$jsonDecoded->lines[] = __LINE__;
        }
        break;
    case AI::$typeImage:
        if (!empty($_REQUEST['response'])) {
            $o = new Ai_responses_json(0);
            $o->setResponse($_REQUEST['response']);
            $o->setAi_type(AI::$typeImage);
            $o->setAi_responses_id($token->ai_responses_id);
            if (!empty($_REQUEST['response']['data'][0]['url'])) {
                $imageUrl = $_REQUEST['response']['data'][0]['url'];
                // SSRF Protection: url_get_contents() validates the URL and all redirect
                // hops via isSSRFSafeURL() with follow_location=0, preventing redirect-based bypass.
                // The AI service always returns an http(s) image URL; isValidURL() keeps local paths
                // and php://input out, since url_get_contents() reads those without the SSRF check.
                $imageContent = isValidURL($imageUrl) ? url_get_contents($imageUrl) : false;
                if ($imageContent === false) {
                    rateLimitedLog('AI-receiveAsync-image-ssrf-' . md5($imageUrl), 'AI: ' . basename(__FILE__) . ' line=' . __LINE__ . ' SSRF protection blocked URL or fetch failed: ' . $imageUrl);
                } else {
                    if (empty($imageContent)) {
                        rateLimitedLog('AI-receiveAsync-image-fetch-fail-' . md5($imageUrl), 'AI: ' . basename(__FILE__) . ' line=' . __LINE__ . ' Error fetching image content');
                    } else {
                        Video::saveImageInVideoLib($token->videos_id, $imageContent, 'png', 'ai');
                    }
                }
            }
            $jsonDecoded->msg = $_REQUEST['msg'];
            $jsonDecoded->Ai_responses_json = $o->save();
            $jsonDecoded->error = empty($jsonDecoded->Ai_responses_json);
        }
        break;

    default:
        rateLimitedLog('AI-receiveAsync-type-not-found-' . md5($_REQUEST['type'] ?? 'unknown'), 'AI: ' . basename(__FILE__) . ' line=' . __LINE__ . ' type not found');
        $jsonDecoded->msg = 'Type not found';
        break;
}

if ($jsonDecoded->error) {
    rateLimitedLog('AI-receiveAsync-db-error-' . md5(($global['lastQuery'] ?? '') . '|' . ($global['mysqli']->errno ?? '0')), ($global['lastQuery'] ?? '') . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}

if ($jsonDecoded->vttsaved) {
    Video::clearCache($token->videos_id);
}

sendSocketMessageToUsers_id(['type' => $_REQUEST['type']], $token->users_id, 'aiSocketMessage');

$r = json_encode($jsonDecoded);

echo $r;
