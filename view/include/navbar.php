<?php
if (isset($_GET['noNavbar'])) {
    _session_start();
    if (!empty($_GET['noNavbar'])) {
        $_SESSION['noNavbar'] = 1;
    } else {
        $_SESSION['noNavbar'] = 0;
        $_SESSION['noNavbarClose'] = 0;
    }
}
if (isIframe() && !empty($_SESSION['noNavbar'])) {
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
    if(empty($lang)){
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
AVideoPlugin::navBar();
if (!$includeDefaultNavBar) {
    return false;
}

if(!empty($_GET['avideoIframe'])){ // comes from avideoModalIframe(url) javascript
    ?>
        <style>body{padding: 0;}#mainFooter{display: none !important;}</style>
    <?php
    return false;
}
?>
<style>
    /* if it is IE */
    @media all and (-ms-high-contrast:none){
        nav ul.items-container li:first-child {
            display: block;
            flex: 0 1 auto; /* Default */
        }
    }

    #mysearch.in,
    #mysearch.collapsing {
        display: block!important;
    }

    #myNavbar.in,
    #myNavbar.collapsing {
        display: block!important;
    }
    #searchForm {
        width: 100%;
        margin-left: 5px;
        white-space: nowrap;
    }
    #searchForm .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left-width: 0;
    }
    #searchForm input {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right-width: 0;
    }

    #rightProfileButton{
        padding: 0; 
        margin-left: 5px; 
        margin-right: 40px; 
        border: 0;
        background: none;
        background-color: transparent;
    }

    #rightLoginButton{
        margin-left: 5px; 
        margin-right: 40px; 
    }

    #navbarRegularButtons{
        max-width: 70%;
        /* remove the scroll because the dropsown menus does not work */
        /*overflow-x: auto;*/
        /*overflow-y: hidden;*/
    }

    #navbarRegularButtons span.hidden-mdx{
        max-width: 15vw;
        display: inline-block;
    }

    #navbarRegularButtons .btn{
        overflow: hidden;
    }

    #navbarRegularButtons::-webkit-scrollbar {
        height: 4px;
    }

    @media (max-width : 992px) {
        #searchForm input{
            width: 100px;
        }
    }
    @media (max-width : 767px) {
        #searchForm {
            padding-left: 10px;
        }
        #rightLoginButton, #rightProfileButton{
            margin-right: 5px; 
            margin-left: 0;
        }

        #searchForm > div{
            width: 100%;
        }

        .mobilesecondnav {
            position: absolute; left: 40%; right: 5px;
        }

        #mysearch{
            /* width: 100%; */
            position: absolute;
            right: 0;
            left: 0;
            padding-left: 0px;
            padding-right: 0px;
            background-color: #FFF;

        }

        #myNavbar{
            position: absolute;
            right: 0;
            top: 50px;
            background-color: #FFF;
            padding: 4px;
            width: 50%;
        }
        #mainNavBar .navbar-brand{
            width: 100% !important;
            text-align: center;
        }
        #mainNavBar .navbar-brand>img {
            display: unset;
        }

        #myNavbar ul.right-menus{
            display: block;
        }

        #myNavbar ul.right-menus li{
            margin: 0;
            padding: 0;
        }
        #myNavbar ul.right-menus .btn, #myNavbar ul.right-menus .btn-group{
            margin: 2px;
            width: 100%;
        }
        #myNavbar ul.right-menus .btn-group{
            margin: 0;
        }
        nav ul.items-container li:first-child {
            display: list-item;
        }
        #navbarRegularButtons span.hidden-mdx {
            max-width: 100vw;
        }
        .globalsearchfield {
            width: 90% !important;
        }

        .searchli {
            width: 100%;
            margin-right: 0;
            margin-left: 0;

        }
        .searchdiv {

        }
        .navbar-toggle {
            margin-right: 5px !important;


        }
        .left-side {
            padding: 0 5px;
        }
        .searchul{
            padding-left: 0px;
        }
    }

    li.navsub-toggle .badge {
        float: right;
    }
    li.navsub-toggle a + ul {
        padding-left: 15px;
    }
    
    .navbar-lang-btn .select2-container{
        margin: 8px 0;
    }
    .navbar-lang-btn .select2-selection{
        border-color: #00000077 !important;
    }
    <?php
    if (AVideoPlugin::isEnabledByName("Gallery") || AVideoPlugin::isEnabledByName("YouPHPFlix2")) {
        ?>
        @media screen and (min-width: 992px) {

            body.youtube>div.container-fluid{
                margin-left: 300px;
            }
            body.youtube div.container-fluid .col-sm-10.col-sm-offset-1.list-group-item{
                margin-left: 0;
                margin-right: 0;
                width: 100%;
            }
        }
        <?php
    }
    ?>
