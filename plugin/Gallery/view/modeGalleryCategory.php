<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$obj = AVideoPlugin::getObjectData("Gallery");
$_POST['rowCount'] = 2;
if(empty($_GET['current'])){
    $_POST['current'] = 1;
}else{
    $_POST['current'] = intval($_GET['current']);
}
$categories = Category::getAllCategories(false, true);

if(empty($categories)){
    return false;
}
$_POST['current'] = 1;
$_POST['rowCount'] = $obj->CategoriesRowCount;
?>
<div class="categoriesContainerItem">
<?php
foreach ($categories as $value) {
    $_GET['catName'] = $value['clean_name'];
    unset($_POST['sort']);
    $_POST['sort']['v.created'] = "DESC";
    $_POST['sort']['likes'] = "DESC";
    $videos = Video::getAllVideos("viewableNotUnlisted", false, !$obj->hidePrivateVideos);
    if (empty($videos)) {
        continue;
    }
    ?>
    <div class="clear clearfix">
        <h3 class="galleryTitle">
            <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_name']; ?>">
                <i class="<?php echo $value['iconClass']; ?>"></i> <?php echo $value['name']; ?>
            </a>
        </h3>
        <?php
        createGallerySection($videos, "", array(), true);
        ?>
    </div>

    <?php
}
?>
</div>
<p class="pagination">
    <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/view/modeGalleryCategory.php?current=<?php echo count($categories)?$_POST['current'] + 1:$_POST['current']; ?>"></a>
</p>