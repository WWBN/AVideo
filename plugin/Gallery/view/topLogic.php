<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

$siteTitle = array();

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
    array_push($siteTitle, $currentCat['name']);
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
	
	array_push($siteTitle, __("Home"));
	
	// don't add a prefix for SEO, it's already handled here below by the implode() func	
	$seoComplement = getSEOComplement(array(
		"addAutoPrefix" => false,
		"addCategory" => false
	));
	if (!empty($seoComplement)) {
		array_push($siteTitle, $seoComplement);
	}

	$metaDescription = $video['id'];
} else {
	array_push($siteTitle, __("Video Not Available"));
	array_push($siteTitle, __("Home"));
	
	$metaDescription = __("Video Not Available");
}
array_push($siteTitle, $config->getWebSiteTitle());
$metaDescription .= $config->getPageTitleSeparator() . __("Home");

$siteTitle = implode($config->getPageTitleSeparator(), $siteTitle);
