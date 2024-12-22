<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';
_session_write_close();

if (!User::isLogged()) {
    forbiddenPage('You must login');
}

if (empty($_REQUEST['customUrl'])) {
    forbiddenPage('customUrl is empty');
}

$obj = new stdClass();
$obj->msg = "";
$obj->users_id = User::getId();
$obj->redirectCustomUrl = $_REQUEST['customUrl'];
$obj->redirectCustomMessage = $_REQUEST['customMessage'];
$obj->autoRedirect = !_empty($_REQUEST['autoRedirect']) ? 1 : 0;
$obj->setRedirectCustomUrl = User::setRedirectCustomUrl(User::getId(), array('url'=>$obj->redirectCustomUrl, 'msg'=>$obj->redirectCustomMessage, 'autoRedirect'=>$obj->autoRedirect));
$obj->error = empty($obj->setRedirectCustomUrl);

echo json_encode($obj);
