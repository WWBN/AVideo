<?php
global $global, $config;
require_once __DIR__ . '/../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');

if (!User::isLogged()) {
    forbiddenPage('Permission denied', true);
}

setRowCount(10);
if (empty($_POST['sort'])) {
    $_POST['sort'] = [];
    $_POST['sort']['id'] = 'DESC';
}

$type = 'posted';
if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], ['received', 'all'], true)) {
    $type = $_REQUEST['type'];
}

if ($type === 'all') {
    if (!User::isAdmin()) {
        forbiddenPage('Permission denied', true);
    }
    $comments = Comment::getAllPostedComments(true);
    $total = Comment::getTotalAllPostedComments();
} elseif ($type === 'received') {
    $comments = Comment::getCommentsOnMyVideos(true);
    $total = Comment::getTotalCommentsOnMyVideos();
} else {
    $comments = Comment::getMyPostedComments(true);
    $total = Comment::getTotalMyPostedComments();
}

$comments = Comment::addExtraInfo2InRows($comments);

$obj = new stdClass();
$obj->current = getCurrentPage();
$obj->rowCount = getRowCount();
$obj->total = $total;
$obj->rows = $comments;

echo json_encode($obj);
