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

if (!AI::canUseAI()) {
    forbiddenPage('You cannot use AI');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'status';

$obj = new stdClass();
$obj->error = false;
$obj->msg = '';
// Refresh only after the same AI and video-edit checks as the action itself.
if ($action === 'token') {
    forbidIfNotPost();
    $obj->globalToken = getToken(300, '', $videos_id);
    echo _json_encode($obj);
    exit;
}

// Local duration is available even before Companion has ingested the video.
$localVideo = new Video('', '', $videos_id);
$durationSeconds = (float) $localVideo->getDuration_in_seconds();
if ($durationSeconds <= 0) {
    $durationSeconds = (float) durationToSeconds($localVideo->getDuration());
}
$obj->videoDurationSeconds = is_finite($durationSeconds) && $durationSeconds > 0 ? $durationSeconds : null;

$obj->notConfigured = !CompanionAI::isConfigured();

if (!$obj->notConfigured) {
    switch ($action) {
        case 'status':
            break;
        case 'link_marketplace':
            forbidIfNotPost();
            forbidIfInvalidToken();
            $objAI = AVideoPlugin::getObjectDataIfEnabled('AI');
            $res = empty($objAI->AccessToken) ? null : CompanionAI::linkMarketplace($objAI->AccessToken);
            if (empty($res) || !empty($res->error)) {
                $obj->error = true;
                $obj->msg = empty($objAI->AccessToken)
                    ? CompanionAI::connectionErrorMessage(402, false)
                    : CompanionAI::getConnectionError();
            }
            break;
        case 'submit':
            forbidIfNotPost();
            forbidIfInvalidToken();
            if (CompanionAI::isPasswordProtected($videos_id)) {
                // Say why instead of letting the generic message below blame
                // the wallet: the stored password is a hash, so Companion can
                // never fetch this video.
                $obj->error = true;
                $obj->msg = __('Password protected videos cannot be processed for Chat.');
                break;
            }
            $res = CompanionAI::submitVideo($videos_id);
            if (empty($res)) {
                $obj->error = true;
                $obj->msg = __('Could not submit this video to Companion. Check that your Marketplace wallet is connected and has enough available credits for processing.');
            } else {
                CompanionAI::scheduleSubtitleImport($videos_id, User::getId());
            }
            break;
        case 'copy_subtitle':
            forbidIfNotPost();
            forbidIfInvalidToken();
            // auto=1 is the tab's own silent attempt once processing ends:
            // it never replaces a subtitle that did not come from Companion.
            $automatic = !empty($_POST['auto']);
            $obj->subtitleResult = CompanionAI::importSubtitle($videos_id, !$automatic && !empty($_POST['overwrite']), $automatic);
            break;
        case 'enable_chat':
            forbidIfNotPost();
            forbidIfInvalidToken();
            $res = CompanionAI::enableChat($videos_id);
            if (empty($res)) {
                $obj->error = true;
                $obj->msg = __('Could not enable chat for this video yet - make sure it finished processing.');
            }
            break;
        case 'disable_chat':
            forbidIfNotPost();
            forbidIfInvalidToken();
            CompanionAI::disableChat($videos_id);
            break;
        default:
            $obj->error = true;
            $obj->msg = __('Invalid Companion Chat action.');
            break;
    }

    $obj->status = CompanionAI::status();
    if (empty($obj->status)) {
        $obj->error = true;
        $obj->msg = CompanionAI::getConnectionError();
        echo _json_encode($obj);
        exit;
    }
    $videoHttpCode = null;
    $obj->videoStatus = CompanionAI::getVideoStatus($videos_id, $videoHttpCode);
    if (empty($obj->videoStatus) && $videoHttpCode !== 404) {
        $obj->videoStatusError = true;
        if (!$obj->error) {
            $obj->error = true;
            $obj->msg = __('Could not check the video status in Companion. Please try again.');
        }
    }
    $obj->chatConfig = CompanionAI::getStoredChatConfig($videos_id);
    $obj->subtitle = CompanionAI::getSubtitleState($videos_id);
    $obj->subtitleSwitcherEnabled = CompanionAI::isSubtitleSwitcherEnabled();
    $obj->subtitleLanguageLabel = '';
    if (!empty($obj->subtitle->lang) && is_string($obj->subtitle->lang)) {
        $obj->subtitleLanguageLabel = empty($global['bcp47'][$obj->subtitle->lang]['label']) ? $obj->subtitle->lang : $global['bcp47'][$obj->subtitle->lang]['label'];
    }
}

echo _json_encode($obj);
