<?php
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$categories = Category::getAllCategories();

if (empty($_SESSION['language'])) {
    $lang = 'en';
} else {
    $lang = $_SESSION['language'];
}
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<script>
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

    ga('create', 'UA-96597943-1', 'auto');
    ga('send', 'pageview');

</script>
<nav class="navbar navbar-fixed-top ">
    <div class="navbar-header  navbar-default">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-2" >
                <div class="col-xs-3 col-sm-3 col-lg-3 ">
                    <button type="button" class="navbar-toggle pull-left" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="col-xs-9 col-sm-9 col-lg-9 ">
                    <a class="brand" href="<?php echo $global['webSiteRootURL']; ?>" >
                        <img src="<?php echo $global['webSiteRootURL']; ?>view/img/logo.png" alt="<?php echo $config->getWebSiteTitle(); ?>" class="img-responsive">
                    </a>
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-lg-8 " >
                <form class="navbar-form  " action="<?php echo $global['webSiteRootURL']; ?>" >
                    <div class="input-group"  style="width: 100%;">
                        <input class="form-control" type="text" name="search" placeholder="<?php echo __("Search"); ?>">
                        <span class="input-group-addon"  style="width: 50px;"><span class="glyphicon glyphicon-search"></span></span>
                    </div>
                </form>
            </div>
            <div class="col-xs-6 col-sm-6 col-lg-2" style="padding-top: 8px;" >
                <select class="selectpicker" id="navBarFlag" data-width="fit">
                    <?php
                    $flags = getEnabledLangs();
                    foreach ($flags as $value) {
                        $selected = "";
                        if ($lang == $value) {
                            $selected = 'selected="selected"';
                        }
                        echo "<option data-content='<span class=\"flag-icon flag-icon-{$value}\"></span>' value=\"{$value}\" {$selected}>{$value}</option>";
                    }
                    ?>
                </select>
                <script>
                    $(function () {
                        $('#navBarFlag').selectpicker();
                        $('#navBarFlag').on('change', function () {
                            var selected = $(this).find("option:selected").val();
                            window.location.href = "<?php echo $global['webSiteRootURL']; ?>?lang=" + selected;
                        });
                    });
                </script>
            </div>
        </div>


    </div>
    <div class="navbar-collapse collapse  col-xs-12 col-sm-12 col-lg-2">
        <div class="" style="padding: 5px;">
            <a href="<?php echo $global['webSiteRootURL']; ?>upload" class="btn btn-danger btn-lg btn-block">
                <span class="glyphicon glyphicon-upload" style="font-size: 1em;"></span> 
                <?php echo __("Video and Audio Upload"); ?>
            </a>
        </div>
        <div class="" style="padding: 5px;">
            <a href="<?php echo $global['webSiteRootURL']; ?>download" class="btn btn-danger btn-lg btn-block">
                <span class="fa fa-youtube-play" style="font-size: 1em;"></span> 
                <?php echo __("Import Videos from Sites"); ?><br>
                <small><small><?php echo __("YouTube, DailyMotion, Vimeo and more"); ?></small></small>
            </a>
        </div>
        <ul class="nav navbar-nav col-xs-12 col-sm-12 col-lg-12">
            <?php
            if (User::isLogged()) {
                ?>
                <li style="height: 60px;">
                    <div class="pull-left" style="margin-left: 10px;"><img src="<?php echo User::getPhoto(); ?>" style="max-width: 55px;"  class="img img-thumbnail img-responsive img-circle"/></div>
                    <div  style="margin-left: 70px;">
                        <?php echo User::getName(); ?>
                        <div><small><?php echo User::getMail(); ?></small></div>
                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-xs"><?php echo __("My Account"); ?></a>
                            <a href="<?php echo $global['webSiteRootURL']; ?>logoff" class="btn btn-danger btn-xs">
                                <span class="glyphicon glyphicon-log-out"></span> 
                                <?php echo __("Logoff"); ?>
                            </a>
                        </div>
                    </div>
                </li>
                <?php
                if (User::canUpload()) {
                    ?>
                    <li>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <span class="glyphicon glyphicon-facetime-video"></span> 
                            <?php echo __("Manager Audios and Videos"); ?>
                        </a>
                    </li>
                    <?php
                }
                if (User::isAdmin()) {
                    ?>
                    <li>
                        <a href="<?php echo $global['webSiteRootURL']; ?>users">
                            <span class="glyphicon glyphicon-user"></span> 
                            <?php echo __("Manager Users"); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $global['webSiteRootURL']; ?>categories">
                            <span class="glyphicon glyphicon-list"></span> 
                            <?php echo __("Manager Categories"); ?>
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

            <li class="<?php echo empty($_GET['catName']) ? "active" : ""; ?>"><a href="<?php echo $global['webSiteRootURL']; ?>"><span class="glyphicon glyphicon-align-justify"></span> <?php echo __("All categories"); ?></a></li>
            <?php
            foreach ($categories as $value) {
                echo '<li class="' . ($value['clean_name'] == @$_GET['catName'] ? "active" : "") . '"><a href="' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '" ><span class="glyphicon glyphicon-minus"></span>  ' . $value['name'] . '</a></li>';
            }
            ?>
        </ul>
    </div>
</nav>
<div class="tabbable-panel">
    <div class="tabbable-line">
        <ul class="nav nav-tabs">
            <li class="nav-item <?php echo empty($_SESSION['type']) ? "active" : ""; ?>">
                <a class="nav-link " href="<?php echo $global['webSiteRootURL']; ?>?type=all">
                    <span class="glyphicon glyphicon-star"></span> 
                    <?php echo __("Audios and Videos"); ?>
                </a>
            </li>
            <li class="nav-item <?php echo (!empty($_SESSION['type']) && $_SESSION['type'] == 'video') ? "active" : ""; ?>">
                <a class="nav-link " href="<?php echo $global['webSiteRootURL']; ?>videoOnly">
                    <span class="glyphicon glyphicon-facetime-video"></span> 
                    <?php echo __("Videos"); ?>
                </a>
            </li>
            <li class="nav-item <?php echo (!empty($_SESSION['type']) && $_SESSION['type'] == 'audio') ? "active" : ""; ?>">
                <a class="nav-link" href="<?php echo $global['webSiteRootURL']; ?>audioOnly">
                    <span class="glyphicon glyphicon-headphones"></span> 
                    <?php echo __("Audios"); ?>
                </a>
            </li>
        </ul></div>
</div>