<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/category.php';
$obj = AVideoPlugin::getObjectData("Gallery");
$liveobj = AVideoPlugin::getObjectDataIfEnabled("Live");
$_REQUEST['rowCount'] = 2;
$_REQUEST['current'] = getCurrentPage();

$onlySuggested = $obj->CategoriesShowOnlySuggested;
if(!empty(getSearchVar())){
    $onlySuggested = false;
}
$sort = @$_POST['sort'];
unset($_POST['sort']);
$categories = Category::getAllCategories(false, true, $onlySuggested);
$total = Category::getTotalCategories(false, true, $onlySuggested);
$totalPages = ceil($total / getRowCount());
$page = getCurrentPage();
if ($totalPages < $page) {
    $page = $totalPages;
}
$link = "{$global['webSiteRootURL']}plugin/Gallery/view/modeGalleryCategory.php?tags_id=" . intval(@$_GET['tagsid']) . "&search=" . htmlentities(urlencode(getSearchVar())) . "&current={page}";

if (empty($categories)) {
    return false;
}
$_REQUEST['current'] = 1;
$_REQUEST['rowCount'] = $obj->CategoriesRowCount;
?>
<div class="categoriesContainerItem">
    <?php
    $timeLogName = TimeLogStart('modeGalleryCategory');
    foreach ($categories as $_cat) {
        $setCacheName = "include{$_cat['clean_name']}";
        $contents = ObjectYPT::getCache($setCacheName, 300);
        if(empty($contents)){
            $contents = getIncludeFileContent("{$global['systemRootPath']}plugin/Gallery/view/modeGalleryCategoryInclude.php", 
            ['_cat'=>$_cat, 'obj'=>$obj]);
            ObjectYPT::setCache($setCacheName, $contents);
        }
        echo $contents;
    }
    TimeLogEnd($timeLogName, __LINE__, 1);
    ?>
</div>
<!-- modeGalleryCategory -->
<div class="col-sm-12" style="z-index: 1;">
    <?php
    //getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector="", $infinityScrollAppendIntoSelector="")
    echo getPagination($totalPages, $page, $link, 10, ".categoriesContainerItem", ".categoriesContainerItem");
    ?>
</div>
<?php
$_POST['sort'] = $sort;
?>