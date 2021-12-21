<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

$_POST['comments_id'] = intval(@$_POST['comments_id']);

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = _json_decode($inputJSON, TRUE); //convert JSON into array
unset($_POST["redirectUri"]);
if(!empty($input) && empty($_POST)){
    foreach ($input as $key => $value) {
        $_POST[$key]=$value;
    }
}

if(!empty($_POST['user']) && !empty($_POST['pass'])){
    $user = new User(0, $_POST['user'], $_POST['pass']);
    $user->login(false, true);
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

if (!User::canComment()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

function isCommentASpam($comment, $videos_id){
    $comment = trim($comment);
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = '';
    $obj->time = time();
    if(empty($comment)){
        $obj->msg = __('Comment is empty');
        return $obj;
    }
    if(empty($videos_id)){
        $obj->msg = __('Video is empty');
        return $obj;
    }
    _session_start();
    if(!isset($_SESSION['comments'])){
        $_SESSION['comments'] = array();
    }
    
    // you can only comment each 5 seconds
    $rest = $_SESSION['comments']['_avideo_last_comment'] - ($obj->time-5); 
    if($rest>0){
        $obj->msg = __('You just comment something, please wait to comment again').', '.$rest.' '.__('Seconds');
        return $obj;
    }
    
    // if you already comment 3 times or more on the same video you must wait 30 seconds
    if($_SESSION['comments']['_avideo_count_comments_'.$videos_id]>3){
        $rest = $_SESSION['comments']['_avideo_last_comment'] - ($obj->time-30); 
        if($rest>0){
            $obj->msg = __('You just comment something, please wait to comment again').', '.$rest.' '.__('Seconds');
            return $obj;
        }
    }
    
    $index = preg_replace('/[^0-9a-z]/i', '', $comment);
    $obj->index = $index;
    // you can only repeat the comment (equal or similar) each 60 seconds
    if(!empty($_SESSION['comments'][$index])){
        $rest = $_SESSION['comments'][$index]->time - ($obj->time-60); 
        if($rest>0){
            $obj->msg = __('Please wait to comment again').', '.$rest.' '.__('Seconds');
            return $obj;            
        }
    }
    
    if(empty($_SESSION['comments']['_avideo_count_comments_'.$videos_id])){
        $_SESSION['comments']['_avideo_count_comments_'.$videos_id] = 1;
    }else{
        $_SESSION['comments']['_avideo_count_comments_'.$videos_id]++;
    }
    
    $_SESSION['comments']['_avideo_last_comment'] = $obj->time;
    $obj->error = false;
    $_SESSION['comments'][$index] = $obj;
    
    return $obj;
}

$isSpam = isCommentASpam($_POST['comment'], $_POST['video']);
if($isSpam->error){
    $obj->msg = $isSpam->msg;
    die(json_encode($obj));
}

require_once 'comment.php';
if(!empty($_POST['id'])){
    $_POST['id'] = intval($_POST['id']);
    if(Comment::userCanEditComment($_POST['id'])){
        $objC = new Comment("", 0, $_POST['id']);
        $objC->setComment($_POST['comment']);
    }
}else{
    $objC = new Comment($_POST['comment'], $_POST['video']);
    $objC->setComments_id_pai($_POST['comments_id']);
}

$obj->comments_id = $objC->save();
if(!empty($obj->comments_id)){
   $obj->error = false; 
   $obj->msg = __("Your comment has been saved!");
}else{
   $obj->msg = __("Your comment has NOT been saved!");
}
die(json_encode($obj));
