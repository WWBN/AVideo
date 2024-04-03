<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
    exit;
}

$_page = new Page(array(''));
$_page->printEditorIndexFromFile(__FILE__);
?>