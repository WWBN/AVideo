<?php

require_once $global['systemRootPath'].'objects/functions.php';
// filter some security here
$securityFilter = array('error', 'catName', 'type', 'channelName', 'captcha', 'showOnly', 'key', 'link');
$securityFilterInt = array('videos_id', 'video_id', 'categories_id', 'user_id', 'users_id');

if(isset($_GET['search'])){
    if(is_string($_GET['search'])){
        $_GET['search'] = trim($_GET['search']);
    }else if(is_array($_GET['search'])){
        foreach ($_GET['search'] as $key => $value) {
            if(is_string($_GET['search'][$key])){
                $_GET['search'][$key] = trim($_GET['search'][$key]);
            }
        }
    }
}

foreach ($securityFilterInt as $value) {
    if (!empty($_POST[$value])) {
        $_POST[$value] = intval($_POST[$value]);
    }
    if (!empty($_GET[$value])) {
        $_GET[$value] = intval($_GET[$value]);
    }
}

foreach ($securityFilter as $value) {
    if (!empty($_POST[$value])) {
        $_POST[$value] = str_replace(array("'",'"',"&quot;","&#039;"), array('','','',''), xss_esc($_POST[$value]));
    }
    if (!empty($_GET[$value])) {
        $_GET[$value] = str_replace(array("'",'"',"&quot;","&#039;"), array('','','',''), xss_esc($_GET[$value]));
    }
}

if(!empty($_GET['sort']) && is_array($_GET['sort'])){
    foreach ($_GET['sort'] as $key => $value) {
        $_GET['sort'][xss_esc($key)] = strcasecmp($value, "ASC")===0?"ASC":"DESC";
    }
}
if(!empty($_POST['sort']) && is_array($_POST['sort'])){
    foreach ($_POST['sort'] as $key => $value) {
        $_POST['sort'][xss_esc($key)] = strcasecmp($value, "ASC")===0?"ASC":"DESC";
    }
}
