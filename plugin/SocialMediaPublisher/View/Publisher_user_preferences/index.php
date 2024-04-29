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

//$status = SocialMediaPublisher::upload(13, 1438); var_dump($status); exit;

if(empty($plugin)){
    forbiddenPage('Plugin SocialMediaPublisher is disabled');
}
$_page = new Page(array('SocialMediaPublisher'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/index_body.php';
if(User::isAdmin()){
    include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_social_medias/index_body.php';
}
$_page->print();
?>