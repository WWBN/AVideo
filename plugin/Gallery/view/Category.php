<?php
//var_dump($videos, debug_backtrace());exit;
if ((!empty($videos)) || (!empty($obj) && $obj->SubCategorys)) {
    if (($obj->CategoryDescription) && (!empty($_REQUEST['catName']))) { 
        $category = Category::getCategoryByName($_REQUEST['catName']);
        ?>
        <h1 style="text-align: center;" class="categories_id_<?php echo $category['id']; ?>"><?php echo __($category['name']); ?></h1>
        <p style="margin-left: 10%; margin-right: 10%; max-height: 200px; overflow-x: auto;"><?php echo __($category['description']); ?></p>
        <?php
    }
    if (($obj->SubCategorys) && (!empty($_REQUEST['catName']))) {
        unset($_REQUEST['rowCount']);
        if (!empty($currentCat)) {
            $childCategories = Category::getChildCategories($currentCat['id']);
            $parentCat = Category::getCategory($currentCat['parentId']);
            // -1 is a personal workaround only
            if ((empty($parentCat)) && (($currentCat['parentId'] == "0") || ($currentCat['parentId'] == "-1"))) {
                if (!empty($_REQUEST['catName'])) { 
                    $backURL = getBackURL();
                    if(!empty($_REQUEST['getBackURL'])){
                        $backURL = $_REQUEST['getBackURL'];
                    }
                    ?>
                    <div class="clearfix" style="margin: 10px 0;">
                        <a class="btn btn-default btn-sm pull-left"  href="<?php echo $backURL; ?>">
                            <i class="fa fa-backward"></i>
                            <?php echo __("Back"); ?> 
                        </a>
                    </div>
                    <?php
                }
            } else if (!empty($parentCat)) {
                $backURL = '';
                if(!empty($_REQUEST['getBackURL'])){
                    $backURL = $_REQUEST['getBackURL'];
                }
                ?>
                <div class="clearfix" style="margin: 10px 0;">
                    <a class="btn btn-default btn-sm pull-left" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $parentCat['clean_name']; ?>?getBackURL=<?php echo urlencode($backURL); ?>">
                        <i class="fa fa-backward"></i>
                        <?php echo __("Back to") . " " . $parentCat['name']; ?> 
                    </a>
                </div>
                <?php
            }
        } else {
            ?>
            <div>
                <a class="btn btn-primary" onclick="window.history.back();" ><?php echo __("Back"); ?> </a>
            </div>
            <?php
        }
        //var_dump(!empty($childCategories) ,($currentCat['parentId'] != "0") , ($currentCat['parentId'] != "-1"));exit;
        if ((!empty($childCategories)) && ((($currentCat['parentId'] != "0") || ($currentCat['parentId'] != "-1")))) {
            $obj->BigVideo = false;
            
            include_once $global['systemRootPath'] . 'view/include/categoryTop.php';
            ?>
            <!-- category.php -->
            <div class="clear clearfix">
                <h3 class="galleryTitle">
                    <i class="fas fa-plus"></i>
                    <?php echo __('More'); ?>
                    <span class="badge"><?php echo count($childCategories); ?></span>
                </h3>
                <div class="row">
                    <?php
                    $countCols = 0;
                    $originalCat = $_REQUEST['catName'];
                    unset($_POST['sort']);
                    $_POST['sort']['title'] = "ASC";

                    foreach ($childCategories as $cat) {
                        $_REQUEST['catName'] = $cat['clean_name'];
                        $description = $cat['description'];

                        $_GET['limitOnceToOne'] = "1";
                        $videos = Video::getAllVideos();
                        
                        // make a row each 6 cols
                        if ($countCols % 6 === 0) {
                            echo '</div><div class="clearfix">';
                        }
                        $countCols++;
                        unset($_REQUEST['catName']);
                        
                        $backURL = getBackURL();
                        if(!empty($_REQUEST['getBackURL'])){
                            $backURL = $_REQUEST['getBackURL'];
                        }
                        $sql = "SELECT COUNT(title) FROM videos WHERE categories_id = ?;";
                        $res = sqlDAL::readSql($sql, "i", array($cat['id']));
                        $videoCount = sqlDAL::fetchArray($res);
                        sqlDAL::close($res);
                        if(!empty($videoCount[0])){

                            ?>
                            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo  galleryVideo<?php echo $cat['id']; ?> fixPadding">
                                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $cat['clean_name']; ?>?getBackURL=<?php echo urlencode($backURL);?>" title="<?php $cat['name']; ?>">
                                    <div class="aspectRatio16_9">
                                        <?php
                                        if (!empty($videos)) {
                                            foreach ($videos as $value) {
                                                $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                                $poster = $images->thumbsJpg;
                                                ?>
                                                <img src="<?php echo $poster; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />
                                                <?php if ((!empty($imgGif)) && (!$o->LiteGalleryNoGifs)) { ?>
                                                    <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                                                    <?php
                                                }
                                                $sql = "SELECT COUNT(title) FROM videos WHERE categories_id = ?;";
                                                $res = sqlDAL::readSql($sql, "i", array($value['categories_id']));
                                                $videoCount = sqlDAL::fetchArray($res);
                                                sqlDAL::close($res);
                                                break;
                                            }
                                        } else {
                                            $poster = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
                                            ?>
                                            <img src="<?php echo $poster; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive" id="thumbsJPG<?php echo $cat['id']; ?>" />
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="videoInfo">
                                        <?php if (!empty($videoCount)) { ?>
                                            <span class="label label-default" style="top: 1px !important; position: absolute;">
                                                <i class="glyphicon glyphicon-cd"></i>
                                                <?php echo $videoCount[0]; ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                    <div data-toggle="tooltip" title="<?php echo $cat['name']; ?>" class="subCategoryName">
                                        <?php echo $cat['name']; ?>
                                    </div>
                                </a>
                            </div>          
    
                            <?php
                        }
                    } // end of foreach-cat       
                    unset($_POST['sort']);
                    if (!empty($originalCat)) {
                        $_REQUEST['catName'] = $originalCat;
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }
}