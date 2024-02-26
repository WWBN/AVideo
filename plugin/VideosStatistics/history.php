<?php
require_once '../../videos/configuration.php';
$_page = new Page(array('History'));
include $global['systemRootPath'] . 'plugin/VideosStatistics/historyContent.php';
$_page->print();
?>