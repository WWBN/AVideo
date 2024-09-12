<?php
require_once '../../videos/configuration.php';
$_page = new Page(array('History'));
$_page->setExtraStyles(
    array(
        'plugin/Gallery/style.css',
    )
);
include $global['systemRootPath'] . 'plugin/VideosStatistics/historyContent.php';
$_page->print();
?>