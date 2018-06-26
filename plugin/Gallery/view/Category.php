<?php
if ((!empty($videos)) || ($obj->SubCategorys)) {
    ?>
    <?php if (($obj->CategoryDescription) && (!empty($_GET['catName']))) { ?>
        <h1 style="text-align: center;"><?php echo $video['category']; ?></h1>
        <p style="margin-left: 10%; margin-right: 10%; max-height: 200px; overflow-x: auto;"><?php echo $video['category_description']; ?></p>
        <?php
    }
    if (($obj->SubCategorys) && (!empty($_GET['catName']))) {
        unset($_POST['rowCount']);
        if (!empty($currentCat)) {
            $childCategories = Category::getChildCategories($currentCat['id']);
            $parentCat = Category::getCategory($currentCat['parentId']);
            // -1 is a personal workaround only
            if ((empty($parentCat)) && (($currentCat['parentId'] == "0") || ($currentCat['parentId'] == "-1"))) {
                if (!empty($_GET['catName'])) {
                    ?>
                    <div>
                        <a class="btn btn-default btn-sm pull-right"  href="<?php echo $global['webSiteRootURL']; ?>">
                            <i class="fa fa-backward"></i>
                            <?php echo __("Back to startpage"); ?> 
                        </a>
                        <hr>
                    </div>
                    <?php
                }
            } else if (!empty($parentCat)) {
                ?>
                <div>
                    <a class="btn btn-default btn-sm pull-right" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $parentCat['clean_name']; ?>">
                        <i class="fa fa-backward"></i>
                        <?php echo __("Back to") . " " . $parentCat['name']; ?> 
                    </a>
                    <hr>
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
        if ((!empty($childCategories)) && ((($currentCat['parentId'] != "0") || ($currentCat['parentId'] != "-1")))) {
            ?>         
            <div class="clear clearfix">
                <h3 class="galleryTitle"><i class="glyphicon glyphicon-download"></i>
                    <?php echo __("Sub-Category-Gallery"); ?>
                    <span class="badge"><?php echo count($childCategories); ?></span>
                </h3>
                <div class="row">
                    <?php
                    $countCols = 0;
                    $originalCat = $_GET['catName'];
                    unset($_POST['sort']);
                    $_POST['sort']['title'] = "ASC";

                    foreach ($childCategories as $cat) {
                        $_GET['catName'] = $cat['clean_name'];
                        $description = $cat['description'];

                        $_GET['limitOnceToOne'] = "1";
                        $videos = Video::getAllVideos();

                        // make a row each 6 cols
                        if ($countCols % 6 === 0) {
                            echo '</div><div class="row aligned-row ">';
                        }
                        $countCols ++;
                        unset($_GET['catName']);
                        ?>
                        <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                            <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $cat['clean_name']; ?>" title="<?php $cat['name']; ?>">
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
                                            $res = sqlDAL::readSql($sql,"i",array($value['categories_id']));
                                            $videoCount = sqlDAL::fetchArray($res);
                                            sqlDAL::close($res);
                                            break;
                                        }
                                    } else {
                                        $poster = $global['webSiteRootURL'] . "view/img/notfound.jpg";
                                        ?>
                                        <img src="<?php echo $poster; ?>" alt="" data-toggle="tooltip" title="<?php echo $description; ?>" class="thumbsJPG img img-responsive" id="thumbsJPG<?php echo $cat['id']; ?>" />
                                        <?php
                                        $sql = "SELECT COUNT(title) FROM videos WHERE categories_id = ?;";
                                        $res = sqlDAL::readSql($sql,"i",array($cat['id']));
                                        $videoCount = sqlDAL::fetchArray($res);
                                        sqlDAL::close($res);
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
                                <div data-toggle="tooltip" title="<?php echo $cat['name']; ?>" class="tile__title" style="border-radius: 10px; background-color: black; color: white; position: absolute; margin-left: 10%; width: 80% !important; bottom: 40% !important; opacity: 0.8 !important; text-align: center;">
                                    <?php echo $cat['name']; ?>
                                </div>
                            </a>
                        </div>          

                        <?php
                    } // end of foreach-cat       
                    unset($_POST['sort']);
                    if (!empty($originalCat)) {
                        $_GET['catName'] = $originalCat;
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }
}