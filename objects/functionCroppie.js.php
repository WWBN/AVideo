<?php
global $croppieFilesAdded;
if (empty($croppieFilesAdded)) {
?>
    <link href="<?php echo getURL('node_modules/croppie/croppie.css'); ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo getURL('node_modules/croppie/croppie.min.js'); ?>" type="text/javascript"></script>
    <script>
        function getCroppie(uploadCropObject, callback, width, height) {

            function proceedWithCroppie() {
                //console.log('getCroppie 1', uploadCropObject);
                var ret = uploadCropObject.croppie('result', {
                    type: 'base64',
                    size: {
                        width: width,
                        height: height
                    },
                    format: 'png'
                }).then(function(resp) {
                    //console.log('getCroppie 2 ' + callback, resp);
                    eval(callback + "(resp);");
                }).catch(function(err) {
                    //console.log('cropieError getCroppie => ' + callback, err);
                    eval(callback + "(null);");
                });
                //console.log('getCroppie 3', ret);
            }

            // Check if the element is visible before proceeding
            var elementId = '#' + $(uploadCropObject).attr('id');
            if (isVisibleAndInViewport(elementId)) {
                proceedWithCroppie();
            } else {
                $(elementId).closest('.tab-pane').addClass('active').addClass('in');
                var checkVisibilityInterval = setInterval(function() {
                    if (isVisibleAndInViewport(elementId)) {
                        clearInterval(checkVisibilityInterval);
                        proceedWithCroppie();
                    }
                }, 500);
            }
        }
    </script>
<?php
}
$croppieFilesAdded = 1;
?>
<div class="croppieDiv" objectName="uploadCrop<?php echo $uid; ?>">
    <div class="col-md-12 ">
        <div id="croppie<?php echo $uid; ?>" style="min-height: <?php echo $boundaryHeight + 40; ?>px; overflow: hidden;"></div>
        <div class="clearfix"></div>
        <small class="text-muted text-center" style="display: block;"><?php echo __('Width'); ?>: <?php echo $resultWidth; ?>px | <?php echo __('Height'); ?>: <?php echo $resultHeight; ?>px</small>
        <div class="clearfix"></div>
        <div class="btn-group btn-group-justified" role="group">
            <a id="upload-btn<?php echo $uid; ?>" class="btn btn-primary" role="button">
                <i class="fa fa-upload"></i> <?php echo $buttonTitle; ?>
            </a>
            <a id="library-btn<?php echo $uid; ?>" class="btn btn-info" role="button">
                <i class="fa-solid fa-images"></i> <?php echo __('Select from Library'); ?>
            </a>
            <a id="delete-btn<?php echo $uid; ?>" class="btn btn-danger" role="button">
                <i class="fa fa-trash"></i> <?php echo __('Delete'); ?>
            </a>
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
        $('#croppie<?php echo $uid; ?>').show();
        if ($('#croppie<?php echo $uid; ?>').is(":hidden")) {
            createCroppie<?php echo $uid; ?>Timeout = setTimeout(function() {
                createCroppie<?php echo $uid; ?>(imageURL);
            }, 1000);
            return false;
        }
        var viewportWidth = <?php echo $viewportWidth; ?>;
        var viewportHeight = <?php echo $viewportHeight; ?>;
        var boundaryWidth = <?php echo $boundaryWidth; ?>;
        var boundaryHeight = <?php echo $boundaryHeight; ?>;

        var parentWidth = $('#croppie<?php echo $uid; ?>').parent().width();
        var totalWidth = viewportWidth + (boundaryWidth - viewportWidth);

        if (parentWidth <= totalWidth) {
            var factor = (parentWidth / (totalWidth));
            console.log('createCroppie parent and factor ', parentWidth, totalWidth, factor, viewportWidth, viewportHeight, boundaryWidth, boundaryHeight);

            viewportWidth = parseInt(viewportWidth * factor);
            viewportHeight = parseInt(viewportHeight * factor);
            boundaryWidth = viewportWidth;
            boundaryHeight = viewportHeight;
            console.log('createCroppie make size smaller ', viewportWidth, viewportHeight, boundaryWidth, boundaryHeight);
            $('#croppie<?php echo $uid; ?>').css("min-height", (boundaryHeight + 10) + "px");
        } else {
            console.log('createCroppie ', viewportWidth, viewportHeight, boundaryWidth, boundaryHeight);
        }

        var paddingTop = 25;
        var saveButton = 55;
        var slider = 25;
        var uploadDeleteButtons = 40;
        var parentHeight = $('body').height();

        var totalHeight = viewportHeight + (boundaryHeight - viewportHeight) + paddingTop + saveButton + slider + uploadDeleteButtons;

        if (parentHeight <= totalHeight) {
            var factor = (parentHeight / (totalHeight));
            console.log('createCroppie height parent and factor parentHeight, totalHeight', parentHeight, totalHeight);
            console.log('createCroppie height parent and factor factor, viewportWidth, viewportHeight', factor, viewportWidth, viewportHeight);

            viewportWidth = parseInt(viewportWidth * factor);
            viewportHeight = parseInt(viewportHeight * factor);
            boundaryWidth = viewportWidth;
            boundaryHeight = viewportHeight;
            console.log('createCroppie height make size smaller parentHeight, totalHeight, boundaryWidth, boundaryHeight', parentHeight, totalHeight, boundaryWidth, boundaryHeight);
            $('#croppie<?php echo $uid; ?>').css("min-height", (boundaryHeight + 10) + "px");
        } else {
            console.log('createCroppie height', parentHeight, totalHeight, viewportHeight, boundaryWidth, boundaryHeight);
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
        $('#upload<?php echo $uid; ?>').on('change', function() {
            readFileCroppie(this, uploadCrop<?php echo $uid; ?>);
        });

        $('#upload-btn<?php echo $uid; ?>').off('click');
        $('#upload-btn<?php echo $uid; ?>').on('click', function(ev) {
            $('#upload<?php echo $uid; ?>').trigger("click");
        });

        $('#library-btn<?php echo $uid; ?>').off('click');
        $('#library-btn<?php echo $uid; ?>').on('click', function(ev) {
            var url = webSiteRootURL + 'view/list-images.php?uid=<?php echo $uid; ?>';
            if (typeof mediaId == 'number' && !empty(mediaId)) {
                url = addQueryStringParameter(url, 'videos_id', mediaId);
            }
            avideoModalIframe(url);
        });


        $('#delete-btn<?php echo $uid; ?>').off('click');
        $('#delete-btn<?php echo $uid; ?>').on('click', function(ev) {
            restartCroppie<?php echo $uid; ?>('<?php echo getImageTransparent1pxURL(); ?>');
        });

        $('#croppie<?php echo $uid; ?>').croppie('bind', {
            url: addGetParam(imageURL, 'cache', Math.random())
        }).then(function() {
            <?php
            if ($enforceBoundary) {
            ?>
                //$('#croppie<?php echo $uid; ?>').croppie('setZoom', <?php echo $zoom; ?>);
                var croppieData = $('#croppie<?php echo $uid; ?>').croppie('get');
                $('#croppie<?php echo $uid; ?>').croppie('setZoom', croppieData.zoom);
            <?php
            }
            ?>
        });
    }

    function restartCroppie<?php echo $uid; ?>(imageURL) {
        console.log("restartCroppie<?php echo $uid; ?>", imageURL);
        $('#croppie<?php echo $uid; ?>').croppie('destroy');
        setTimeout(function() {
            createCroppie<?php echo $uid; ?>(imageURL);
        }, 1000);
    }

    if (!window._croppieLibraryListenerAdded) {
        window._croppieLibraryListenerAdded = true;

        window.addEventListener('message', function(event) {
            const data = event.data;
            if (data && data.selectedImageURL && data.croppieUID) {
                const uid = data.croppieUID;
                const imageURL = data.selectedImageURL;

                console.log(`Received image for croppie ${uid}: ${imageURL}`);

                // Calls restartCroppie{uid}(imageURL) if it exists
                const restartFunctionName = 'restartCroppie' + uid;
                if (typeof window[restartFunctionName] === 'function') {
                    avideoModalIframeClose();
                    window[restartFunctionName](imageURL);
                } else {
                    console.warn(`Function ${restartFunctionName} not found`);
                }
            }
        });
    }
</script>
