<?php
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
$head = AVideoPlugin::getHeadCode();
$custom = "The Best YouTube Clone Ever - AVideo";
$extraPluginFile = $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';

$custom = array();

if (file_exists($extraPluginFile) && AVideoPlugin::isEnabled("c4fe1b83-8f5a-4d1b-b912-172c608bf9e3")) {
    require_once $extraPluginFile;
    $ec = new ExtraConfig();
    $custom[] = $ec->getDescription();
}

if (!empty($poster)) {
    $subTitle = str_replace(array('"', "\n", "\r"), array("", "", ""), strip_tags($video['description']));
    $custom = array();
    $custom[] = $subTitle;
    $custom[] = $video["category"];
}

if (!empty($_GET['catName'])) {
    $category = Category::getCategoryByName($_GET['catName']);
    $description = str_replace(array('"', "\n", "\r"), array("", "", ""), strip_tags($category['description']));
    $custom = array();
    $custom[] = $description;
    $custom[] = $category['name'];
}

foreach ($custom as $key => $value) {
    if(empty($value)){
        unset($custom[$key]);
    }
}

if(!empty($metaDescription)){
    $metaDescription = implode(" - ", $custom)." - {$metaDescription}";
}else{
    $metaDescription = implode(" - ", $custom);
}
// for SEO to not rise an error of duplicated title or description of same pages with and without last slash
$metaDescription .= getSEOComplement();
$theme = $config->getTheme();
?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo $metaDescription; ?>">
<meta name="device_id" content="<?php echo getDeviceID(); ?>">

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
<link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
<link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
<meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">
<!--
<meta name="newCache" content="<?php echo ObjectYPT::checkSessionCacheBasedOnLastDeleteALLCacheTime()?"yes":"No" ?>">
<meta name="sessionCache" content="<?php echo humanTimingAgo(@$_SESSION['user']['sessionCache']['time']), " " ,@$_SESSION['user']['sessionCache']['time']; ?>">
<meta name="systemCache" content="<?php echo humanTimingAgo(ObjectYPT::getLastDeleteALLCacheTime()), " " ,ObjectYPT::getLastDeleteALLCacheTime(); ?>">
<meta name="sessionCache-systemCache" content="<?php $dif = @$_SESSION['user']['sessionCache']['time']-ObjectYPT::getLastDeleteALLCacheTime();
echo $dif, " Seconds "; ?>">
-->
<!-- <link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 768px)" href="view/css/mobile.css" /> -->
<link href="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/css/flagstrap/css/flags.css" rel="stylesheet" type="text/css"/>
<?php
$cssFiles = array();
//$cssFiles[] = "view/js/seetalert/sweetalert.css";
$cssFiles[] = "view/bootstrap/bootstrapSelectPicker/css/bootstrap-select.min.css";
$cssFiles[] = "view/js/bootgrid/jquery.bootgrid.css";
$cssFiles[] = "view/js/jquery-toast/jquery.toast.min.css";
$cssFiles[] = "view/css/custom/{$theme}.css";
$cssFiles = array_merge($cssFiles);
$cssURL = combineFiles($cssFiles, "css");
?>
<link href="<?php echo $cssURL; ?>" rel="stylesheet" type="text/css"/>
<?php
$filename = "{$global['systemRootPath']}videos/cache/custom.css";
if($theme === "default" && !empty($customizePlugin->showCustomCSS) && file_exists($filename)){
    echo '<link href="'.$global['webSiteRootURL'].'videos/cache/custom.css?'.  filectime($filename) .'" rel="stylesheet" type="text/css" id="pluginCustomCss" />';
}else{
    if($theme !== "default"){
        echo "<!-- theme is not default -->";
    }
    if(empty($customizePlugin->showCustomCSS)){
        echo "<!-- showCustomCSS is empty -->";
    }
    if(!file_exists($filename)){
        echo "<!-- css file does not exist -->";
    }
    echo '<link href="" rel="stylesheet" type="text/css" id="pluginCustomCss" />';
}
$cssFiles = array();
$cssFiles[] = "view/css/main.css";
$cssFiles = array_merge($cssFiles, AVideoPlugin::getCSSFiles());
$cssURL = combineFiles($cssFiles, "css");
?>
<link href="<?php echo $cssURL; ?>" rel="stylesheet" type="text/css"/>
<?php
if (isRTL()) {
    ?>
    <style>
        .principalContainer, #mainContainer, #bigVideo, .mainArea, .galleryVideo, #sidebar, .navbar-header li{
            direction:rtl;
            unicode-bidi:embed;
        }
        #sidebar .nav{
            padding-right: 0;
        }
        .dropdown-menu, .navbar-header li a, #sideBarContainer .btn {
            text-align: right !important;
        }
        .dropdown-submenu a{
            width: 100%;
        }
        .galeryDetails div{
            float: right !important;
        }
        #saveCommentBtn{
            border-width: 1px;
            border-right-width: 0;
        }
    </style>    
    <?php
}
?>
<script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.5.1.min.js"></script>
<script>
    var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
    var player;
</script>
<?php
if (!$config->getDisable_analytics()) {
    ?>
    <script>
        // AVideo Analytics
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-96597943-1', 'auto', 'aVideo');
        ga('aVideo.send', 'pageview');
    </script>
    <?php
}
echo $config->getHead();
echo $head;
if (!empty($video)) {
    if (!empty($video['users_id'])) {
        $userAnalytics = new User($video['users_id']);
        echo $userAnalytics->getAnalytics();
        unset($userAnalytics);
    }
}
ogSite();

?>
