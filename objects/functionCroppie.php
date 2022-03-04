<?php
global $croppieFilesAdded;
if (empty($croppieFilesAdded)) {
    ?>
    <link href="<?php echo getCDN(); ?>node_modules/croppie/croppie.css" rel="stylesheet" type="text/css"/>
    <script src="<?php echo getCDN(); ?>node_modules/croppie/croppie.min.js" type="text/javascript"></script>
    <?php
}
$croppieFilesAdded = 1;
?>
<div class="croppieDiv" objectName="uploadCrop<?php echo $uid; ?>">
    <div class="col-md-12 ">
        <div id="croppie<?php echo $uid; ?>" style="min-height: <?php echo $boundaryHeight+40; ?>px;"></div>
        <div class="btn-group btn-group-justified" role="group">
            <a id="upload-btn<?php echo $uid; ?>" class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo $buttonTitle; ?></a>
            <a id="delete-btn<?php echo $uid; ?>" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo __('Delete'); ?></a>
        </div>
    </div>
    <div class="col-md-12 ">
        <input type="file" id="upload<?php echo $uid; ?>" value="Choose a file" accept="image/*" style="display:none;" />
    </div>
</div>
<script>

    var uploadCrop<?php echo $uid; ?>;
    var createCroppie<?php echo $uid; ?>Timeout;
    function createCroppie<?php echo $uid; ?>(imageURL) {
        clearTimeout(createCroppie<?php echo $uid; ?>Timeout);
        if($('#croppie<?php echo $uid; ?>').is(":hidden")){
            createCroppie<?php echo $uid; ?>Timeout = setTimeout(function(){
                createCroppie<?php echo $uid; ?>(imageURL);
            },1000);
            return false;
        }
        var viewportWidth = <?php echo $viewportWidth; ?>;
        var viewportHeight = <?php echo $viewportHeight; ?>;
        var boundaryWidth = <?php echo $boundaryWidth; ?>;
        var boundaryHeight = <?php echo $boundaryHeight; ?>;
        
        var parentWidth = $('#croppie<?php echo $uid; ?>').parent().width();
        var totalWidth = viewportWidth+(boundaryWidth-viewportWidth);
        
        if(parentWidth <= totalWidth){
            
            var factor = (parentWidth/(totalWidth));
            console.log('createCroppie parent and factor ', parentWidth, totalWidth, factor, viewportWidth, viewportHeight, boundaryWidth, boundaryHeight);
            
            viewportWidth = parseInt(viewportWidth * factor);
            viewportHeight = parseInt(viewportHeight * factor);
            boundaryWidth = viewportWidth;
            boundaryHeight = viewportHeight;
            console.log('createCroppie make size smaller ', viewportWidth, viewportHeight, boundaryWidth, boundaryHeight);
        }else{
            console.log('createCroppie ', viewportWidth, viewportHeight, boundaryWidth, boundaryHeight);
        }

        
        uploadCrop<?php echo $uid; ?> = $('#croppie<?php echo $uid; ?>').croppie({
            //url: imageURL,
            //enableExif: true,
            enforceBoundary: <?php echo json_encode($enforceBoundary); ?>,
            enableResizeboolean: true,
            mouseWheelZoom: false,
            viewport: {
                width: viewportWidth,
                height: viewportHeight
            },
            boundary: {
                width: boundaryWidth,
                height: boundaryHeight
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
            <?php
            if($enforceBoundary){
                ?>
                $('#croppie<?php echo $uid; ?>').croppie('setZoom', <?php echo $zoom; ?>);    
                <?php
            }
            ?>
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
