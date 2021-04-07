<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$obj = AVideoPlugin::getObjectData("Gallery");
$liveobj = AVideoPlugin::getObjectData("Live");
$_REQUEST['rowCount'] = 2;
if(empty($_GET['current'])){
    $_REQUEST['current'] = 1;
}else{
    $_REQUEST['current'] = intval($_GET['current']);
}
$categories = Category::getAllCategories(false, true);

if(empty($categories)){
    return false;
}
$_REQUEST['current'] = 1;
$_REQUEST['rowCount'] = $obj->CategoriesRowCount;
?>
<div class="categoriesContainerItem">
<?php
foreach ($categories as $_cat) {
    $_GET['catName'] = $_cat['clean_name'];
    if (empty($liveobj->doNotShowLiveOnCategoryList)) {
        $currentCat = $_cat;
        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
    }
    unset($_POST['sort']);
    $_POST['sort']['v.created'] = "DESC";
    $_POST['sort']['likes'] = "DESC";
    $videos = Video::getAllVideos("viewableNotUnlisted", false, !$obj->hidePrivateVideos);
    if (empty($videos)) {
        continue;
    }
    if(empty($_cat['clean_name'])){
        continue;
    }
    ?>
    <div class="row clear clearfix">
        <?php 
        if(canPrintCategoryTitle($_cat['name'])){
        ?>
        <h3 class="galleryTitle">
            <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $_cat['clean_name']; ?>">
                <i class="<?php echo $_cat['iconClass']; ?>"></i> <?php echo $_cat['name']; ?>
            </a>
        </h3>
        <?php
        }
        createGallerySection($videos, "", array(), true);
        ?>
    </div>

    <?php
}
?>
</div>
<p class="pagination">
    <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/view/modeGalleryCategory.php?tags_id=<?php echo intval(@$_GET['tagsid']); ?>&catName=<?php echo @$_GET['catName']; ?>&search=<?php echo htmlentities(urlencode(getSearchVar())); ?>&current=<?php echo count($categories)?$_REQUEST['current'] + 1:$_REQUEST['current']; ?>"></a>
</p>