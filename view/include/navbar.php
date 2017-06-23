<?php
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$categories = Category::getAllCategories();
if (empty($_SESSION['language'])) {
    $lang = 'us';
} else {
    $lang = $_SESSION['language'];
}
?>
<style>
    @media (max-width: 550px) {
        a.brand img{
            display: none;
        }
        a.brand{
            background: no-repeat url(<?php echo $global['webSiteRootURL'], $config->getLogo_small(); ?>);
            width: 32px;
            height: 32px;
            margin-left: 20px;
        }
    }
</style>
<nav class="navbar navbar-fixed-top ">
    <div class="navbar-header  navbar-default">
        <div class="row">
            <div class="col-xs-5 col-sm-4 col-lg-2" >
                <div class="col-xs-3 col-sm-3 col-lg-3 ">
                    <button type="button" class="navbar-toggle pull-left" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="col-xs-9 col-sm-9 col-lg-9 ">
                    <a class="brand" href="<?php echo $global['webSiteRootURL']; ?>" >
                        <img src="<?php echo $global['webSiteRootURL'], $config->getLogo(); ?>" alt="<?php echo $config->getWebSiteTitle(); ?>" class="img-responsive">
                    </a>
                </div>
            </div>
            <div class="col-xs-4 col-sm-5 col-lg-8" >
                <form class="navbar-form  " action="<?php echo $global['webSiteRootURL']; ?>" >
                    <div class="input-group"  style="width: 100%;">
                        <input class="form-control" type="text" name="search" placeholder="<?php echo __("Search"); ?>">
                        <span class="input-group-addon"  style="width: 50px;"><span class="glyphicon glyphicon-search"></span></span>
                    </div>
                </form>
            </div>
            <div class="col-xs-3 col-sm-3 col-lg-2">
                <select class="selectpicker" id="navBarFlag" data-width="fit">
                    <?php
                    $flags = getEnabledLangs();
                    foreach ($flags as $value) {
                        $selected = "";
                        if ($value == 'en') {
                            $value = 'us';
                        }
                        if ($lang == $value) {
                            $selected = 'selected="selected"';
                        }
                        echo "<option data-content='<span class=\"flag-icon flag-icon-{$value}\"></span>' value=\"{$value}\" {$selected}>{$value}</option>";
                    }
                    ?>
                </select>
                <script>
                    $(function () {
                        $('#navBarFlag').selectpicker('setStyle', 'btn-default');
                        $('#navBarFlag').selectpicker('setStyle', 'navbar-btn', 'add');
                        $('#navBarFlag').on('change', function () {
                            var selected = $(this).find("option:selected").val();
                            window.location.href = "<?php echo $global['webSiteRootURL']; ?>?lang=" + selected;
                        });
                    });
                </script>
            </div>
        </div>


    </div>
    <div class="navbar-collapse collapse  col-xs-12 col-sm-12 col-lg-2 list-group-item">
        <a href="<?php echo $global['webSiteRootURL']; ?>upload" class="btn btn-danger btn-block navbar-btn">
            <span class="glyphicon glyphicon-upload" style="font-size: 1em;"></span> 
            <?php echo __("Video and Audio Upload"); ?>
        </a>
        <a href="<?php echo $global['webSiteRootURL']; ?>download" class="btn btn-danger btn-block navbar-btn">
            <span class="fa fa-youtube-play" style="font-size: 1em;"></span> 
            <?php echo __("Import Videos from Sites"); ?><br>
            <small><small><?php echo __("YouTube, DailyMotion, Vimeo and more"); ?></small></small>
        </a>
        <ul class="nav navbar-nav">
            <?php
            if (User::isLogged()) {
                ?>
                <li style="height: 60px;">
                    <div class="pull-left" style="margin-left: 10px;"><img src="<?php echo User::getPhoto(); ?>" style="max-width: 55px;"  class="img img-thumbnail img-responsive img-circle"/></div>
                    <div  style="margin-left: 70px;">
                        <?php echo User::getName(); ?>
                        <div><small><?php echo User::getMail(); ?></small></div>
                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-xs">
                                <span class="fa fa-user-circle"></span> 
                                <?php echo __("My Account"); ?>
                            </a>
                            <?php
                            if (User::canUpload()) {
                                ?>
                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success btn-xs">
                                    <span class="glyphicon glyphicon-film"></span> 
                                    <span class="glyphicon glyphicon-headphones"></span> 
                                    <?php echo __("My videos"); ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </li>
                <li>
                    <div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>logoff" class="btn btn-danger btn-xs btn-block navbar-btn">
                            <span class="glyphicon glyphicon-log-out"></span> 
                            <?php echo __("Logoff"); ?>
                        </a>
                    </div>
                </li>
                <?php
                if (User::isAdmin()) {
                    ?>
                    <li>
                        <a href="<?php echo $global['webSiteRootURL']; ?>users">
                            <span class="glyphicon glyphicon-user"></span> 
                            <?php echo __("Users"); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $global['webSiteRootURL']; ?>subscribes">
                            <span class="fa fa-check"></span> 
                            <?php echo __("Subscriptions"); ?>
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
                    <?php
                }
                ?>
                <?php
            } else {
                ?>
                <li class="liLogin">
                    <a href="<?php echo $global['webSiteRootURL']; ?>user">
                        <span class="glyphicon glyphicon-log-in"></span> 
                        <?php echo __("Login"); ?>
                    </a>
                </li>
                <?php
            }
            ?>
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
</nav>
<div class="tabbable-panel bgWhite">
    <div class="tabbable-line">
        <ul class="nav nav-tabs">
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
            <?php
            foreach ($categories as $value) {
                echo '<li class="' . ($value['clean_name'] == @$_GET['catName'] ? "active" : "") . '"><a href="' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '" ><span class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></span>  ' . $value['name'] . '</a></li>';
            }
            ?>

            <!-- categories END -->
        </ul></div>
</div>