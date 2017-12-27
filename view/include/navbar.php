<?php
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$categories = Category::getAllCategories();
if (empty($_SESSION['language'])) {
    $lang = 'us';
} else {
    $lang = $_SESSION['language'];
}

$json_file = file_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
$advancedCustom = json_decode($json_file);
?>
<nav class="navbar navbar-default navbar-fixed-top ">
    <ul class="items-container">
        <li>
            <ul class="left-side">
                <li>
                    <button class="btn btn-default navbar-btn pull-left" id="buttonMenu" ><span class="fa fa-bars"></span></button>
                    <script>
                        $('#buttonMenu').click(function (event) {
                            event.stopPropagation();
                            $('#sidebar').fadeToggle();

                        });

                        $(document).on("click", function () {
                            $("#sidebar").fadeOut();
                        });
                        $("#sidebar").on("click", function (event) {
                            event.stopPropagation();
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
        <li>
            <div class="navbar-header">
                <button type="button" class=" navbar-toggle btn btn-default navbar-btn" data-toggle="collapse" data-target="#myNavbar" style="padding: 6px 12px;">
                    <span class="fa fa-bars"></span>                      
                </button>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="right-menus">
                    <li class="">
                        <form class="navbar-form navbar-left"  action="<?php echo $global['webSiteRootURL']; ?>" >
                            <div class="input-group" >
                                <input class="form-control" type="text" name="search" placeholder="<?php echo __("Search"); ?>">
                                <span class="input-group-addon"  style="width: 50px;"><span class="glyphicon glyphicon-search"></span></span>
                            </div>
                        </form>
                    </li>
                    <?php
                    echo YouPHPTubePlugin::getHTMLMenuRight();
                    ?>
                    <?php
                    if (User::canUpload()) {
                        ?>
                        <li>

                            <div class="btn-group">
                                <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left"  data-toggle="dropdown">
                                    <span class="fa fa-video-camera"></span> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu" style="">
                                    <?php
                                    if (empty($advancedCustom->doNotShowEncoderButton)) {
                                        if (!empty($config->getEncoderURL())) {
                                            ?>
                                            <li>
                                                <a href="<?php echo $config->getEncoderURL(), "?webSiteRootURL=", urlencode($global['webSiteRootURL']), "&user=", urlencode(User::getUserName()), "&pass=", urlencode(User::getUserPass()); ?>" >
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
                                            <a  href="<?php echo $global['webSiteRootURL']; ?>upload" >
                                                <span class="fa fa-upload"></span> <?php echo __("Upload a MP4 video"); ?>
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
                </ul>
            </div>

        </li>
    </ul>


    <div id="sidebar" class="list-group-item" style="display: none;">
        <div id="sideBarContainer">
            <ul class="nav navbar">
                <?php
                if (User::isLogged()) {
                    ?>
                    <li>
                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>logoff" class="btn btn-default btn-block" >
                                <span class="glyphicon glyphicon-log-out"></span>
                                <?php echo __("Logoff"); ?>
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
                            <a href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo User::getId(); ?>" class="btn btn-danger btn-block" style="border-radius: 0;">
                                <span class="fa fa-youtube-play"></span>
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
                        <li>
                            <div>
                                <a href="<?php echo $global['webSiteRootURL']; ?>charts" class="btn btn-info btn-block" style="border-radius: 0;">
                                    <span class="fa fa-dashboard"></span>
                                    <?php echo __("Dashboard"); ?>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div>
                                <a href="<?php echo $global['webSiteRootURL']; ?>subscribes" class="btn btn-warning btn-block" style="border-radius: 0 0 4px 4px;">
                                    <span class="fa fa-check"></span>
                                    <?php echo __("Subscriptions"); ?>
                                </a>
                            </div>
                        </li>
                        <?php
                    }
                    ?>
                    <?php
                    if (User::isAdmin()) {
                        ?>

                        <li>
                            <hr>
                            <h2 class="text-danger"><?php echo __("Admin Menu"); ?></h2>
                            <ul  class="nav navbar" style="margin-bottom: 10px;">
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
                                    <a href="<?php echo $global['webSiteRootURL']; ?>ads">
                                        <span class="fa fa-money"></span>
                                        <?php echo __("Video Advertising"); ?>
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
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>siteConfigurations">
                                        <span class="glyphicon glyphicon-cog"></span>
                                        <?php echo __("Site Configurations"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>locale">
                                        <span class="glyphicon glyphicon-flag"></span>
                                        <?php echo __("Create more translations"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>plugins">
                                        <span class="fa fa-plug"></span> 
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
                                <span class="glyphicon glyphicon-log-in"></span>
                                <?php echo __("Login"); ?>
                            </a>
                        </div>
                    </li>
                    <?php
                }
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
                <!-- categories -->

                <li>
                    <hr>
                    <h3 class="text-danger"><?php echo __("Categories"); ?></h3>
                </li>
                <?php
                foreach ($categories as $value) {
                    echo '<li class="' . ($value['clean_name'] == @$_GET['catName'] ? "active" : "") . '">'
                    . '<a href="' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '" >'
                    . '<span class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></span>  ' . $value['name'] . '</a></li>';
                }
                ?>

                <?php
                echo YouPHPTubePlugin::getHTMLMenuLeft();
                ?>

                <!-- categories END -->

                <li>
                    <hr>
                </li>
                <li>
                    <a href="<?php echo $global['webSiteRootURL']; ?>about">
                        <span class="glyphicon glyphicon-info-sign"></span>
                        <?php echo __("About"); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $global['webSiteRootURL']; ?>contact">
                        <span class="glyphicon glyphicon-comment"></span>
                        <?php echo __("Contact"); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
