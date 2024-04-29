<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__.'/../../../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('You can not do this');
    exit;
}
$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
if(empty($plugin)){
    forbiddenPage('Plugin SocialMediaPublisher is disabled');
}
$_page = new Page(array('SocialMediaPublisher'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
require $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_video_publisher_logs/index_body.php';
$_page->print();
?>