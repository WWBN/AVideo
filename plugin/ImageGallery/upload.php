<?php

require_once __DIR__ . '/../../videos/configuration.php';


if (!AVideoPlugin::isEnabledByName('ImageGallery')) {
    forbiddenPage('ImageGallery plugin is disabled');
}

$videos_id = getVideos_id();
ImageGallery::dieIfIsInvalid($videos_id);

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$video = new Video('', '', $videos_id);

$list = ImageGallery::listFiles($videos_id);

$_page = new Page(array('Edit Gallery'));

?>
<link href="<?php echo getURL('view/mini-upload-form/assets/css/style.css'); ?>" rel="stylesheet" />
<style>
    .galleryListImg {
        max-height: 200px;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default contest-panel">
        <div class="panel-heading clearfix">
            <h1><?php
                echo $video->getTitle();
                ?></h1>
        </div>
        <div class="panel-body">

            <form id="upload" method="post" action="<?php echo $global['webSiteRootURL']; ?>plugin/ImageGallery/upload.json.php?videos_id=<?php echo $videos_id; ?>" enctype="multipart/form-data">
                <div id="drop">
                    <a><?php echo __("Browse files"); ?></a>
                    <input type="file" name="upl" />
                </div>

                <ul>
                    <!-- The file uploads will be shown here -->
                </ul>
            </form>
        </div>
    </div>
</div>
<div class="container">
    <div id="imageGallery" class="row">
        <!-- Images will be loaded here -->
    </div>
</div>
<!-- JavaScript Includes -->
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.knob.js'); ?>"></script>

<!-- jQuery File Upload Dependencies -->
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.ui.widget.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.iframe-transport.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.fileupload.js'); ?>"></script>
<script>
    var galleryList = <?php echo json_encode($list); ?>;

    function deleteImageGallery(filename) {

        modal.showPleaseWait();
        var url = webSiteRootURL + 'plugin/ImageGallery/delete.json.php';
        data = {
            filename: filename,
            videos_id: <?php echo $videos_id; ?>
        }
        $.ajax({
            url: url,
            data: data,
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(response.msg);
                    galleryList = response.list;
                    displayImages();
                }
            },
            error: function(response) {
                //console.error('avideoAjax2', url, data, pleaseWait, response.responseJSON);
                if (response.responseJSON.error) {
                    avideoAlertError(response.responseJSON.msg);
                } else {
                    avideoToastError(response.responseJSON.msg);
                }
            },
            complete: function(response) {
                modal.hidePleaseWait();
            }
        });
    }

    function displayImages() {
        $('#imageGallery').empty(); // Clear the gallery first
        $.each(galleryList, function(i, image) {
            // Determine if the item is an image or a video
            var contentHtml;
            if (image.type === 'video/mp4') {
                contentHtml = '<video controls class="img-responsive center-block galleryListImg">' +
                    '<source src="' + image.url + '" type="' + image.type + '">Your browser does not support the video tag.</video>';
            } else { // Assuming everything else is an image
                contentHtml = '<img src="' + image.url + '" class="img-responsive center-block galleryListImg" alt="Image">';
            }

            var html = '<div class="col-xs-12 col-sm-6 col-md-3">' +
                '<div class="panel panel-default gallery-item">' +
                '<div class="panel-body">' + contentHtml + '</div>' +
                '<div class="panel-footer">' +
                '<button class="btn btn-danger btn-block delete-btn" onclick="deleteImageGallery(\'' + image.base + '\')"><i class="fa fa-trash"></i> Delete</button>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('#imageGallery').append(html);

            // Add clearfix div after every 2nd item for sm and every 4th item for md
            if ((i + 1) % 2 === 0) { // For sm screens
                $('#imageGallery').append('<div class="clearfix visible-sm-block"></div>');
            }
            if ((i + 1) % 4 === 0) { // For md screens and up
                $('#imageGallery').append('<div class="clearfix visible-md-block visible-lg-block"></div>');
            }
        });
    }


    $(document).ready(function() {
        var ul = $('#upload ul');
        $('#drop a').click(function() {
            // Simulate a click on the file input button
            // to show the file browser dialog
            $(this).parent().find('input').click();
        });
        // Initialize the jQuery File Upload plugin
        $('#upload').fileupload({
            dropZone: null,
            pasteZone: null,
            // This function is called when a file is added to the queue;
            // either via the browse button, or via drag/drop:
            add: function(e, data) {
                var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"' +
                    ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p style="color:#AAA;" class="action">Uploading...</p><p class="filename"></p><span></span></li>');
                // Append the file name and file size
                tpl.find('p.filename').text(data.files[0].name)
                    .append('<i>' + humanFileSize(data.files[0].size) + '</i>');
                // Add the HTML to the UL element
                data.context = tpl.appendTo(ul);
                // Initialize the knob plugin
                tpl.find('input').knob();
                // Listen for clicks on the cancel icon
                tpl.find('span').click(function() {

                    if (tpl.hasClass('working')) {
                        jqXHR.abort();
                    }

                    tpl.fadeOut(function() {
                        tpl.remove();
                    });
                });
                // Automatically upload the file once it is added to the queue
                var jqXHR = data.submit();
                videoUploaded = true;
            },
            progress: function(e, data) {

                // Calculate the completion percentage of the upload
                var progress = parseInt(data.loaded / data.total * 100, 10);
                // Update the hidden input field and trigger a change
                // so that the jQuery knob plugin knows to update the dial
                data.context.find('input').val(progress).change();
                if (progress == 100) {
                    data.context.removeClass('working');
                }
            },
            fail: function(e, data) {
                // Something has gone wrong!
                data.context.addClass('error');
            },
            done: function(e, data) {
                console.log('done', data);
                avideoResponse(data.result);
                if (data.result.error) {
                    data.context.addClass('error');
                    data.context.find('p.action').text("Error");
                } else {
                    data.context.find('p.action').html("Upload done");
                    data.context.addClass('working');
                    galleryList = data.result.list;
                    displayImages();
                    //$("#grid").bootgrid("reload");
                }
            }

        });
        // Prevent the default action when a file is dropped on the window
        $(document).on('drop dragover', function(e) {
            e.preventDefault();
        });
        // Initially display images
        displayImages();
    });
</script>
<?php
$_page->print();
?>
