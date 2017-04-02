<?php
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$categories = Category::getAllCategories();

if (empty($_SESSION['language'])) {
    $lang = 'en';
} else {
    $lang = $_SESSION['language'];
}
?>
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
                        <img src="<?php echo $global['webSiteRootURL']; ?>view/img/logo.png" alt="<?php echo $global['webSiteTitle']; ?>" class="img-responsive">
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
                <select class="selectpicker" data-width="fit">
                    <option data-content='<span class="flag-icon flag-icon-us"></span> <?php echo __("English"); ?>' <?php if ($lang == "en") { ?>selected="selected" <?php } ?> value="en"><?php echo __("English"); ?></option>
                    <option  data-content='<span class="flag-icon flag-icon-es"></span> <?php echo __("Spanish"); ?>' <?php if ($lang == "es") { ?>selected="selected" <?php } ?> value="es"><?php echo __("Spanish"); ?></option>
                    <option  data-content='<span class="flag-icon flag-icon-fr"></span> <?php echo __("French"); ?>' <?php if ($lang == "fr") { ?>selected="selected" <?php } ?> value="fr"><?php echo __("French"); ?></option>
                    <option  data-content='<span class="flag-icon flag-icon-br"></span> <?php echo __("Brazilian Portuguese"); ?>' <?php if ($lang == "pt_BR") { ?>selected="selected" <?php } ?> value="pt_BR"><?php echo __("Brazilian Portuguese"); ?></option>
                </select>
                <script>
                    $(function () {
                        $('.selectpicker').selectpicker();
                        $('.selectpicker').on('change', function () {
                            var selected = $(this).find("option:selected").val();
                            window.location.href = "<?php echo $global['webSiteRootURL']; ?>?lang=" + selected;
                        });
                    });
                </script>
            </div>
        </div>


    </div>
    <div class="navbar-collapse collapse  col-xs-12 col-sm-12 col-lg-2">
        <div class="col-xs-12 col-sm-12 col-lg-12" style="padding: 5px;">
            <a href="<?php echo $global['webSiteRootURL']; ?>upload" class="btn btn-danger btn-lg col-xs-12 col-sm-12 col-lg-12">
                <span class="glyphicon glyphicon-upload"></span> 
                <?php echo __("Video Upload"); ?>
            </a>
        </div>
        <ul class="nav navbar-nav col-xs-12 col-sm-12 col-lg-12">
            <?php
            if (User::isLogged()) {
                ?>
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
                        <a href="<?php echo $global['webSiteRootURL']; ?>categories">
                            <span class="glyphicon glyphicon-list"></span> 
                            <?php echo __("Categories"); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos">
                            <span class="glyphicon glyphicon-facetime-video"></span> 
                            <?php echo __("Videos"); ?>
                        </a>
                    </li>
                    <?php
                }
                ?>
                <li>
                    <a href="<?php echo $global['webSiteRootURL']; ?>user">
                        <span class="glyphicon glyphicon-cog"></span> 
                        <?php echo __("Welcome"); ?> 
                        <?php echo User::getName(); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $global['webSiteRootURL']; ?>logoff">
                        <span class="glyphicon glyphicon-log-out"></span> 
                        <?php echo __("Logoff"); ?>
                    </a>
                </li>
                <?php
            } else {
                ?>
                <li>
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