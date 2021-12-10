                       
<?php
$images = Video::getImageFromID($videos_id);

if(isMobile()){
    $viewportWidth = 250;
}else{
    $viewportWidth = 1024;
}

if(defaultIsPortrait()){
    $width = 540;
    $height = 800;
    $path = $images->posterPortraitPath;
    $portreait = 1;
}else{
    $width = 1280;
    $height = 720;
    $path = $images->posterLandscapePath;
    $portreait = 0;
}

$image = str_replace(array($global['systemRootPath'], DIRECTORY_SEPARATOR), array($global['webSiteRootURL'], '/'), $path);

$image = addQueryStringParameter($image, 'cache', filectime($path));
//var_dump($image);exit;
$croppie1 = getCroppie(__("Upload Poster"), "saveVideo", $width, $height, $viewportWidth);
echo $croppie1['html'];
?>

<button class="btn btn-success btn-lg btn-block" onclick="<?php echo $croppie1['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __('Save'); ?></button>
<script>
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
                    //avideoModalIframeClose();
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