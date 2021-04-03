<?php
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
unset($_POST['sort']);
cleanSearchVar();
$sites = CombineSitesDB::getAllActive();
reloadSearchVar();
$count = 1;
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <?php 
        echo getHTMLTitle( $siteTitle);
        ?>
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <script src="<?php echo getCDN(); ?>view/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container-fluid gallery">
            <div class="row text-center" style="padding: 10px;">
                <?php echo getAdsLeaderBoardTop(); ?>
            </div>
            <div class="col-sm-10 col-sm-offset-1 list-group-item">
                <div class="tabbable-panel nopadding">
                    <div class="tabbable-line">
                        <ul class="nav nav-tabs" id="combineSitesTabs">
                            <?php
                            $active = "";
                            if(empty($_COOKIE['combineSitesTabs']) || $_COOKIE['combineSitesTabs'] == 'combineSitesTabs0'){
                                $active = "active";
                            }
                            ?>
                            <li class="nav-item <?php echo $active;?>" id="channelPlayListsLi">
                                <a style="height: 40px;" id="combineSitesTabs0" class="nav-link" href="#tabHome" data-toggle="tab" aria-expanded="true" title="<?php echo __("Home"); ?>">
                                    <i class="fas fa-home"></i>
                                </a>
                            </li>
                            <?php
                            foreach ($sites as $value) {
                                $active = "";
                                if(!empty($_COOKIE['combineSitesTabs']) && $_COOKIE['combineSitesTabs'] == 'combineSitesTabs'.$count){
                                    $active = "active";
                                }
                                ?>
                                <li class="nav-item  <?php echo $active;?>" id="channelPlayListsLi">
                                    <a style="height: 40px;" id="combineSitesTabs<?php echo $count++; ?>"  class="nav-link " href="#tabSite<?php echo $value['id']; ?>" data-toggle="tab" aria-expanded="true" title="<?php echo $value['site_label']; ?>">
                                        <img style="height: 15px;" src="<?php echo $value['site_url']; ?>videos/favicon.png" class="img img-responsive">
                                    </a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                        <div class="tab-content clearfix">
                            <?php
                            $active = "";
                            if(empty($_COOKIE['combineSitesTabs']) || $_COOKIE['combineSitesTabs'] == 'combineSitesTabs0'){
                                $active = "active fade in";
                            }
                            ?>
                            <div class="tab-pane <?php echo $active;?>" id="tabHome">
                                <?php
                                include $global['systemRootPath'] . 'plugin/Gallery/view/mainArea.php';
                                ?>
                            </div>
                            <?php
                            $count = 1;
                            foreach ($sites as $value) {
                                $active = "";
                                if(!empty($_COOKIE['combineSitesTabs']) && $_COOKIE['combineSitesTabs'] == 'combineSitesTabs'.$count){
                                    $active = "active fade in";
                                }
                                $count++
                                ?>
                                <div class="tab-pane <?php echo $active;?>" id="tabSite<?php echo $value['id']; ?>">
                                    <?php
                                    include $global['systemRootPath'] . 'plugin/CombineSites/page/gallerySection.php';
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'plugin/Gallery/view/footer.php';
        ?>
        <script>
            $("#combineSitesTabs > li > a").on("shown.bs.tab", function (e) {
                Cookies.set('combineSitesTabs', $(this).attr('id'), {
                                            path: '/',
                                            expires: 365
                                        });
            });
            /*
            if (typeof Cookies.get('combineSitesTabs') !== 'undefined') {
                $("#"+Cookies.get('combineSitesTabs')).tab("show");
            }
             */
        </script>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>