</style>
<?php
if (!User::isLogged() && !empty($advancedCustomUser->userMustBeLoggedIn) && !empty($advancedCustomUser->userMustBeLoggedInCloseButtonURL)) {
    ?>
    <nav class="navbar navbar-default navbar-fixed-top " id="mainNavBar">
        <div class="pull-right">
            <a id="buttonMyNavbar" class=" btn btn-default navbar-btn" style="padding: 6px 12px; margin-right: 40px;" href="<?php echo $advancedCustomUser->userMustBeLoggedInCloseButtonURL; ?>">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </nav>
    <?php
} else if (((empty($advancedCustomUser->userMustBeLoggedIn) && empty($advancedCustom->disableNavbar)) || $thisScriptFile["basename"] === "signUp.php" || $thisScriptFile["basename"] === "userRecoverPass.php") || User::isLogged()) {
    $updateFiles = getUpdatesFilesArray();
    ?>
    <nav class="navbar navbar-default navbar-fixed-top " id="mainNavBar">
        <ul class="items-container">
            <li>
                <ul class="left-side">
                    <li style="max-width: 40px;">
                        <button class="btn btn-default navbar-btn pull-left" id="buttonMenu"  data-toggle="tooltip" title="<?php echo __("Main Menu"); ?>" data-placement="right" ><span class="fa fa-bars"></span></button>
                        <script>
                            function YPTSidebarOpen() {
                                $('body').addClass('youtube')
                                $("#sidebar").fadeIn();
                                youTubeMenuIsOpened = true;
                            }
                            function YPTSidebarClose() {
                                $('body').removeClass('youtube');
                                $("#sidebar").fadeOut();
                                youTubeMenuIsOpened = false;
                            }

                            function YPTHidenavbar() {
                                if (typeof inIframe == 'undefined') {
                                    setTimeout(function () {
                                        YPTHidenavbar()
                                    }, 500);
                                } else {
                                    if (inIframe()) {
                                        $("#mainNavBar").hide();
                                        $("body").css("padding-top", "0");
                                    }
                                }
                            }

                            $(document).ready(function () {
                                <?php if($advancedCustom->disableNavBarInsideIframe){echo 'YPTHidenavbar();';} ?>
                                $('#buttonMenu').on("click.sidebar", function (event) {
                                    event.stopPropagation();
                                    //$('#sidebar').fadeToggle();
                                    if ($('body').hasClass('youtube')) {
                                        YPTSidebarClose();
                                    } else {
                                        YPTSidebarOpen();
                                    }

                                    $('#myNavbar').removeClass("in");
                                    $('#mysearch').removeClass("in");
                                });
                                /*
                                 $(document).on("click.sidebar", function () {
                                 YPTSidebarClose();
                                 });
                                 */
                                $("#sidebar").on("click", function (event) {
                                    event.stopPropagation();
                                });
                                $("#buttonSearch").click(function (event) {
                                    $('#myNavbar').removeClass("in");
                                    $("#sidebar").fadeOut();
                                });
                                $("#buttonMyNavbar").click(function (event) {
                                    $('#mysearch').removeClass("in");
                                    $("#sidebar").fadeOut();
                                });
                                var wasMobile = true;
                                $(window).resize(function () {
                                    if ($(window).width() > 767) {
                                        // Window is bigger than 767 pixels wide - show search again, if autohide by mobile.
                                        if (wasMobile) {
                                            wasMobile = false;
                                            $('#mysearch').addClass("in");
                                            $('#myNavbar').addClass("in");
                                        }
                                    }
                                    if ($(window).width() < 767) {
                                        // Window is smaller 767 pixels wide - show search again, if autohide by mobile.
                                        if (wasMobile == false) {
                                            wasMobile = true;
                                            $('#myNavbar').removeClass("in");
                                            $('#mysearch').removeClass("in");
                                        }
                                    }
                                });
                            });
                        </script>
                    </li>
                    <li style="width: 100%; text-align: center;">
                        <a class="navbar-brand" id="mainNavbarLogo" href="<?php echo empty($advancedCustom->logoMenuBarURL) ? $global['webSiteRootURL'] : $advancedCustom->logoMenuBarURL; ?>" >
                            <img src="<?php echo getCDN(), $config->getLogo(true); ?>" alt="<?php echo $config->getWebSiteTitle(); ?>" class="img-responsive ">
                        </a>
                    </li>
                    <?php
                    if (!empty($advancedCustomUser->keepViewerOnChannel) && !empty($_SESSION['channelName'])) {
                        $user = User::getChannelOwner($_SESSION['channelName']);
                        ?>
                        <li>
                            <a class="navbar-brand" href="<?php echo User::getChannelLinkFromChannelName($_SESSION['channelName']); ?>" >
                                <img src="<?php echo User::getPhoto($user['id']); ?>" alt="<?php echo User::getNameIdentificationById($user['id']); ?>" 
                                     class="img img-circle " style="height: 33px; width: 33px; margin-right: 15px;"> 
                            </a>
                        </li>
    <?php } ?>

                </ul>
            </li>
            <li class="nav-item" style="margin-right: 0px; ">

                <div class="navbar-header">
                    <button type="button" id="buttonSearch" class="visible-xs navbar-toggle btn btn-default navbar-btn" data-toggle="collapse" data-target="#mysearch" style="padding: 6px 12px;">
                        <span class="fa fa-search"></span>
                    </button>
                </div>
                <div class="input-group hidden-xs"  id="mysearch">
                    <form class="navbar-form form-inline input-group" role="search" id="searchForm"  action="<?php echo $global['webSiteRootURL']; ?>" style="padding: 0;">
                        <input class="form-control globalsearchfield" type="text" value="<?php
                        if (!empty($_GET['search'])) {
                            echo htmlentities($_GET['search']);
                        }
                        ?>" name="search" placeholder="<?php echo __("Search"); ?>" id="searchFormInput">
                        <span class="input-group-append">
                            <button class="btn btn-default btn-outline-secondary border-left-0 border  py-2" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </span>
                    </form>
                </div>
            </li>

            <li style="margin-right: 0px; padding-left: 0px;" id="navbarRegularButtons">
                <div class="hidden-xs" id="myNavbar">
                    <ul class="right-menus" style="padding-left: 0;">
                        <?php
                        if (!empty($advancedCustom->menuBarHTMLCode->value)) {
                            echo $advancedCustom->menuBarHTMLCode->value;
                        }
                        ?>

                        <?php
                        echo AVideoPlugin::getHTMLMenuRight();
                        ?>
                        <?php
                        if (User::canUpload() && empty($advancedCustom->doNotShowUploadButton)) {
                            ?>
                            <li>
                                <div class="btn-group" data-toggle="tooltip" title="<?php echo __("Submit your videos"); ?>" data-placement="left" >
                                    <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left"  data-toggle="dropdown">
                                        <i class="<?php echo isset($advancedCustom->uploadButtonDropdownIcon) ? $advancedCustom->uploadButtonDropdownIcon : "fas fa-video"; ?>"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
                                    </button>
                                    <?php
                                    if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && User::isVerified()) || (isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && !$advancedCustomUser->onlyVerifiedEmailCanUpload) || !isset($advancedCustomUser->onlyVerifiedEmailCanUpload)) {
                                        ?>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                            <?php
                                            include $global['systemRootPath'] . 'view/include/navbarEncoder.php';
                                            if (empty($advancedCustom->doNotShowUploadMP4Button)) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>mvideos?upload=1"  data-toggle="tooltip" title="<?php echo __("Upload files without encode"); ?>" data-placement="left"  >
                                                        <span class="fa fa-upload"></span> <?php echo empty($advancedCustom->uploadMP4ButtonLabel) ? __("Direct upload") : $advancedCustom->uploadMP4ButtonLabel; ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            if (empty($advancedCustom->doNotShowImportMP4Button)) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>view/import.php"  data-toggle="tooltip" title="<?php echo __("Search for videos in your local disk"); ?>" data-placement="left" >
                                                        <span class="fas fa-hdd"></span> <?php echo empty($advancedCustom->importMP4ButtonLabel) ? __("Direct Import Local Videos") : $advancedCustom->importMP4ButtonLabel; ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            if (empty($advancedCustom->doNotShowEmbedButton)) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>mvideos?link=1"  data-toggle="tooltip" title="<?php echo __("Embed videos/files in your site"); ?>" data-placement="left" >
                                                        <span class="fa fa-link"></span> <?php echo empty($advancedCustom->embedButtonLabel) ? __("Embed a video link") : $advancedCustom->embedButtonLabel; ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            if (AVideoPlugin::isEnabledByName("Articles")) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>mvideos?article=1"  data-toggle="tooltip" title="<?php echo __("Write an article"); ?>" data-placement="left" >
                                                        <i class="far fa-newspaper"></i> <?php echo __("Add Article"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            echo AVideoPlugin::getUploadMenuButton();
                                            ?>
                                        </ul>     
                                        <?php
                                    } else {
                                        ?>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                            <li>
                                                <a  href="" >
                                                    <span class="fa fa-exclamation"></span> <?php echo __("Only verified users can upload"); ?>
                                                </a>
                                            </li>
                                            <?php
                                            echo AVideoPlugin::getUploadMenuButton();
                                            ?>
                                        </ul>

                                        <?php
                                    }
                                    ?>
                                </div>

                            </li>
                            <?php
                        } else {
                            $output = ob_get_clean();
                            echo AVideoPlugin::getUploadMenuButton();
                            $getUploadMenuButton = ob_get_clean();
                            if (!empty($getUploadMenuButton)) {
                                ?>
                                <li>
                                    <div class="btn-group" data-toggle="tooltip" title="<?php echo __("Submit your videos"); ?>" data-placement="left" >
                                        <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left"  data-toggle="dropdown">
                                            <i class="<?php echo isset($advancedCustom->uploadButtonDropdownIcon) ? $advancedCustom->uploadButtonDropdownIcon : "fas fa-video"; ?>"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                            <?php
                                            echo $getUploadMenuButton;
                                            ?>
                                        </ul>
                                    </div>
                                </li>
                                <?php
                            }
                            ob_start();
                            echo $output;
                        }
                        ?>
                        <li>
                            <div class="navbar-lang-btn">
                            <?php
                            if ($lang == 'en') {
                                $lang = 'en_US';
                            }
                            echo Layout::getLangsSelect('navBarFlag', $lang, 'navBarFlag', '', true);
                            ?>
                                
                            </div>
                            <script>
                                $(function () {
                                    $("#navBarFlag").change(function () {
                                        var selfURI = "<?php echo getSelfURI(); ?>";
                                        window.location.href = addGetParam(selfURI, 'lang', $(this).val());
                                    });
                                });
                            </script>
                        </li>
                        <?php
                        if (!empty($advancedCustomUser->signInOnRight)) {
                            if (User::isLogged()) {
                                if (!$advancedCustomUser->disableSignOutButton) {
                                    ?>
                                    <li>
                                        <a class="btn navbar-btn btn-default"  href="<?php echo $global['webSiteRootURL']; ?>logoff">
                                            <?php
                                            if (!empty($_COOKIE['user']) && !empty($_COOKIE['pass'])) {
                                                ?>
                                                <i class="fas fa-lock text-muted" style="opacity: 0.2;"></i>    
                                                <?php
                                            } else {
                                                ?>
                                                <i class="fas fa-lock-open text-muted" style="opacity: 0.2;"></i>    
                                                <?php
                                            }
                                            ?>
                                            <i class="fas fa-sign-out-alt"></i> <span class="hidden-md hidden-sm"><?php echo __("Sign Out"); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>
                                    <a class="btn navbar-btn btn-default" href="<?php echo $global['webSiteRootURL']; ?>user" >
                                        <i class="fas fa-sign-in-alt"></i> <?php echo __("Sign In"); ?>
                                    </a>
                                </li>
                                <?php
                            }
                        }
                        ?>


                    </ul>
                </div>

            </li>

            <li style="margin-right: 0px;">

                <div class="navbar-header pull-right">
                    <ul style="margin: 0; padding: 0;">
                        <?php
                        if (empty($advancedCustomUser->doNotShowRightProfile)) {
                            $tooltip = "";
                            if (User::isLogged()) {
                                $tooltip = 'data-toggle="tooltip" data-html="true" title="' . User::getName() . ":: " . User::getMail() . '" data-placement="left"';
                            } else {
                                $tooltip = 'data-toggle="tooltip" data-html="true" title="' . __("Login") . '" data-placement="left"';
                            }
                            ?>
                            <li class="rightProfile" <?php echo $tooltip; ?> >
                                <div class="btn-group" >

                                    <?php
                                    if (User::isLogged()) {
                                        ?>
                                        <button type="button" class="btn btn-default dropdown-toggle navbar-btn pull-left btn-circle"  data-toggle="dropdown" id="rightProfileButton" style="padding:0;">
                                            <img src="<?php echo User::getPhoto(); ?>" 
                                                 style="width: 32px; height: 32px; max-width: 32px;"  
                                                 class="img img-responsive img-circle" alt="User Photo"
                                                 />
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                            <li>
                                                <div class="pull-left" style="margin-left: 10px;">
                                                    <img src="<?php echo User::getPhoto(); ?>" style="max-width: 50px;"  class="img img-responsive img-circle" alt="User Photo"/>
                                                </div>
                                                <div  class="pull-left" >
                                                    <h2><?php echo User::getName(); ?></h2>
                                                    <div><small><?php echo User::getMail(); ?></small></div>

                                                </div>
                                            </li>
                                            <li>
                                                <hr>
                                            </li>
                                            <?php
                                            if (!$advancedCustomUser->disableSignOutButton) {
                                                ?>
                                                <li>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>logoff" >
                                                        <?php
                                                        if (!empty($_COOKIE['user']) && !empty($_COOKIE['pass'])) {
                                                            ?>
                                                            <i class="fas fa-lock text-muted" style="opacity: 0.2;"></i>    
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <i class="fas fa-lock-open text-muted" style="opacity: 0.2;"></i>    
                                                            <?php
                                                        }
                                                        ?>
                                                        <i class="fas fa-sign-out-alt"></i> <?php echo __("Sign out"); ?>
                                                    </a>
                                                </li> 
                                                <?php
                                            }
                                            ?>

                                            <li>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>user" style="border-radius: 4px 4px 0 0;">
                                                    <span class="fa fa-user-circle"></span>
            <?php echo __("My Account"); ?>
                                                </a>
                                            </li>

                                            <?php
                                            if (User::canUpload(true)) {
                                                ?>
                                                <li>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                                                        <span class="glyphicon glyphicon-film"></span>
                                                        <span class="glyphicon glyphicon-headphones"></span>
                                                <?php echo __("My videos"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                            <li>
                                                <a href="<?php echo User::getChannelLink(); ?>" >
                                                    <span class="fas fa-play-circle"></span>
                                            <?php echo __($advancedCustomUser->MyChannelLabel); ?>
                                                </a>
                                            </li>    
                                            <?php
                                            print AVideoPlugin::navBarProfileButtons();

                                            if ((($config->getAuthCanViewChart() == 0) && (User::canUpload())) || (($config->getAuthCanViewChart() == 1) && (User::canViewChart()))) {
                                                ?>
                                                <li>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>charts">
                                                        <span class="fas fa-tachometer-alt"></span>
                                                <?php echo __("Dashboard"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            } if (User::canUpload()) {
                                                ?>
                                                <li>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>subscribes">
                                                        <span class="fa fa-check"></span>
                                                <?php echo __("My Subscribers"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                                if (Category::canCreateCategory()) {
                                                    ?>

                                                    <li>
                                                        <a href="<?php echo $global['webSiteRootURL']; ?>categories">
                                                            <span class="glyphicon glyphicon-list"></span>
                    <?php echo __($advancedCustom->CategoryLabel); ?>
                                                        </a>

                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                                <li>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>comments">
                                                        <span class="fa fa-comment"></span>
                                                <?php echo __("Comments"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            ?>

                                        </ul>
                                        <?php
                                    } else {
                                        ?>
                                        <a class="btn btn-default navbar-btn " href="<?php echo $global['webSiteRootURL']; ?>user"   id="rightLoginButton" style="min-height:34px; padding: 6px 12px; border-width: 1px;">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </a>
                                        <?php
                                    }
                                    ?>
                                </div>

                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="pull-right">
                    <button type="button" id="buttonMyNavbar" class=" navbar-toggle btn btn-default navbar-btn" data-toggle="collapse" data-target="#myNavbar" style="padding: 6px 12px;">
                        <span class="fa fa-bars"></span>
                    </button>
                </div>
            </li>
        </ul>


        <div id="sidebar" class="list-group-item" style="<?php echo $sidebarStyle; ?>">
            <div id="sideBarContainer">
                <ul class="nav navbar">

                    <?php
                    if (empty($advancedCustom->doNotShowLeftHomeButton)) {
                        ?>
                        <li>

                            <div>
                                <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-primary btn-block  " style="border-radius: 4px 4px 0 0;">
                                    <span class="fa fa-home"></span>
        <?php echo __("Home"); ?>
                                </a>

                            </div>
                        </li>
                        <?php
                    }

                    if (AVideoPlugin::isEnabledByName("PlayLists") && PlayLists::showTVFeatures()) {
                        ?>
                        <li>
                            <div>
                                <a href="<?php echo $global['webSiteRootURL']; ?>epg" class="btn btn-primary btn-block " style="border-radius:  0 0 0 0;">
                                    <i class="fas fa-stream"></i>
        <?php echo __("EPG"); ?>
                                </a>

                            </div>
                        </li>
                        <li>
                            <div>
                                <a href="<?php echo $global['webSiteRootURL']; ?>tv" class="btn btn-primary btn-block " style="border-radius:  0 0 0 0;">
                                    <i class="fas fa-tv"></i>
        <?php echo __("TV"); ?>
                                </a>

                            </div>
                        </li>
                        <?php
                    }
                    if (empty($advancedCustom->doNotShowLeftTrendingButton)) {
                        ?>
                        <li>

                            <div>
                                <a href="<?php echo $global['webSiteRootURL']; ?>trending" class="btn btn-primary btn-block " style="border-radius:  0 0 4px 4px;">
                                    <i class="fas fa-fire"></i>
        <?php echo __("Trending"); ?>
                                </a>

                            </div>
                        </li>
                        <?php
                    }
                    if (empty($advancedCustomUser->doNotShowLeftProfile)) {
                        if (User::isLogged()) {
                            ?>
                            <li>
                                <hr>
                            </li>
                            <li>
                                <h2 class="text-danger"><?php echo __("My Menu"); ?></h2>

                                <?php
                                if (!$advancedCustomUser->disableSignOutButton) {
                                    ?>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>logoff" class="btn btn-default btn-block" >
                                            <?php
                                            if (!empty($_COOKIE['user']) && !empty($_COOKIE['pass'])) {
                                                ?>
                                                <i class="fas fa-lock text-muted" style="opacity: 0.2;"></i>    
                                                <?php
                                            } else {
                                                ?>
                                                <i class="fas fa-lock-open text-muted" style="opacity: 0.2;"></i>    
                                                <?php
                                            }
                                            ?>
                                            <i class="fas fa-sign-out-alt"></i> <?php echo __("Sign out"); ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>

                            </li>
                            <li style="min-height: 60px;">
                                <div class="pull-left" style="margin-left: 10px;">
                                    <img src="<?php echo User::getPhoto(); ?>" style="max-width: 55px;"  class="img img-thumbnail img-responsive img-circle"/>
                                </div>
                                <div  style="margin-left: 80px;">
                                    <h2><?php echo User::getName(); ?></h2>
                                    <div><small><?php echo User::getMail(); ?></small></div>

                                </div>
                            </li>
                            <li>

                                <div>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-block" style="border-radius: 4px 4px 0 0;">
                                        <span class="fa fa-user-circle"></span>
            <?php echo __("My Account"); ?>
                                    </a>

                                </div>
                            </li>

                            <?php
                            if (User::canUpload()) {
                                ?>
                                <li>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success btn-block" style="border-radius: 0;">
                                            <span class="glyphicon glyphicon-film"></span>
                                            <span class="glyphicon glyphicon-headphones"></span>
                <?php echo __("My videos"); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                            <li>

                                <div>
                                    <a href="<?php echo User::getChannelLink(); ?>" class="btn btn-danger btn-block" style="border-radius: 0;">
                                        <span class="fas fa-play-circle"></span>
            <?php echo __($advancedCustomUser->MyChannelLabel); ?>
                                    </a>

                                </div>
                            </li>    
                            <?php
                            print AVideoPlugin::navBarButtons();

                            if ((($config->getAuthCanViewChart() == 0) && (User::canUpload())) || (($config->getAuthCanViewChart() == 1) && (User::canViewChart()))) {
                                ?>
                                <li>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>charts" class="btn btn-default btn-block" style="border-radius: 0;">
                                            <span class="fas fa-tachometer-alt"></span>
                <?php echo __("Dashboard"); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php
                            } if (User::canUpload()) {
                                ?>
                                <li>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>subscribes" class="btn btn-default btn-block" style="border-radius: 0">
                                            <span class="fa fa-check"></span>
                <?php echo __("My Subscribers"); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php
                                if (Category::canCreateCategory()) {
                                    ?>

                                    <li>
                                        <div>
                                            <a href="<?php echo $global['webSiteRootURL']; ?>categories" class="btn btn-default btn-block" style="border-radius: 0;">
                                                <span class="glyphicon glyphicon-list"></span>
                    <?php echo __($advancedCustom->CategoryLabel); ?>
                                            </a>
                                        </div>
                                    </li>
                                    <?php
                                }
                                ?>
                                <li>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>comments" class="btn btn-default btn-block" style="border-radius: 0 0 4px 4px;">
                                            <span class="fa fa-comment"></span>
                <?php echo __("Comments"); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                            <?php
                        } else {
                            ?>
                            <li>
                                <hr>
                            </li>
                            <li>
                                <div>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-success btn-block">
                                        <i class="fas fa-sign-in-alt"></i>
            <?php echo __("Sign In"); ?>
                                    </a>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    if (User::isAdmin()) {
                        ?>
                        <li>
                            <hr>
                        </li>
                        <li>
                            <h2 class="text-danger"><?php echo __("Admin Menu"); ?></h2>
                            <ul  class="nav navbar" style="margin-bottom: 10px;">
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>admin/">
                                        <i class="fas fa-star"></i>
        <?php echo __("Admin Panel"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>users">
                                        <span class="glyphicon glyphicon-user"></span>
        <?php echo __("Users"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups">
                                        <span class="fa fa-users"></span>
        <?php echo __("Users Groups"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>categories">
                                        <span class="glyphicon glyphicon-list"></span>
        <?php echo __($advancedCustom->CategoryLabel); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>update">
                                        <span class="glyphicon glyphicon-refresh"></span>
                                        <?php echo __("Update version"); ?>
                                        <?php
                                        if (!empty($updateFiles)) {
                                            ?><span class="label label-danger"><?php echo count($updateFiles); ?></span><?php
                            }
                            ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>siteConfigurations">
                                        <span class="glyphicon glyphicon-cog"></span>
        <?php echo __("Site Configurations"); ?>
                                    </a>
                                </li>
                                <!--
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>locale">
                                        <span class="glyphicon glyphicon-flag"></span>
        <?php echo __("Create more translations"); ?>
                                    </a>
                                </li>
                                -->
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>plugins">
                                        <i class="fas fa-puzzle-piece"></i>
        <?php echo __("Plugins"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearCacheFirstPageButton">
                                        <i class="fa fa-trash"></i> <?php echo __("Clear First Page Cache"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearCacheButton">
                                        <i class="fa fa-trash"></i> <?php echo __("Clear Cache Directory"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>i/log" class="">
                                        <i class="fas fa-clipboard-list"></i> <?php echo __("Log file"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="generateSiteMapButton">
                                        <i class="fa fa-sitemap"></i> <?php echo __("Generate Sitemap"); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php
                    } else {
                        $menus = array();
                        if (Permissions::canAdminUsers()) {
                            $menus[] = '
                                ?>
                                <li>
                                    <a href="<?php echo $global[\'webSiteRootURL\']; ?>users">
                                        <span class="glyphicon glyphicon-user"></span>
                                        <?php echo __("Users"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                        }
                        if (Permissions::canAdminUserGroups()) {
                            $menus[] = '?>
                                <li>
                                    <a href="<?php echo $global[\'webSiteRootURL\']; ?>usersGroups">
                                        <span class="fa fa-users"></span>
                                        <?php echo __("Users Groups"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                        }
                        if (Permissions::canClearCache()) {
                            $menus[] = '?>
                                <li>
                                    <a href="#" class="clearCacheFirstPageButton">
                                        <i class="fa fa-trash"></i> <?php echo __("Clear First Page Cache"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearCacheButton">
                                        <i class="fa fa-trash"></i> <?php echo __("Clear Cache Directory"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                        }
                        if (Permissions::canSeeLogs()) {
                            $menus[] = ' ?>
                                <li>
                                    <a href="<?php echo $global[\'webSiteRootURL\']; ?>i/log" class="">
                                        <i class="fas fa-clipboard-list"></i> <?php echo __("Log file"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                        }
                        if (Permissions::canGenerateSiteMap()) {
                            $menus[] = '?>
                                <li>
                                    <a href="#" class="generateSiteMapButton">
                                        <i class="fa fa-sitemap"></i> <?php echo __("Generate Sitemap"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                        }
                        if (count($menus)) {
                            ?>
                            <hr>
                            <h2 class="text-danger"><?php echo __("Extra Permissions"); ?></h2>
                            <ul  class="nav navbar" style="margin-bottom: 10px;">
                                <?php
                                eval(implode(" ", $menus));
                                ?>
                            </ul>
                            <?php
                        }
                    }
                    ?>


                    <?php
                    echo AVideoPlugin::getHTMLMenuLeft();
                    ?>

                    <?php
                    if (empty($advancedCustom->doNotShowLeftMenuAudioAndVideoButtons)) {
                        ?>
                        <li>
                            <hr>
                        </li>
                        <li class="nav-item <?php echo empty($_SESSION['type']) ? "active" : ""; ?>">
                            <a class="nav-link " href="<?php echo $global['webSiteRootURL']; ?>?type=all">
                                <span class="glyphicon glyphicon-star"></span>
        <?php echo __("Audios and Videos"); ?>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (!empty($_SESSION['type']) && $_SESSION['type'] == 'video' && empty($_GET['catName'])) ? "active" : ""; ?>">
                            <a class="nav-link " href="<?php echo $global['webSiteRootURL']; ?>videoOnly">
                                <span class="glyphicon glyphicon-facetime-video"></span>
        <?php echo __("Videos"); ?>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (!empty($_SESSION['type']) && $_SESSION['type'] == 'audio' && empty($_GET['catName'])) ? "active" : ""; ?>">
                            <a class="nav-link" href="<?php echo $global['webSiteRootURL']; ?>audioOnly">
                                <span class="glyphicon glyphicon-headphones"></span>
                        <?php echo __("Audios"); ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>

                    <?php
                    if (empty($advancedCustom->removeBrowserChannelLinkFromMenu)) {
                        ?>
                        <!-- Channels -->
                        <li>
                            <hr>
                        </li>
                        <li>
                            <h3 class="text-danger"><?php echo __("Channels"); ?></h3>
                        </li>
                        <li>
                            <a href="<?php echo $global['webSiteRootURL']; ?>channels">
                                <i class="fa fa-search"></i>
        <?php echo __("Browse Channels"); ?>
                            </a>
                        </li>

                        <?php
                    }
                    ?>
                    <li>
                        <hr>
                    </li>
                    <!-- categories -->
                    <li>
                        <h3>
                            <a href="<?php echo $global['webSiteRootURL']; ?>listCategories" class="text-danger">
    <?php echo __($advancedCustom->CategoryLabel); ?>
                            </a>
                        </h3>
                    </li>
                    <?php
                    $_rowCount = getRowCount();
                    $_REQUEST['rowCount'] = 1000;
                    $parsed_cats = array();
                    if (!function_exists('mkSub')) {

                        function mkSub($catId) {
                            global $global, $parsed_cats;
                            unset($_GET['parentsOnly']);
                            $subcats = Category::getChildCategories($catId);
                            if (!empty($subcats)) {
                                echo "<ul class=\"nav\" style='margin-bottom: 0px; list-style-type: none;'>";
                                foreach ($subcats as $subcat) {
                                    if ($subcat['parentId'] != $catId) {
                                        continue;
                                    }
                                    if (empty($subcat['total'])) {
                                        continue;
                                    }
                                    if (is_array($parsed_cats) && in_array($subcat['id'], $parsed_cats)) {
                                        continue;
                                    }
                                    //$parsed_cats[] = $subcat['id'];
                                    echo '<li class="navsub-toggle ' . ($subcat['clean_name'] == @$_GET['catName'] ? "active" : "") . '">'
                                    . '<a href="' . $global['webSiteRootURL'] . 'cat/' . $subcat['clean_name'] . '" >'
                                    . '<span class="' . (empty($subcat['iconClass']) ? "fa fa-folder" : $subcat['iconClass']) . '"></span>  ' . $subcat['name'] . ' <span class="badge">' . $subcat['total'] . '</span>';
                                    echo '</a>';
                                    mkSub($subcat['id']);
                                    echo '</li>';
                                }
                                echo "</ul>";
                            }
                        }

                    }
                    if (empty($advancedCustom->doNotDisplayCategoryLeftMenu)) {
                        $post = $_POST;
                        $get = $_GET;
                        unset($_GET);
                        unset($_POST);
                        $_GET['current'] = $_POST['current'] = 1;
                        $_GET['parentsOnly'] = 1;
                        $categories = Category::getAllCategories();
                        foreach ($categories as $value) {
                            if ($value['parentId']) {
                                continue;
                            }
                            if ($advancedCustom->ShowAllVideosOnCategory) {
                                $total = $value['fullTotal'];
                            } else {
                                $total = $value['total'];
                            }
                            if (empty($total)) {
                                continue;
                            }
                            if (in_array($value['id'], $parsed_cats)) {
                                continue;
                            }
                            //$parsed_cats[] = $value['id'];
                            echo '<li class="navsub-toggle ' . ($value['clean_name'] == @$_GET['catName'] ? "active" : "") . '">'
                            . '<a href="' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '" >';
                            echo '<span class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></span>  ' . $value['name'];
                            if (empty($advancedCustom->hideCategoryVideosCount)) {
                                echo ' <span class="badge">' . $total . '</span>';
                            }
                            echo '</a>';
                            mkSub($value['id']);
                            echo '</li>';
                        }
                        $_POST = $post;
                        $_GET = $get;
                    }

                    $_REQUEST['rowCount'] = $_rowCount;
                    ?>

                    <!-- categories END -->

                    <li>
                        <hr>
                    </li>
                    <?php
                    if (empty($advancedCustom->disablePlayLink)) {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $global['webSiteRootURL']; ?>playLink">
                                <i class="fas fa-play-circle"></i>
                        <?php echo __("Play a Link"); ?>
                            </a>
                        </li>    
                        <?php
                    }
                    if (empty($advancedCustom->disableHelpLeftMenu)) {
                        ?>
                        <li>
                            <a href="<?php echo $global['webSiteRootURL']; ?>help">
                                <span class="glyphicon glyphicon-question-sign"></span>
                        <?php echo __("Help"); ?>
                            </a>
                        </li>
                        <?php
                    }

                    if (empty($advancedCustom->disableAboutLeftMenu)) {
                        ?>
                        <li>
                            <a href="<?php echo $global['webSiteRootURL']; ?>about">
                                <span class="glyphicon glyphicon-info-sign"></span>
                        <?php echo __("About"); ?>
                            </a>
                        </li>
                        <?php
                    }

                    if (empty($advancedCustom->disableContactLeftMenu)) {
                        ?>
                        <li>
                            <a href="<?php echo $global['webSiteRootURL']; ?>contact">
                                <span class="glyphicon glyphicon-comment"></span>
                        <?php echo __("Contact"); ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
    <script>

        var seachFormIsRunning = 0;
        $(document).ready(function () {
            setTimeout(function () {
                $('.nav li.navsub-toggle a:not(.selected) + ul').hide();
                var navsub_toggle_selected = $('.nav li.navsub-toggle a.selected');
                navsub_toggle_selected.next().show();
                navsub_toggle_selected = navsub_toggle_selected.parent();

                var navsub_toggle_selected_stop = 24;
                while (navsub_toggle_selected.length) {
                    if ($.inArray(navsub_toggle_selected.prop('localName'), ['li', 'ul']) == -1)
                        break;
                    if (navsub_toggle_selected.prop('localName') == 'ul') {
                        navsub_toggle_selected.show().prev().addClass('selected');
                    }
                    navsub_toggle_selected = navsub_toggle_selected.parent();

                    navsub_toggle_selected_stop--;
                    if (navsub_toggle_selected_stop < 0)
                        break;
                }
            }, 500);


            $('.nav').on('click', 'li.navsub-toggle a:not(.selected)', function (e) {
                var a = $(this),
                        b = a.next();
                if (b.length) {
                    e.preventDefault();

                    a.addClass('selected');
                    b.slideDown();

                    var c = a.closest('.nav').find('li.navsub-toggle a.selected').not(a).removeClass('selected').next();

                    if (c.length)
                        c.slideUp();
                }
            });

            $('#searchForm').submit(function (event) {
                if (seachFormIsRunning) {
                    event.preventDefault();
                    return false;
                }
                seachFormIsRunning = 1;
                var str = $('#searchFormInput').val();
                if (isMediaSiteURL(str)) {
                    event.preventDefault();
                    console.log("searchForm is URL " + str);
                    seachFormPlayURL(str);
                    return false;
                } else {
                    console.log("searchForm submit " + str);
                    document.location = webSiteRootURL + "?search=" + str;
                }
            });

        });

        function seachFormPlayURL(url) {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'view/url2Embed.json.php',
                method: 'POST',
                data: {
                    'url': url
                },
                success: function (response) {
                    seachFormIsRunning = 0;
                    if (response.error) {
                        modal.hidePleaseWait();
                        avideoToast(response.msg);
                    } else {
                        if (typeof linksToEmbed === 'function') {
                            document.location = response.playEmbedLink;
                        } else
                        if (typeof flixFullScreen == 'function') {
                            flixFullScreen(response.playEmbedLink, response.playLink);
                            modal.hidePleaseWait();
                        } else {
                            document.location = response.playLink;
                        }
                    }
                }
            });
        }
    </script>
    <?php
    if (!empty($advancedCustom->underMenuBarHTMLCode->value)) {
        echo $advancedCustom->underMenuBarHTMLCode->value;
    }
} else if ($thisScriptFile["basename"] !== 'user.php' && empty($advancedCustom->disableNavbar)) {
    
}
unset($_GET['parentsOnly']);
?>
