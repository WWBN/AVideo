<?php
if(empty($advancedCustom->showCategoryTopImages)){
    return false;
}
if (!empty($_GET['catName'])) {
    $currentCat = Category::getCategoryByName($_GET['catName']);
    $categories_id = $currentCat['id'];
    if(!Category::isAssetsValids($categories_id)){
        return false;
    }
}else{
    return false;
}

$photo = Category::getCategoryPhotoPath($categories_id);
$background = Category::getCategoryBackgroundPath($categories_id);
?>
<div class="row" style="position: relative; z-index: 1; margin-top: -15px;">
    <img src="<?php echo $background['url']; ?>" 
         style="-webkit-mask-image: linear-gradient(to top, transparent 15%, black 85%);
         mask-image: linear-gradient(to top, transparent 15%, black 85%);
         width: 100%; margin-bottom: 10px;position: absolute; left: 0; top:0; z-index: -1;" 
         class="img img-responsive"/>
    <img src="<?php echo $photo['url']; ?>" style="max-height: 15vw;  z-index: 1; margin: 10px;" 
         class="img img-responsive img-thumbnail hidden-sm hidden-xs" />
    <img src="<?php echo $photo['url']; ?>" style="max-height: 15vw;  z-index: 1; margin: 5px;" 
         class="img img-responsive hidden-md hidden-lg" />
</div>

