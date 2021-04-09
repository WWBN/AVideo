<?php
// if there is no section display only the dateAdded row for the selected category
if (!empty($currentCat) && empty($_GET['showOnly'])) {
    if (empty($_GET['page'])) {
        $_GET['page'] = 1;
    }
    $_REQUEST['current'] = $_GET['page'];


    include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
    unset($_POST['sort']);
    $_POST['sort']['v.created'] = "DESC";
    $_POST['sort']['likes'] = "DESC";
    $_GET['catName'] = $currentCat['clean_name'];
    $_REQUEST['rowCount'] = $obj->CategoriesRowCount * 3;
    $videos = Video::getAllVideos("viewableNotUnlisted", false, !$obj->hidePrivateVideos);
    if (!empty($videos)) {
        ?>
        <div class="row clear clearfix" id="Div<?php echo $currentCat['clean_name']; ?>">
            <?php
            if (canPrintCategoryTitle($currentCat['name'])) {
                ?>
                <h3 class="galleryTitle">
                    <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $currentCat['clean_name']; ?>">
                        <i class="<?php echo $currentCat['iconClass']; ?>"></i> <?php echo $currentCat['name']; ?>
                    </a>
                </h3>
                <?php
            }
            ?>
            <div class="Div<?php echo $currentCat['clean_name']; ?>Section">
                <?php
                createGallerySection($videos, "", array(), true);
                ?>
            </div>
        </div>
        <?php
        $total = Video::getTotalVideos("viewable");
        $totalPages = ceil($total / getRowCount());
        $page = getCurrentPage();
        if ($totalPages < $page) {
            $page = $totalPages;
        }
        ?>
        <!-- mainAreaCategory -->
        <div class="col-sm-12" style="z-index: 1;">
            <?php
            //getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector="", $infinityScrollAppendIntoSelector="")
            echo getPagination($totalPages, $page, "{$url}{page}{$args}", 10, ".Div{$currentCat['clean_name']}Section", ".Div{$currentCat['clean_name']}Section");
            ?>
        </div>
        <?php
    }
}