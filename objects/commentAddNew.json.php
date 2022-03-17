<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$_REQUEST['comments_id'] = intval(@$_REQUEST['comments_id']);

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = _json_decode($inputJSON, true); //convert JSON into array
unset($_REQUEST["redirectUri"]);
if (!empty($input) && empty($_REQUEST)) {
    foreach ($input as $key => $value) {
        $_REQUEST[$key]=$value;
    }
}

if (!empty($_REQUEST['user']) && !empty($_REQUEST['pass'])) {
    $user = new User(0, $_REQUEST['user'], $_REQUEST['pass']);
    $user->login(false, true);
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

if (!User::canComment()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

function isCommentASpam($comment, $videos_id)
{
    $comment = trim($comment);
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = '';
    $obj->time = time();
    if (empty($comment)) {
        $obj->msg = __('Comment is empty');
        return $obj;
    }
    if (empty($videos_id)) {
        $obj->msg = __('Video is empty');
        return $obj;
    }
    _session_start();
    if (!isset($_SESSION['comments'])) {
        $_SESSION['comments'] = [];
    }

    // you can only comment each 5 seconds
    $rest = $_SESSION['comments']['_avideo_last_comment'] - ($obj->time-5);
    if ($rest>0) {
        $obj->msg = __('You just comment something, please wait to comment again').', '.$rest.' '.__('Seconds');
        return $obj;
    }

    // if you already comment 3 times or more on the same video you must wait 30 seconds
    if ($_SESSION['comments']['_avideo_count_comments_'.$videos_id]>3) {
        $rest = $_SESSION['comments']['_avideo_last_comment'] - ($obj->time-30);
        if ($rest>0) {
            $obj->msg = __('You just comment something, please wait to comment again').', '.$rest.' '.__('Seconds');
            return $obj;
        }
    }

    $index = preg_replace('/[^0-9a-z]/i', '', $comment);
    $obj->index = $index;
    // you can only repeat the comment (equal or similar) each 60 seconds
    if (!empty($_SESSION['comments'][$index])) {
        $rest = $_SESSION['comments'][$index]->time - ($obj->time-60);
        if ($rest>0) {
            $obj->msg = __('You just comment something similar, please wait to comment again').', '.$rest.' '.__('Seconds');
            return $obj;
        }
    }

    if (empty($_SESSION['comments']['_avideo_count_comments_'.$videos_id])) {
        $_SESSION['comments']['_avideo_count_comments_'.$videos_id] = 1;
    } else {
        $_SESSION['comments']['_avideo_count_comments_'.$videos_id]++;
    }

    $_SESSION['comments']['_avideo_last_comment'] = $obj->time;
    $obj->error = false;
    $_SESSION['comments'][$index] = $obj;

    return $obj;
}

require_once 'comment.php';
if (empty($_REQUEST['video']) && !empty($_REQUEST['comments_id'])) {
    $c = new Comment('', '', $_REQUEST['comments_id']);
    $_REQUEST['video'] = $c->getVideos_id();
}

$isSpam = isCommentASpam($_REQUEST['comment'], $_REQUEST['video']);
if ($isSpam->error) {
    $obj->msg = $isSpam->msg;
    die(json_encode($obj));
}

if (!empty($_REQUEST['id'])) {
    $_REQUEST['id'] = intval($_REQUEST['id']);
    if (Comment::userCanEditComment($_REQUEST['id'])) {
        $objC = new Comment("", 0, $_REQUEST['id']);
        $objC->setComment($_REQUEST['comment']);
    }
} else {
    $objC = new Comment($_REQUEST['comment'], $_REQUEST['video']);
    $objC->setComments_id_pai($_REQUEST['comments_id']);
}

$obj->comments_id = $objC->save();
if (!empty($obj->comments_id)) {
    $obj->error = false;
    $obj->msg = __("Your comment has been saved!");
} else {
    $obj->msg = __("Your comment has NOT been saved!");
}
die(json_encode($obj));
