<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';


$obj = YouPHPTubePlugin::getObjectData("YouPHPFlix2");
?>
<!DOCTYPE html>
<html>
    <head>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var pageDots = <?php echo empty($obj->pageDots) ? "false" : "true"; ?>;
        </script>

        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/js/flickty/flickity.min.css" rel="stylesheet" type="text/css" />
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
    </head>
    <body>
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container-fluid" id="mainContainer" style="display: none;"> 
            <?php
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/BigVideo.php';
            if ($obj->DateAdded) {


                $_POST['sort']['created'] = "DESC";
                $_POST['current'] = 1;
                $_POST['rowCount'] = $obj->maxVideos;

                $videos = Video::getAllVideos("viewable");
                if (!empty($videos)) {
                    ?>
                    <div class="row">
                        <h2>
                            <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                            echo __("Date added (newest)");
                            ?>
                        </h2>
                        <!-- Date Added -->
                        <?php
                        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                        ?>
                    </div>

                    <?php
                }
                if ($obj->MostWatched) {
                    unset($_POST['sort']);
                    $_POST['sort']['views_count'] = "DESC";
                    $videos = Video::getAllVideos("viewable");
                    ?>
                    <span class="md-col-12">&nbsp;</span>
                    <div class="row">
                        <h2>
                            <i class="glyphicon glyphicon-eye-open"></i> <?php echo __("Most watched"); ?>
                        </h2>
                        <!-- Most watched -->
                        <?php
                        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                        ?>
                    </div>
                    <?php
                }

                if ($obj->MostPopular) {
                    unset($_POST['sort']);
                    $_POST['sort']['likes'] = "DESC";
                    $videos = Video::getAllVideos("viewable");
                    ?>
                    <div class="row">
                        <span class="md-col-12">&nbsp;</span>
                        <h2>
                            <i class="glyphicon glyphicon-thumbs-up"></i> <?php echo __("Most popular"); ?>
                        </h2>
                        <!-- Most Popular -->
                        <?php
                        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                        ?>
                    </div>


                    <?php
                }

                if ($obj->Categories && empty($_GET['catName'])) {
                    unset($_POST['sort']);
                    $categories = Category::getAllCategories();
                    foreach ($categories as $value) {
                        unset($_POST['sort']);
                        $_GET['catName'] = $value['clean_name'];
                        $_POST['sort']['likes'] = "DESC";
                        $videos = Video::getAllVideos("viewable");
                        ?>
                        <div class="row">
                            <span class="md-col-12">&nbsp;</span>
                            <h2>
                                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_name']; ?>"><i class="fas fa-folder"></i> <?php echo $value['name']; ?></a>
                            </h2>
                            <!-- Categories -->
                            <?php
                            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                            ?>
                        </div>
                        <?php
                        unset($_GET['catName']);
                    }
                }else if ($obj->Categories && !empty($_GET['catName'])) {
                    unset($_POST['sort']);
                    $categories = Category::getChildCategoriesFromTitle($_GET['catName']);
                    foreach ($categories as $value) {
                        unset($_POST['sort']);
                        $_GET['catName'] = $value['clean_name'];
                        $_POST['sort']['likes'] = "DESC";
                        $videos = Video::getAllVideos("viewable");
                        ?>
                        <div class="row">
                            <span class="md-col-12">&nbsp;</span>
                            <h2>
                                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_name']; ?>"><i class="fas fa-folder"></i> <?php echo $value['name']; ?></a>
                            </h2>
                            <!-- Sub category -->
                            <?php
                            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                            ?>
                        </div>
                        <?php
                        unset($_GET['catName']);
                    }
                }

                unset($_POST['sort']);
                unset($_POST['current']);
                unset($_POST['rowCount']);
            }
            ?>
        </div>
        <div id="loading" class="loader"
             style="width: 30vh; height: 30vh; position: absolute; left: 50%; top: 50%; margin-left: -15vh; margin-top: -15vh;"></div>
        
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';

        if (!empty($tmpSessionType)) {
            $_SESSION['type'] = $tmpSessionType;
        } else {
            unset($_SESSION['type']);
        }
        $jsFiles = array("view/js/bootstrap-list-filter/bootstrap-list-filter.min.js", "plugin/YouPHPFlix2/view/js/flickty/flickity.pkgd.min.js", "view/js/webui-popover/jquery.webui-popover.min.js", "plugin/YouPHPFlix2/view/js/script.js");
        $jsURL = combineFiles($jsFiles, "js");
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/script.js" type="text/javascript"></script>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
    </body>
</html>
