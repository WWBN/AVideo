<?php
global $global, $config;
require_once __DIR__ . '/../videos/configuration.php';
$videos_id = getVideos_id();
$_page = new Page(array('List Categories'));
?>
<link href="<?php echo getURL('view/mini-upload-form/assets/css/style.css'); ?>" rel="stylesheet" />
<link href="<?php echo getURL('view/mini-upload-form/assets/css/smaller.css'); ?>" rel="stylesheet" />
<style>
    .image-col {
        margin-bottom: 15px;
    }

    .image-col img {
        margin: 0 !important;
    }

    .image-grid .img-thumbnail {
        margin-bottom: 15px;
        cursor: pointer;
        transition: border-color 0.3s;
    }

    .image-grid .selected {
        border-color: #337ab7;
    }
</style>

<div class="container-fluid" style="padding: 10px">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            <form id="upload" method="post" action="<?php echo $global['webSiteRootURL']; ?>view/list-images.upload.json.php" enctype="multipart/form-data">
                <div id="drop">
                    <a><?php echo __("Browse files"); ?></a>
                    <input type="file" name="upl" id="fileInput" multiple accept="image/*" />
                    <input type="hidden" name="videos_id" value="<?php echo $videos_id; ?>" />
                </div>
                <ul>
                    <!-- Upload progress shown here -->
                </ul>
            </form>
        </div>
        <div class="col-lg-10 col-md-9">
            <div class="row image-grid" id="imageGrid"></div>
        </div>
    </div>
</div>
<!-- jQuery File Upload Dependencies (se ainda não estão inclusos acima) -->
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.knob.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.ui.widget.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.iframe-transport.js'); ?>"></script>
<script src="<?php echo getURL('view/mini-upload-form/assets/js/jquery.fileupload.js'); ?>"></script>

<script>
    var videos_id = <?php echo json_encode($videos_id ?: null); ?>;

    function loadImages() {
        const url = webSiteRootURL + 'view/list-images.json.php';
        const query = videos_id ? {
            videos_id: videos_id
        } : {};

        $.getJSON(url, query, function(images) {
            $('#imageGrid').empty();
            images.forEach(function(img) {
                const col = $('<div class="col-xs-6 col-sm-4 col-md-3 text-center image-col"></div>');
                const image = $('<img class="img-thumbnail" src="' + img.url + '" data-src="' + img.url + '" data-filename="' + img.filename + '">');
                const delBtn = $('<button class="btn btn-xs btn-danger btn-block" style="margin-top: 5px;"><i class="fa fa-trash"></i> Delete</button>');

                delBtn.on('click', function(e) {
                    e.stopPropagation();
                    avideoConfirm(__('Are you sure you want to delete this image?')).then(function(response) {
                        if (response) {
                            $.post(webSiteRootURL + 'view/list-images.delete.json.php', {
                                filename: img.filename,
                                videos_id: videos_id
                            }, function(response) {
                                if (!response.error) {
                                    col.remove();
                                } else {
                                    avideoToastError(response.msg || 'Delete failed');
                                }
                            }, 'json');
                        }
                    });
                });

                image.on('click', function() {
                    $('#imageGrid img').removeClass('selected');
                    image.addClass('selected');

                    const uid = new URLSearchParams(window.location.search).get('uid');
                    window.parent.postMessage({
                        selectedImageURL: image.data('src'),
                        croppieUID: uid,
                        videos_id: videos_id
                    }, '*');
                });

                col.append(image).append(delBtn);
                $('#imageGrid').append(col);
            });
        });
    }


    $(document).ready(function() {
        $('#drop a').click(function() {
            $(this).parent().find('input').click();
        });

        $('#upload').fileupload({
            dropZone: $('#drop2'),
            pasteZone: null,
            add: function(e, data) {
                var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"' +
                    ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p class="action">Uploading...</p><p class="filename"></p><span></span></li>');

                tpl.find('p.filename').text(data.files[0].name)
                    .append('<i>' + humanFileSize(data.files[0].size) + '</i>');

                data.context = tpl.appendTo($('#upload ul'));
                tpl.find('input').knob();

                tpl.find('span').click(function() {
                    if (tpl.hasClass('working')) {
                        jqXHR.abort();
                    }
                    tpl.fadeOut(function() {
                        tpl.remove();
                    });
                });

                var jqXHR = data.submit();
            },
            progress: function(e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                data.context.find('input').val(progress).change();
                if (progress == 100) {
                    data.context.removeClass('working');
                }
            },
            fail: function(e, data) {
                data.context.addClass('error');
            },
            done: function(e, data) {
                avideoResponse(data.result);
                if (data.result.error) {
                    data.context.addClass('error');
                    data.context.find('p.action').text("Error");
                } else {
                    data.context.find('p.action').text("Done");
                    data.context.addClass('working');
                    // reload gallery
                    loadImages();
                }
            }
        });
        loadImages();
    });
</script>



<?php
$_page->print();
?>
