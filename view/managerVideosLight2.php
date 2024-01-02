<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$videos_id = getVideos_id();

if (empty($videos_id)) {
    forbiddenPage('Videos ID empty');
}

User::loginFromRequest();
if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$isVideoTagsEnabled = AVideoPlugin::isEnabledByName('VideoTags');

$video = new Video('', '', $videos_id);
$title = $video->getTitle();
$description = $video->getDescription();
$categories_id = $video->getCategories_id();
$_page = new Page(array('Edit Video', $title));
$videoTags = '[]';
if ($isVideoTagsEnabled) {
    $_page->setExtraScripts(
        array(
            'plugin/VideoTags/bootstrap-tagsinput/bootstrap-tagsinput.min.js',
            'plugin/VideoTags/bootstrap-tagsinput/typeahead.bundle.js',
        )
    );
    $_page->setExtraStyles(
        array('plugin/VideoTags/bootstrap-tagsinput/bootstrap-tagsinput.css')
    );
    $videoTags = VideoTags::getTagsInputsJquery();
}
?>
<div class="container-fluid">
    <div class="panel panel-default ">
        <div class="panel-heading clearfix ">
            <h1 class="pull-left">
                <?php
                echo $title;
                ?>
            </h1>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                    <?php
                    $images = Video::getImageFromID($videos_id);

                    if (isMobile()) {
                        $viewportWidth = 250;
                    } else {
                        $viewportWidth = 800;
                    }

                    if (defaultIsPortrait()) {
                        $width = 540;
                        $height = 800;
                        $path = $images->posterPortraitPath;
                        $portrait = 1;
                    } else {
                        $width = 1280;
                        $height = 720;
                        $path = empty($images->posterLandscapePath) ? ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_PATH) : $images->posterLandscapePath;
                        $portrait = 0;
                    }

                    $image = str_replace([$global['systemRootPath'], DIRECTORY_SEPARATOR], [$global['webSiteRootURL'], '/'], $path);

                    $image = addQueryStringParameter($image, 'cache', filectime($path));
                    //var_dump($image, $images);exit;
                    $croppie1 = getCroppie(__("Upload Poster"), "saveVideoMeta", $width, $height, $viewportWidth);
                    
                    ?>
                        <div class="panel panel-default ">
                            <div class="panel-heading ">
                                <i class="fa-regular fa-image"></i>
                                <?php
                                echo __('Poster');
                                ?>
                            </div>
                            <div class="panel-body">
                                <?php
                                echo $croppie1['html'];
                                ?>
                            </div>
                        </div>
                    <?php

                    if ($isVideoTagsEnabled) {
                    ?>
                        <div class="panel panel-default ">
                            <div class="panel-heading ">
                                <i class="fa-solid fa-tags"></i>
                                <?php
                                echo __('Tags');
                                ?>
                            </div>
                            <div class="panel-body">
                                <?php
                                echo VideoTags::getTagsInputs(6, $videos_id);
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-sm-8">
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="title"><?php echo __('Title'); ?></label>
                            <input type="text" class="form-control" id="title" placeholder="<?php echo __('Title'); ?>" value="<?php echo $title; ?>">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="categories_id"><?php echo __('Categories'); ?></label>
                            <?php echo Layout::getCategorySelect('categories_id', $categories_id, 'categories_id'); ?>
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="description"><?php echo __('Description'); ?></label>
                            <textarea class="form-control" id="description" rows="10"><?php echo $description; ?></textarea>
                            <?php
                            echo ("<script>window.videos_id={$videos_id}</script>");
                            if (empty($advancedCustom->disableHTMLDescription)) {
                                echo getTinyMCE("description");
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-success btn-lg btn-block" onclick="<?php echo $croppie1['getCroppieFunction']; ?>">
                <i class="fas fa-save"></i> 
                <?php echo __('Save'); ?>
            </button>
        </div>
    </div>
</div>
<script>
    var closeWindowAfterImageSave = false;

    var modalimage = getPleaseWait();
    var modalmeta = getPleaseWait();
    
    function saveVideoMeta(image) {
        modalmeta.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/videoEditLight.php',
            data: {
                videos_id: <?php echo $videos_id; ?>,
                title: $('#title').val(),
                categories_id: $('#categories_id').val(),
                portrait: <?php echo $portrait; ?>,
                videoTags: <?php echo $videoTags; ?>,
                user: "<?php echo User::getUserName() ?>",
                pass: "<?php echo User::getUserPass() ?>",
                description: <?php
                                if (empty($advancedCustom->disableHTMLDescription)) {
                                    echo 'tinymce.get(\'description\').getContent()';
                                } else {
                                    echo '$(\'#description\').val()';
                                }
                                ?>,
                image: image,
            },
            type: 'post',
            success: function(response) {
                modalmeta.hidePleaseWait();
                avideoResponse(response);
                if (response && !response.error) {
                    if (close) {
                        avideoModalIframeClose();
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        setupFormElement('#title', 35, 65, true, true);
        <?php
        echo $croppie1['createCroppie'] . "('{$image}');";
        ?>
    });
</script>
<?php
$_page->print();
?>