<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';

header('Content-Type: application/json');

forbidIfNotPost();
inputToRequest();

$resp = new stdClass();
$resp->error = true;
$resp->msg = "Not ready";

if (empty($_REQUEST['videos_id']) && !empty($_GET['videos_id'])) {
    $_REQUEST['videos_id'] = $_GET['videos_id'];
}
if (!empty($_GET['user']) && !empty($_GET['pass'])) {
    $user = new User(0, $_GET['user'], $_GET['pass']);
    $user->login(false, true);
}
// Mobile apps send the credentials in the POST body (same as objects/*.json.php)
User::loginFromRequestIfNotLogged();

if (empty($_REQUEST['videos_id']) && empty($_REQUEST['comments_id']) && empty($_REQUEST['reported_users_id'])) {
    $resp->msg = "The video is empty";
    die(json_encode($resp));
}

if (!User::isLogged()) {
    forbiddenPage('User not logged', true);
}

$plugin = AVideoPlugin::loadPluginIfEnabled('ReportVideo');

if (empty($plugin)) {
    $resp->msg = "Plugin not enabled";
    die(json_encode($resp));
}

$videos_id = intval($_REQUEST['videos_id'] ?? 0);
$reported_users_id = intval($_REQUEST['reported_users_id'] ?? 0);
$comments_id = intval($_REQUEST['comments_id'] ?? 0);
if ($videos_id > 0) {
    forbiddenPageIfCannotWatchVideo($videos_id);
} elseif ($reported_users_id > 0) {
    $reportedUser = new User($reported_users_id);
    if (empty($reportedUser->getUser())) {
        $resp->msg = __('User not found');
        die(json_encode($resp));
    }
} elseif ($comments_id > 0) {
    require_once $global['systemRootPath'] . 'objects/comment.php';
    $comment = Comment::getComment($comments_id);
    if (empty($comment)) {
        $resp->msg = __('Comment not found');
        die(json_encode($resp));
    }
    forbiddenPageIfCannotWatchVideo($comment['videos_id']);
} else {
    $resp->msg = __('Invalid report target');
    die(json_encode($resp));
}

$obs = ReportVideo::sanitizeReportReason($_REQUEST['obs'] ?? '');
try {
    if ($videos_id > 0) {
        $resp = $plugin->report(User::getId(), $videos_id, $obs);
    } else {
        // User/live and comment reports are delivered only by email: do not report
        // success when the site's only copy of the report could not be delivered.
        $subject = $reported_users_id > 0
            ? "User {$reported_users_id} was reported as inappropriate"
            : "Comment {$comments_id} was reported as inappropriate";
        $message = $subject . " by user " . intval(User::getId());
        if (!empty($obs)) {
            $message .= ". Reason: " . htmlspecialchars($obs, ENT_QUOTES, 'UTF-8');
        }
        $resp->error = !sendEmailToSiteOwner($subject, $message);
        if ($resp->error) {
            $resp->msg = __('Could not send the report. Please try again later');
            _error_log('ReportVideo: report email not sent: ' . $subject);
        } else {
            $resp->msg = $reported_users_id > 0
                ? __('This user was reported to our team, we will review it soon')
                : __('This comment was reported to our team, we will review it soon');
        }
    }
} catch (\Throwable $th) {
    _error_log('ReportVideo report: ' . $th->getMessage());
    $resp->error = true;
    $resp->msg = __('An error occurred');
}
die(json_encode($resp));
