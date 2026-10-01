<?php
/**
 * Alerts sent by the Encoder monitor cron (Encoder: install/cron.php, objects/EncoderMonitor.php).
 *
 * - type waiting|processing|error (and *_reminder): e-mails the owner of videos_id. The Encoder
 *   authenticates with the job's video_id_hash, exactly like aVideoEncoderLog.json.php.
 * - type system: e-mails the authenticated account itself (an Encoder admin).
 *
 * The recipient always comes from this database, never from the request. Responses use
 * "skipped"/"permanent" so the Encoder does not retry something that cannot succeed.
 * CSRF: covered by autoCSRFGuard(), which trusts the Encoder user agent (no bypass entry needed).
 *
 * Never add videos_id, video_id or video_id_hash to this response: Encoder::sendToStreamer()
 * saves those into the Encoder job, and system alerts are sent with an unsaved job object.
 */
if (empty($global)) {
    $global = [];
}
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/EncoderAlert.php';
header('Content-Type: application/json');

inputToRequest();
if (!isset($_REQUEST['encodedPass'])) {
    $_REQUEST['encodedPass'] = 1;
}
useVideoHashOrLogin();
if (!User::isLogged()) {
    forbiddenPage('Permission denied', true);
}

$isSystemAlert = isset($_REQUEST['type']) && $_REQUEST['type'] === 'system';
$videos_id = 0;
if ($isSystemAlert) {
    enforceRateLimit('aVideoEncoderAlert_system_' . User::getId(), 20, 3600);
} else {
    $videos_id = intval(@$_REQUEST['videos_id']);
    if (empty($videos_id) || !User::canUpload() || !Video::canEncoderEdit($videos_id)) {
        forbiddenPage('Permission denied', true);
    }
    enforceRateLimit('aVideoEncoderAlert_' . $videos_id, 10, 3600);
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->skipped = false;
$obj->permanent = false;

try {
    if ($isSystemAlert) {
        $alert = EncoderAlert::readSystemAlert($_REQUEST);
        $users_id = User::getId();
    } else {
        $alert = EncoderAlert::readOwnerAlert($_REQUEST);
        $users_id = Video::getOwner($videos_id);
    }
    $video = null;
    if (!$isSystemAlert && !empty($alert)) {
        $video = new Video('', '', $videos_id, true);
    }
    $stillWaitingForEncoder = [Video::STATUS_ENCODING, Video::STATUS_ENCODING_ERROR, Video::STATUS_DOWNLOADING, Video::STATUS_TRANFERING, Video::STATUS_ACTIVE_AND_ENCODING];
    if (empty($alert)) {
        $obj->permanent = true;
        $obj->msg = 'Unsupported alert';
    } elseif (!$isSystemAlert && !in_array($video->getStatus(), $stillWaitingForEncoder, true)) {
        // The video was fixed, sent again or published meanwhile; an old encoder job must not
        // tell the owner that it failed.
        $obj->error = false;
        $obj->skipped = true;
        $obj->msg = 'Video is no longer waiting for the encoder';
    } else {
        $email = empty($users_id) ? '' : User::getEmailDb($users_id);
        if (empty($email) || !isValidEmail($email)) {
            // Nothing to retry: the recipient has no usable address.
            $obj->error = false;
            $obj->skipped = true;
            $obj->msg = 'Recipient has no valid email';
        } else {
            if ($isSystemAlert) {
                $mail = EncoderAlert::buildSystemEmail($alert);
            } else {
                $owner = new User($users_id);
                $manageURL = $global['webSiteRootURL'] . 'mvideos?video_id=' . $videos_id;
                $mail = EncoderAlert::buildOwnerEmail($alert, $video->getTitle(), $owner->getNameIdentificationBd(), $manageURL);
            }
            $sent = sendSiteEmail($email, $mail['subject'], $mail['body']);
            $obj->error = empty($sent);
            $obj->msg = empty($sent) ? 'Email was not sent' : 'Email sent';
            _error_log('aVideoEncoderAlert.json: ' . ($isSystemAlert ? 'system ' . $alert['check'] : $alert['type'] . ' videos_id=' . $videos_id) . ' users_id=' . intval($users_id) . ' sent=' . ($sent ? 'yes' : 'no'));
        }
    }
} catch (\Throwable $th) {
    $obj->error = true;
    $obj->msg = 'An error occurred';
    _error_log('aVideoEncoderAlert.json: ' . $th->getMessage(), AVideoLog::$ERROR);
}

echo json_encode($obj);
