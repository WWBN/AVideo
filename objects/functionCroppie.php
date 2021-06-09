<?php
global $croppieFilesAdded;
if (empty($croppieFilesAdded)) {
    ?>
    <link href="<?php echo getCDN(); ?>view/js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
    <script src="<?php echo getCDN(); ?>view/js/Croppie/croppie.min.js" type="text/javascript"></script>
    <?php
}
$croppieFilesAdded = 1;
?>
<div class="croppieDiv" objectName="uploadCrop<?php echo $uid; ?>">
    <div class="col-md-12 ">
        <div id="croppie<?php echo $uid; ?>" style="min-height: <?php echo $boundaryHeight+40; ?>px;"></div>
        <center>
            <a id="upload-btn<?php echo $uid; ?>" class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo $buttonTitle; ?></a>
            <a id="delete-btn<?php echo $uid; ?>" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo __('Delete'); ?></a>
        </center>
    </div>
    <div class="col-md-12 ">
        <input type="file" id="upload<?php echo $uid; ?>" value="Choose a file" accept="image/*" style="display:none;" />
    </div>
</div>
<script>

    var uploadCrop<?php echo $uid; ?>;

    function createCroppie<?php echo $uid; ?>(imageURL) {
        console.log('createCroppie');
        uploadCrop<?php echo $uid; ?> = $('#croppie<?php echo $uid; ?>').croppie({
            //url: imageURL,
            //enableExif: true,
            //enforceBoundary: true,
            mouseWheelZoom: false,
            viewport: {
                width: <?php echo $viewportWidth; ?>,
                height: <?php echo $viewportHeight; ?>
            },
            boundary: {
                width: <?php echo $boundaryWidth; ?>,
                height: <?php echo $boundaryHeight; ?>
            }
        });
        $('#upload<?php echo $uid; ?>').off('change');
        $('#upload<?php echo $uid; ?>').on('change', function () {
            readFileCroppie(this, uploadCrop<?php echo $uid; ?>);
        });

        $('#upload-btn<?php echo $uid; ?>').off('click');
        $('#upload-btn<?php echo $uid; ?>').on('click', function (ev) {
            $('#upload<?php echo $uid; ?>').trigger("click");
        });

        $('#delete-btn<?php echo $uid; ?>').off('click');
        $('#delete-btn<?php echo $uid; ?>').on('click', function (ev) {
            restartCroppie<?php echo $uid; ?>('<?php echo getImageTransparent1pxURL(); ?>');
        });

        $('#croppie<?php echo $uid; ?>').croppie('bind', {url: addGetParam(imageURL, 'cache', Math.random())}).then(function () {
            $('#croppie<?php echo $uid; ?>').croppie('setZoom', <?php echo $zoom; ?>)
        });
    }

    function restartCroppie<?php echo $uid; ?>(imageURL) {
        console.log("restartCroppie<?php echo $uid; ?>", imageURL);
        $('#croppie<?php echo $uid; ?>').croppie('destroy');
        setTimeout(function () {
            createCroppie<?php echo $uid; ?>(imageURL);
        }, 1000);
    }
</script>
