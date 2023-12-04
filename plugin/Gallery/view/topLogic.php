<?php

global $global, $config;
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
require_once $global['systemRootPath'] . 'objects/user.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
require_once $global['systemRootPath'] . 'objects/functions.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';

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
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
require_once $global['systemRootPath'] . 'objects/category.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
$currentCat;
if (!empty($_REQUEST['catName'])) {
    $currentCat = Category::getCategoryByName($_REQUEST['catName']);
    if(!empty($currentCat)){
        array_push($siteTitle, $currentCat['name']);
    }
}
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';

require_once $global['systemRootPath'] . 'objects/video.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
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
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
$video = Video::getVideo("", "viewable", !$obj->hidePrivateVideos, false, true);
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
$debugLastGetVideoSQL = $lastGetVideoSQL;
if (empty($video)) {
    $video = Video::getVideo("", "viewable", !$obj->hidePrivateVideos, true);
    echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
    $debugLastGetVideoSQL = $lastGetVideoSQL;
}
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
$total = 0;
$totalPages = 0;
$url = '';
$metaDescription = "";
if (!empty($video)) {
    if (strpos($_SERVER['REQUEST_URI'], "/cat/") === false) {
        $url = $global['webSiteRootURL'] . "page/";
    } else {
        $url = $global['webSiteRootURL'] . "cat/" . $video['clean_category'] . "/page/";
    }
    global $contentSearchFound;
    if (empty($contentSearchFound)) {
        $contentSearchFound = !empty($videos);
    }
    //array_push($siteTitle, __("Home"));
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
    //array_push($siteTitle, __("Home"));

    $metaDescription = __("Video Not Available");
}
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
array_push($siteTitle, $config->getWebSiteTitle());
$metaDescription .= $config->getPageTitleSeparator();
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';

$siteTitle = implode($config->getPageTitleSeparator(), $siteTitle);
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
