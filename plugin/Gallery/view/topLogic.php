<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

$siteTitle = $config->getWebSiteTitle();

$obj = AVideoPlugin::getObjectData("Gallery");
if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else {
        unset($_SESSION['type']);
    }
}
require_once $global['systemRootPath'] . 'objects/category.php';
$currentCat;
if (!empty($_GET['catName'])) {
    $currentCat = Category::getCategoryByName($_GET['catName']);
    $siteTitle = "{$currentCat['name']}";
}

require_once $global['systemRootPath'] . 'objects/video.php';
$orderString = "";
if ($obj->sortReverseable) {
    if (strpos($_SERVER['REQUEST_URI'], "?") != false) {
        $orderString = $_SERVER['REQUEST_URI'] . "&";
    } else {
        $orderString = $_SERVER['REQUEST_URI'] . "/?";
    }
    $orderString = str_replace("&&", "&", $orderString);
    $orderString = str_replace("//", "/", $orderString);
}
$video = Video::getVideo("", "viewable", !$obj->hidePrivateVideos, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewable", !$obj->hidePrivateVideos, true);
}
if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$total = 0;
$totalPages = 0;
$url = '';
$args = '';
$metaDescription = "";
if(!empty($video)){
    if (strpos($_SERVER['REQUEST_URI'], "?") != false) {
        $args = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "?"), strlen($_SERVER['REQUEST_URI']));
    }
    if (strpos($_SERVER['REQUEST_URI'], "/cat/") === false) {
        $url = $global['webSiteRootURL'] . "page/";
    } else {
        $url = $global['webSiteRootURL'] . "cat/" . $video['clean_category'] . "/page/";
    }
    $contentSearchFound = false;
    // for SEO to not rise an error of duplicated title or description of same pages with and without last slash
    $siteTitle .= getSEOComplement();
    $metaDescription = " ".$video['id'];
    // make sure the www has a different title and description than non www
    if(strrpos($_SERVER['HTTP_HOST'], 'www.')=== false){
        $siteTitle .= ": ".__("Home");
        $metaDescription .= ": ".__("Home");
    }
}