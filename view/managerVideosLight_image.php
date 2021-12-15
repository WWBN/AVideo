                       
<?php
$images = Video::getImageFromID($videos_id);

if(isMobile()){
    $viewportWidth = 250;
}else{
    $viewportWidth = 800;
}

if(defaultIsPortrait()){
    $width = 540;
    $height = 800;
    $path = $images->posterPortraitPath;
    $portreait = 1;
}else{
    $width = 1280;
    $height = 720;
    $path = empty($images->posterLandscapePath)?"{$global['systemRootPath']}view/img/notfound.jpg":$images->posterLandscapePath;
    $portreait = 0;
}

$image = str_replace(array($global['systemRootPath'], DIRECTORY_SEPARATOR), array($global['webSiteRootURL'], '/'), $path);

$image = addQueryStringParameter($image, 'cache', filectime($path));
//var_dump($image, $images);exit;
$croppie1 = getCroppie(__("Upload Poster"), "saveVideo", $width, $height, $viewportWidth);
echo $croppie1['html'];
?>
<hr>
<div class="btn-group btn-group-justified">
    <button class="btn btn-success btn-lg" onclick="closeWindowAfterImageSave=false;<?php echo $croppie1['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __('Save'); ?></button>
    <button class="btn btn-primary btn-lg" onclick="closeWindowAfterImageSave=true;<?php echo $croppie1['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __('Save And Close'); ?></button>
</div>
<script>
    var closeWindowAfterImageSave = false;
    function saveVideo(image) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/videoEditLight.php',
            data: {
                videos_id: <?php echo $videos_id; ?>,
                image: image,
                portreait: <?php echo $portreait; ?>,
            },
            type: 'post',
            success: function (response) {
                modal.hidePleaseWait();
                avideoResponse(response);
                if (response && !response.error) {
                    if(closeWindowAfterImageSave){
                        avideoModalIframeClose();
                    }
                }
            }
        });
        
    }

    $(document).ready(function () {

    <?php
    echo $croppie1['createCroppie']."('{$image}');";
    ?>
    });
</script>