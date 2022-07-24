<?php
_ob_start();
if (isset($_GET['noNavbar'])) {
    _session_start();
    if (!empty($_GET['noNavbar'])) {
        $_SESSION['noNavbar'] = 1;
    } else {
        $_SESSION['noNavbar'] = 0;
        $_SESSION['noNavbarClose'] = 0;
    }
} else {
    if (!isIframe()) {
        _session_start();
        unset($_SESSION['noNavbar']);
    }
}
if (!empty($_SESSION['noNavbar'])) {
    if (isset($_GET['noNavbarClose'])) {
        _session_start();
        if (!empty($_GET['noNavbar'])) {
            $_SESSION['noNavbarClose'] = 1;
        } else {
            $_SESSION['noNavbarClose'] = 0;
        }
    }
    if (empty($_SESSION['noNavbarClose'])) {
        //$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $actual_link = basename($_SERVER['PHP_SELF']);
        $params = $_GET;
        unset($params['noNavbar']);
        $params['noNavbar'] = "0";
        $new_query_string = http_build_query($params);
        ?>
        <a href="<?php echo $actual_link, "?", $new_query_string; ?>" class="btn btn-default" style="position: absolute; right: 10px; top: 5px;"><i class="fas fa-bars"></i></a>    
        <?php
    } else {
        echo '<style>body{padding-top:0;}</style>';
    }
    echo '<nav class="hidden" id="mainNavBar" style="display:none;"></nav>';
    return '';
}
if (!empty($advancedCustomUser->keepViewerOnChannel)) {
    if (!empty($_GET['channelName'])) {
        _session_start();
        $_SESSION['channelName'] = $_GET['channelName'];
    }
    if (!empty($_GET['leaveChannel'])) {
        _session_start();
        unset($_SESSION['channelName']);
    }
}
session_write_close();
global $includeDefaultNavBar, $global, $config, $advancedCustom, $advancedCustomUser;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$_GET['parentsOnly'] = "1";
if (empty($_SESSION['language'])) {
    $lang = $config->getLanguage();
    if (empty($lang)) {
        $lang = 'us_EN';
    }
} else {
    $lang = $_SESSION['language'];
}

$thisScriptFile = pathinfo($_SERVER["SCRIPT_FILENAME"]);
if (empty($sidebarStyle)) {
    $sidebarStyle = "display: none;";
}
$includeDefaultNavBar = true;
_ob_start();
echo AVideoPlugin::navBar();
if (!$includeDefaultNavBar) {
    return false;
}

if (!empty($_GET['avideoIframe'])) { // comes from avideoModalIframe(url) javascript
    ?>
    <style>
        body, body > div.container-fluid > div.panel {
            padding: 0;
            margin: 0;
        }
        #mainFooter{
            display: none !important;
        }
    </style>
    <?php
    return false;
}
?>
<link href="<?php echo getURL('view/css/navbar.css'); ?>" rel="stylesheet" type="text/css"/>
<?php
if (!empty($customizePluginDescription)) {
    echo "<span class='hidden metaDescription'>{$customizePluginDescription}</span>";
} elseif (!empty($metaDescription)) {
    echo "<span class='hidden metaDescription'>{$metaDescription}</span>";
}
if (!User::isLogged() && !empty($advancedCustomUser->userMustBeLoggedIn) && !empty($advancedCustomUser->userMustBeLoggedInCloseButtonURL)) {
    include $global['systemRootPath'] . 'view/include/navbarCloseButton.php';
} elseif (((empty($advancedCustomUser->userMustBeLoggedIn) && empty($advancedCustom->disableNavbar)) || $thisScriptFile["basename"] === "signUp.php" || $thisScriptFile["basename"] === "userRecoverPass.php") || User::isLogged()) {
    $updateFiles = getUpdatesFilesArray();
    ?>
    <nav class="navbar navbar-default navbar-fixed-top navbar-expand-lg navbar-light bg-light" id="mainNavBar">
        <ul class="items-container">
            <?php
            include $global['systemRootPath'] . 'view/include/navbarMenuAndLogo.php';
            include $global['systemRootPath'] . 'view/include/navbarSearch.php';
            ?>

            <li id="lastItemOnMenu">
                <div class="pull-right" id="myNavbar">
                    <ul class="right-menus align-center" style="padding-left: 0;">
                        <?php
                        echo AVideoPlugin::getHTMLMenuRight();
                        include $global['systemRootPath'] . 'view/include/navbarLang.php';
                        include $global['systemRootPath'] . 'view/include/navbarRightSignIn.php';
                        ?>
                    </ul>
                </div>
                <div class="pull-right">
                    <?php
                    echo getHamburgerButton('buttonMyNavbar', 'x');
                    ?>
                </div>
                <?php
                include $global['systemRootPath'] . 'view/include/navbarRightProfile.php';
                ?>
            </li>
        </ul>
        <?php
        include $global['systemRootPath'] . 'view/include/navbarSidebar.php';
        ?>
    </nav>
    <script src="<?php echo getURL('view/js/navbarLogged.js'); ?>" type="text/javascript"></script>
    <?php
    if (!empty($advancedCustom->underMenuBarHTMLCode->value)) {
        echo $advancedCustom->underMenuBarHTMLCode->value;
    }
} elseif ($thisScriptFile["basename"] !== 'user.php' && empty($advancedCustom->disableNavbar)) {
    
}
echo '<!-- navBarAfter start -->', AVideoPlugin::navBarAfter(), '<!-- navBarAfter end -->';
unset($_GET['parentsOnly']);
?>
