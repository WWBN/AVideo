<?php
if (empty($advancedCustom->showCategoryTopImages)) {
    echo '<!-- category images advancedCustom->showCategoryTopImages disabled -->';
    return false;
}
if (!empty($_REQUEST['catName'])) {
    $currentCat = Category::getCategoryByName($_REQUEST['catName']);
    if(!empty($currentCat)){
        $categories_id = $currentCat['id'];
        if (!Category::isAssetsValids($categories_id)) {
            echo "<!-- category images  assets invalid categories_id=$categories_id -->";
            return false;
        }
    }
} else {
    echo '<!-- category images  catName is empty -->';
    return false;
}
if(empty($categories_id)){
    echo '<!-- category images categories_id is empty -->';
    return false;
}

$photo = Category::getCategoryPhotoPath($categories_id);
$background = Category::getCategoryBackgroundPath($categories_id);

$data = ['id'=>$categories_id];

?>
<div class="row" style="position: relative; z-index: 1; margin-top: -15px;">
    <img src="<?php echo $background['url+timestamp']; ?>" 
         style="-webkit-mask-image: linear-gradient(to top, transparent 15%, black 85%);
         mask-image: linear-gradient(to top, transparent 15%, black 85%);
         width: 100%; margin-bottom: 10px;position: absolute; left: 0; top:0; z-index: -1;" 
         class="img img-responsive"/>
    <img src="<?php echo $photo['url+timestamp']; ?>" style="max-height: 15vw;  z-index: 1; margin: 10px;" 
         class="img img-responsive img-thumbnail hidden-sm hidden-xs" />
    <img src="<?php echo $photo['url+timestamp']; ?>" style="max-height: 15vw;  z-index: 1; margin: 5px;" 
         class="img img-responsive hidden-md hidden-lg" />
         <?php
         if (Category::canCreateCategory()) {
             ?>
            <button class="btn btn-danger btn-xs pull-right" onclick='avideoAjax(webSiteRootURL + "objects/categoryDeleteAssets.json.php", <?php echo json_encode($data); ?>);'>
                <i class="fas fa-trash"></i> <?php echo __('Delete Images'); ?>
            </button>
            <?php
        }
        ?>
</div>

