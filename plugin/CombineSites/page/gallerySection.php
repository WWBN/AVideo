<?php
$elements = array('users_id', 'categories_id', 'playlists_id');
$items = CombineSitesGet::getAllFromSite($value['id'], true);
foreach ($items as $item) {
    $divId = "combineSites_{$item['id']}_{$value['id']}";
    $get_var = array();
    $title = "";
    foreach ($elements as $elem) {
        if (!empty($item[$elem])) {
            $elemId = ($item[$elem]);
            $get_var[] = "{$elem}={$elemId}";
            $title = $elem;
            break;
        }
    }
    $get_var[] = "search=" . getSearchVar();
    ?>
    <div id="<?php echo $divId; ?>">
        <div>    
            <div class="clear clearfix">
                <h3 class="galleryTitle">
                    <a class="btn-default">
                        <i class="fas fa-circle-notch fa-spin text-muted"></i>
                    </a>   
                </h3>
                <?php
                for ($i = 0; $i < 6; $i++) {
                    ?>
                    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 galleryVideo thumbsImage fixPadding" style="min-height: 175px;">
                        <a class="evideo" >
                            <div class="aspectRatio16_9" style="background-image: none;">
                                <img src="<?php echo $global['webSiteRootURL']; ?>view/img/video-placeholder-gray.png" alt="ypt" class="thumbsJPG img img-responsive">
                            </div>
                        </a>
                        <a class="h6 evideo" >
                            <h2>...</h2>
                        </a>
                        <div class="text-muted galeryDetails" style="overflow: hidden;">
                            <div>
                                <i class="fa fa-eye"></i>
                                <span itemprop="interactionCount">
                                    0 Views                                </span>
                            </div>
                            <div>
                                <i class="far fa-clock"></i> loading
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $("#<?php echo $divId; ?>").load("<?php echo $global['webSiteRootURL']; ?>plugin/CombineSites/page/get/index.php?combine_sites_id=<?php echo $value['id']; ?>&<?php echo implode("&", $get_var); ?>");
        });
    </script>
    <?php
}
?>