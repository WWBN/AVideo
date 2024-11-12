<?php
$tTolerance = 0.2;
$tname = TimeLogStart(basename(__FILE__));
if (isInfiniteScroll()) {
    echo '<!-- navbar hidden line='.__LINE__.' -->';
    return '';
} else if (isset($_GET['noNavbar'])) {
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
TimeLogEnd($tname, __LINE__, $tTolerance);
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
    echo '<!-- navbar hidden line='.__LINE__.' -->';
    echo '<nav class="hidden" id="mainNavBar" style="display:none;"></nav>';
    return '';
}
TimeLogEnd($tname, __LINE__, $tTolerance);
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
//_session_write_close();
global $includeDefaultNavBar, $global, $config, $advancedCustom, $advancedCustomUser;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
TimeLogEnd($tname, __LINE__, $tTolerance);
$_GET['parentsOnly'] = "1";
$lang = getLanguage();
$thisScriptFile = pathinfo($_SERVER["SCRIPT_FILENAME"]);
if (empty($sidebarStyle)) {
    $sidebarStyle = "display: none;";
}
$includeDefaultNavBar = true;
_ob_start();
TimeLogEnd($tname, __LINE__, $tTolerance);
echo AVideoPlugin::navBar();
TimeLogEnd($tname, __LINE__, $tTolerance);
if (!$includeDefaultNavBar) {
    echo '<!-- navbar hidden line='.__LINE__.' -->';
    return false;
}

if (!empty($_GET['avideoIframe']) && empty($_GET['ajaxLoad'])) { // comes from avideoModalIframe(url) javascript
    ?>
    <style>
        body,
        body>div.container-fluid>div.panel {
            padding: 0;
            margin: 0;
        }

        #mainFooter {
            display: none !important;
        }
    </style>
<?php
    echo '<!-- navbar hidden line='.__LINE__.' -->';
    return false;
}
?>
<link href="<?php echo getURL('view/css/navbar.css'); ?>" rel="stylesheet" type="text/css" />
<?php
if (isFirstPage()) {
    if (!empty($customizePluginDescription)) {
        echo "<span class='hidden metaDescription'>{$customizePluginDescription}</span>";
    } elseif (!empty($metaDescription)) {
        echo "<span class='hidden metaDescription'>{$metaDescription}</span>";
    }
}
TimeLogEnd($tname, __LINE__, $tTolerance);
if (!User::isLogged() && !empty($advancedCustomUser->userMustBeLoggedIn) && !empty($advancedCustomUser->userMustBeLoggedInCloseButtonURL)) {
    TimeLogEnd($tname, __LINE__, $tTolerance);
    include $global['systemRootPath'] . 'view/include/navbarCloseButton.php';
    TimeLogEnd($tname, __LINE__, $tTolerance);
} elseif (((empty($advancedCustomUser->userMustBeLoggedIn) && empty($advancedCustom->disableNavbar)) || $thisScriptFile["basename"] === "signUp.php" || $thisScriptFile["basename"] === "userRecoverPass.php") || User::isLogged()) {
    TimeLogEnd($tname, __LINE__, $tTolerance);
    $updateFiles = getUpdatesFilesArray();
?>
    <nav class="navbar navbar-default navbar-fixed-top navbar-expand-lg navbar-light bg-light" id="mainNavBar">
        <ul class="items-container">
            <?php
            TimeLogEnd($tname, __LINE__, $tTolerance);
            echo getIncludeFileContent($global['systemRootPath'] . 'view/include/navbarMenuAndLogo.php', [], true);
            TimeLogEnd($tname, __LINE__, $tTolerance);
            if(!isBot()){
                echo getIncludeFileContent($global['systemRootPath'] . 'view/include/navbarSearch.php', [], true);
            }
            TimeLogEnd($tname, __LINE__, $tTolerance);
            ?>

            <li id="lastItemOnMenu">
                <div class="pull-right" id="myNavbar">
                    <ul class="right-menus align-center" style="padding-left: 0;">
                        <?php
                        TimeLogEnd($tname, __LINE__, $tTolerance);
                        echo AVideoPlugin::getHTMLMenuRight();
                        TimeLogEnd($tname, __LINE__, $tTolerance);
                        include $global['systemRootPath'] . 'view/include/navbarLang.php';
                        TimeLogEnd($tname, __LINE__, $tTolerance);
                        include $global['systemRootPath'] . 'view/include/navbarRightSignIn.php';
                        TimeLogEnd($tname, __LINE__, $tTolerance);
                        ?>
                    </ul>
                </div>
                <div class="pull-right">
                    <?php
                    TimeLogEnd($tname, __LINE__, $tTolerance);
                    echo getHamburgerButton('buttonMyNavbar', 'x');
                    TimeLogEnd($tname, __LINE__, $tTolerance);
                    ?>
                </div>
                <?php
                TimeLogEnd($tname, __LINE__, $tTolerance);
                include $global['systemRootPath'] . 'view/include/navbarRightProfile.php';
                TimeLogEnd($tname, __LINE__, $tTolerance);
                ?>
            </li>
        </ul>
        <?php
        TimeLogEnd($tname, __LINE__, $tTolerance);
        $varsArray = array('sidebarStyle' => $sidebarStyle);
        $filePath = $global['systemRootPath'] . 'view/include/navbarSidebar.php';
        echo getIncludeFileContent($filePath, $varsArray, true);
        //echo getIncludeFileContent($filePath);
        //include $filePath;
        TimeLogEnd($tname, __LINE__, $tTolerance);
        ?>
    </nav>
    <script src="<?php echo getURL('view/js/navbarLogged.js'); ?>" type="text/javascript"></script>
<?php
    if (!empty($advancedCustom->underMenuBarHTMLCode->value)) {
        echo $advancedCustom->underMenuBarHTMLCode->value;
    }
} elseif ($thisScriptFile["basename"] !== 'user.php' && empty($advancedCustom->disableNavbar)) {
}
TimeLogEnd($tname, __LINE__, $tTolerance);
echo '<!-- navBarAfter start -->', AVideoPlugin::navBarAfter(), '<!-- navBarAfter end -->';
TimeLogEnd($tname, __LINE__, $tTolerance);
unset($_GET['parentsOnly']);
?>