<?php
require_once $global['systemRootPath'].'objects/functionInfiniteScroll.php';
$post = $_POST;
$request = $_REQUEST;
// if there is no section display only the dateAdded row for the selected category
if (!empty($currentCat) && empty($_GET['showOnly'])) {
    $obj = AVideoPlugin::getObjectData("Gallery");
    setRowCount($obj->CategoriesRowCount * 3);
    if (empty($_GET['page'])) {
        unsetCurrentPage();
    }
    
    if(!empty($_GET['catName'])){
        resetCurrentPage();
    }
    $_REQUEST['current'] = $_GET['page'];

    echo '<!-- ' . basename(__FILE__) . ' -->';

    include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
    unset($_POST['sort']);
    $_POST['sort']['v.created'] = "DESC";
    $_POST['sort']['likes'] = "DESC";
    $_REQUEST['catName'] = $currentCat['clean_name'];
    $_REQUEST['doNotShowCatChilds'] = 1;
    $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
    global $contentSearchFound;
    if (empty($contentSearchFound)) {
        $contentSearchFound = !empty($videos);
    }
    $currPage = getCurrentPage();
    echo '<!-- currPage=' . $currPage . ' page='. (@$_GET['page']) .' line='.__LINE__.' -->';
    //var_dump($currPage, $_GET);exit;
    global $categoryLiveVideos;
    if (empty($categoryLiveVideos) && $currPage < 2) {
        $categoryLiveVideos = getLiveVideosFromCategory($currentCat['id']);
        if (!empty($categoryLiveVideos)) {
            $videos = array_merge($categoryLiveVideos, $videos);
        }
    }

    createCategorySection($videos);
    $global['doNotSearch'] = 1;
    $rows = Category::getChildCategories($currentCat['id']);
    $global['doNotSearch'] = 0;
    //var_dump($currentCat['id'], $rows);exit;
    $sort = $_POST['sort'];
    $_POST['sort'] = array();
    $_POST['sort']['v.created'] = 'DESC';
    $_POST['sort']['v.id'] = 'DESC';
    foreach ($rows as $key => $value) {
        $_REQUEST['catName'] = $value['clean_name'];
        $_REQUEST['doNotShowCatChilds'] = 0;
        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        createCategorySection($videos);
    } 
    $_POST['sort'] = $sort;
}

$_POST = $post;
$_REQUEST = $request;

function createCategorySection($videos)
{
    global $global, $args;
    $obj = AVideoPlugin::getObjectData("Gallery");
    if(empty($videos[0])){
        return;
    }
?>
    <!-- mainAreaCategory.php -->
    <div class="clear clearfix" id="Div<?php echo $videos[0]['clean_category']; ?>">
        <?php
        if (canPrintCategoryTitle($videos[0]['category'])) {
        ?>
            <h3 class="galleryTitle">
                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $videos[0]['clean_category']; ?>">
                    <i class="<?php echo $videos[0]['iconClass']; ?>"></i> <?php echo $videos[0]['category']; ?>
                </a>
                <?php
                if (!isHTMLEmpty($videos[0]['category_description'])) {
                    $duid = uniqid();
                    $titleAlert = str_replace(array('"', "'"), array('``', "`"), $videos[0]['category']);
                ?>
                    <a href="#" class="pull-right" onclick='avideoAlert("<?php echo $titleAlert; ?>", "<div style=\"max-height: 300px; overflow-y: scroll;overflow-x: hidden;\" id=\"categoryDescriptionAlertContent<?php echo $duid; ?>\" ></div>", "");$("#categoryDescriptionAlertContent<?php echo $duid; ?>").html($("#categoryDescription<?php echo $duid; ?>").html());return false;'><i class="far fa-file-alt"></i> <?php echo __("Description"); ?></a>
                    <div id="categoryDescription<?php echo $duid; ?>" style="display: none;"><?php echo $videos[0]['category_description']; ?></div>
                <?php
                }
                ?>
            </h3>
        <?php
        }
        ?>
        <div class="Div<?php echo $videos[0]['clean_category']; ?>Section">
            <?php
            createGallerySection($videos, true, true);
            ?>
        </div>
    </div>
    <!-- mainAreaCategory -->
    <div class="col-sm-12 gallerySection" >
        <?php
        $_REQUEST['catName'] = $videos[0]['clean_category'];
        $total = Video::getTotalVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        $totalPages = ceil($total / getRowCount());
        //var_dump($totalPages, $page);
        $categoryURL = "{$global['webSiteRootURL']}cat/{$videos[0]['clean_category']}/page/";
        //getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector="", $infinityScrollAppendIntoSelector="")
        echo getPagination($totalPages, "{$categoryURL}_pageNum_{$args}", 10, ".Div{$videos[0]['clean_category']}Section", ".Div{$videos[0]['clean_category']}Section");
        echo getPagination($totalPages, "{$categoryURL}_pageNum_{$args}");
        ?>
    </div>
<?php
}
