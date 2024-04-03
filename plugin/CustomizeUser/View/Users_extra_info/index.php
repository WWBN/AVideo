<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("Must be admin");
}

$_page = new Page(array(''));
$_page->printEditorIndexFromFile(__FILE__);
?>