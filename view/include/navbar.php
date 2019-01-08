<?php
global $includeDefaultNavBar;
if(empty($sidebarStyle)){
    $sidebarStyle = "display: none;";
}
$includeDefaultNavBar = true;
YouPHPTubePlugin::navBar();
if (!$includeDefaultNavBar) {
    return false;
}
?>
<style>
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
        .globalsearchfield {
            width: 80% !important;
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
            padding: 5px;
        }
        .searchul{
            padding-left: 0px;
        }
    }
</style>
<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$_GET['parentsOnly'] = "1";
$categories = Category::getAllCategories();
if (empty($_SESSION['language'])) {
    $lang = 'us';
} else {
    $lang = $_SESSION['language'];
}

$json_file = url_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
$advancedCustom = json_decode($json_file);
$thisScriptFile = pathinfo($_SERVER["SCRIPT_FILENAME"]);
if (((empty($advancedCustomUser->userMustBeLoggedIn) && empty($advancedCustom->disableNavbar)) || $thisScriptFile["basename"] === "signUp.php") || User::isLogged()) {
    $updateFiles = getUpdatesFilesArray();
    ?>
    <nav class="navbar navbar-default navbar-fixed-top ">
        <ul class="items-container">
            <li>
                <ul class="left-side">
                    <li>
                        <button class="btn btn-default navbar-btn pull-left" id="buttonMenu" ><span class="fa fa-bars"></span></button>
                        <script>
                            $(document).ready(function () {
                                $('#buttonMenu').on("click.sidebar", function (event) {
                                    event.stopPropagation();
                                    //$('#sidebar').fadeToggle();
                                    if ($('body').hasClass('youtube')) {
                                        $('body').removeClass('youtube')
                                        $("#sidebar").fadeOut();
                                    } else {
                                        $('body').addClass('youtube')
                                        $("#sidebar").fadeIn();
                                    }

                                    $('#myNavbar').removeClass("in");
                                    $('#mysearch').removeClass("in");
                                });

                                $(document).on("click.sidebar", function () {
                                    $("#sidebar").fadeOut();
                                });
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
                    <li>
                        <a class="navbar-brand" href="<?php echo $global['webSiteRootURL']; ?>" >
                            <img src="<?php echo $global['webSiteRootURL'], $config->getLogo(); ?>" alt="<?php echo $config->getWebSiteTitle(); ?>" class="img-responsive ">
                        </a>
                    </li>

                </ul>
            </li>
            <li class="nav-item" style="margin-right: 0px; ">

                <div class="navbar-header">
                    <button type="button" id="buttonSearch" class="visible-xs navbar-toggle btn btn-default navbar-btn" data-toggle="collapse" data-target="#mysearch" style="padding: 6px 12px;">
                        <span class="fa fa-search"></span>
                    </button>
                </div>
                <div class="input-group hidden-xs"  id="mysearch">
                    <form class="navbar-form form-inline input-group" role="search" id="searchForm"  action="<?php echo $global['webSiteRootURL']; ?>">
                        <input class="form-control globalsearchfield" type="text" value="<?php
                        if (!empty($_GET['search'])) {
                            echo htmlentities($_GET['search']);
                        }
                        ?>" name="search" placeholder="<?php echo __("Search"); ?>">
                        <span class="input-group-append">
                            <button class="btn btn-default btn-outline-secondary border-left-0 border  py-2" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </span>
                    </form>
                </div>
            </li>

            <li style="margin-right: 0px; padding-left: 0px;">
                <div class="navbar-header">
                    <button type="button" id="buttonMyNavbar" class=" navbar-toggle btn btn-default navbar-btn" data-toggle="collapse" data-target="#myNavbar" style="padding: 6px 12px;">
                        <span class="fa fa-bars"></span>
                    </button>
                </div>
                <div class="hidden-xs col-md-3 col-sm-4" id="myNavbar">
                    <ul class="right-menus" style="padding-left: 0;">
                        <?php
                        if (!empty($advancedCustom->menuBarHTMLCode->value)) {
                            echo $advancedCustom->menuBarHTMLCode->value;
                        }
                        ?>

                        <?php
                        echo YouPHPTubePlugin::getHTMLMenuRight();
                        ?>
                        <?php
                        if (User::canUpload() && empty($advancedCustom->doNotShowUploadButton)) {
                            ?>
                            <li>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left"  data-toggle="dropdown">
                                        <i class="<?php echo isset($advancedCustom->uploadButtonDropdownIcon) ? $advancedCustom->uploadButtonDropdownIcon : "fas fa-video"; ?>"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
                                    </button>
                                    <?php
                                    if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && User::isVerified()) || (isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && !$advancedCustomUser->onlyVerifiedEmailCanUpload) || !isset($advancedCustomUser->onlyVerifiedEmailCanUpload)
                                    ) {
                                        ?>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                            <?php
                                            if (!empty($advancedCustom->encoderNetwork) && empty($advancedCustom->doNotShowEncoderNetwork)) {
                                                ?>
                                                <li>
                                                    <form id="formEncoderN" method="post" action="<?php echo $advancedCustom->encoderNetwork; ?>" target="encoder">
                                                        <input type="hidden" name="webSiteRootURL" value="<?php echo $global['webSiteRootURL']; ?>" />
                                                        <input type="hidden" name="user" value="<?php echo User::getUserName(); ?>" />
                                                        <input type="hidden" name="pass" value="<?php echo User::getUserPass(); ?>" />
                                                    </form>
                                                    <a href="#" onclick="$('#formEncoderN').submit();
                                                                            return false;">
                                                        <span class="fa fa-cogs"></span> <?php echo __("Encoder Network"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            if (empty($advancedCustom->doNotShowEncoderButton)) {
                                                if (!empty($config->getEncoderURL())) {
                                                    ?>
                                                    <li>
                                                        <form id="formEncoder" method="post" action="<?php echo $config->getEncoderURL(); ?>" target="encoder">
                                                            <input type="hidden" name="webSiteRootURL" value="<?php echo $global['webSiteRootURL']; ?>" />
                                                            <input type="hidden" name="user" value="<?php echo User::getUserName(); ?>" />
                                                            <input type="hidden" name="pass" value="<?php echo User::getUserPass(); ?>" />
                                                        </form>
                                                        <a href="#" onclick="$('#formEncoder').submit();return false;">
                                                            <span class="fa fa-cog"></span> <?php echo __("Encode video and audio"); ?>
                                                        </a>
                                                    </li>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <li>
                                                        <a href="<?php echo $global['webSiteRootURL']; ?>siteConfigurations" ><span class="fa fa-cogs"></span> <?php echo __("Configure an Encoder URL"); ?></a>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            if (empty($advancedCustom->doNotShowUploadMP4Button)) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>mvideos?upload=1" >
                                                        <span class="fa fa-upload"></span> <?php echo __("Direct upload"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            if (empty($advancedCustom->doNotShowImportMP4Button)) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>view/import.php" >
                                                        <span class="fas fa-hdd"></span> <?php echo __("Direct Import Local Videos"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            if (empty($advancedCustom->doNotShowEmbedButton)) {
                                                ?>
                                                <li>
                                                    <a  href="<?php echo $global['webSiteRootURL']; ?>mvideos?link=1" >
                                                        <span class="fa fa-link"></span> <?php echo __("Embed a video link"); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
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
                                        </ul>

                                        <?php
                                    }
                                    ?>
                                </div>

                            </li>
                            <?php
                        }
                        ?>
                        <li>
                            <?php
                            $flags = getEnabledLangs();
                            $objFlag = new stdClass();
                            foreach ($flags as $key => $value) {
                                //$value = strtoupper($value);
                                $objFlag->$value = $value;
                            }
                            if ($lang == 'en') {
                                $lang = 'us';
                            }
                            ?>
                            <style>
                                #navBarFlag .dropdown-menu {
                                    min-width: 20px;
                                }
                            </style>
                            <div id="navBarFlag" data-input-name="country" data-selected-country="<?php echo $lang; ?>"></div>
                            <script>
                                $(function () {
                                    $("#navBarFlag").flagStrap({
                                        countries: <?php echo json_encode($objFlag); ?>,
                                        inputName: 'country',
                                        buttonType: "btn-default navbar-btn",
                                        onSelect: function (value, element) {
                                            if (!value && element[1]) {
                                                value = $(element[1]).val();
                                            }
                                            window.location.href = "<?php echo $global['webSiteRootURL']; ?>?lang=" + value;
                                        },
                                        placeholder: {
                                            value: "",
                                            text: ""
                                        }
                                    });
                                });
                            </script>
                        </li>
                        <?php
                        if (!empty($advancedCustom->signInOnRight)) {
                            if (User::isLogged()) {
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
        </ul>


        <div id="sidebar" class="list-group-item" style="<?php echo $sidebarStyle; ?>">
            <div id="sideBarContainer">
                <ul class="nav navbar">
                    <?php
                    if (User::isLogged()) {
                        ?>
                        <li>
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

                        <li>

                            <div>
                                <a href="<?php echo User::getChannelLink(); ?>" class="btn btn-danger btn-block" style="border-radius: 0;">
                                    <span class="fab fa-youtube"></span>
                                    <?php echo __("My Channel"); ?>
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

                        print YouPHPTubePlugin::navBarButtons();

                        if ((($config->getAuthCanViewChart() == 0) && (User::canUpload())) || (($config->getAuthCanViewChart() == 1) && (User::canViewChart()))) {
                            ?>
                            <li>
                                <div>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>charts" class="btn btn-info btn-block" style="border-radius: 0;">
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
                                    <a href="<?php echo $global['webSiteRootURL']; ?>subscribes" class="btn btn-warning btn-block" style="border-radius: 0">
                                        <span class="fa fa-check"></span>
                                        <?php echo __("Subscriptions"); ?>
                                    </a>
                                </div>
                            </li>
                            <?php
                            if (Category::canCreateCategory()) {
                                ?>

                                <li>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>categories" class="btn btn-info btn-block" style="border-radius: 0;">
                                            <span class="glyphicon glyphicon-list"></span>
                                            <?php echo __("Categories"); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php
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
                        }
                        if (User::isAdmin()) {
                            ?>

                            <li>
                                <hr>
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
                                            <?php echo __("Categories"); ?>
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
                                </ul>
                            </li>

                            <?php
                        }
                        ?>
                        <?php
                    } else {
                        ?>
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
                    <!-- categories -->
                    <li>
                        <hr>
                        <h3 class="text-danger"><?php echo __("Categories"); ?></h3>
                    </li>
                    <?php
                    if (!function_exists('mkSub')) {

                        function mkSub($catId) {
                            global $global;
                            unset($_GET['parentsOnly']);
                            $subcats = Category::getChildCategories($catId);
                            if (!empty($subcats)) {
                                echo "<ul style='margin-bottom: 0px; list-style-type: none;'>";
                                foreach ($subcats as $subcat) {
                                    if (empty($subcat['total'])) {
                                        continue;
                                    }
                                    echo '<li class="' . ($subcat['clean_name'] == @$_GET['catName'] ? "active" : "") . '">'
                                    . '<a href="' . $global['webSiteRootURL'] . 'cat/' . $subcat['clean_name'] . '" >'
                                    . '<span class="' . (empty($subcat['iconClass']) ? "fa fa-folder" : $subcat['iconClass']) . '"></span>  ' . $subcat['name'] . ' <span class="badge">' . $subcat['total'] . '</span></a></li>';
                                    mkSub($subcat['id']);
                                }
                                echo "</ul>";
                            }
                        }

                    }
                    //var_dump($categories);exit;
                    foreach ($categories as $value) {
                        if (empty($value['total'])) {
                            continue;
                        }
                        echo '<li class="' . ($value['clean_name'] == @$_GET['catName'] ? "active" : "") . '">'
                        . '<a href="' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '" >'
                        . '<span class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></span>  ' . $value['name'] . ' <span class="badge">' . $value['total'] . '</span></a>';
                        mkSub($value['id']);
                        echo '</li>';
                    }
                    ?>

                    <?php
                    echo YouPHPTubePlugin::getHTMLMenuLeft();
                    ?>

                    <!-- categories END -->

                    <li>
                        <hr>
                    </li>
                    <?php
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
    <?php
    if (!empty($advancedCustom->underMenuBarHTMLCode->value)) {
        echo $advancedCustom->underMenuBarHTMLCode->value;
    }
} else if ($thisScriptFile["basename"] !== 'user.php' && empty($advancedCustom->disableNavbar)) {
    header("Location: {$global['webSiteRootURL']}user");
}
?>
