<?php
$className = '';
if (empty($video['id'])) {
    $video['id'] = 0;
    $className = 'hidden';
}
$maxLen = empty($advancedCustom->commentsMaxLength) ? 200 : $advancedCustom->commentsMaxLength;

$disabled = '';
$content = '';
$commentButtonText = '<i class="fas fa-comment fa-2x"></i><span class="hidden-md hidden-sm hidden-xs " ><br>' . __("Comment") . '</span>';
$button  = '<button class="btn btn-success " id="saveCommentBtn" style="height: 72px;" onclick="saveComment();">' . $commentButtonText . '</button>';
$button .= '<input type="file" id="commentImageInput" accept="image/jpeg, image/png, image/gif" style="display: none;">';
$button .= '<button class="btn btn-primary" id="uploadImageBtn" style="height: 72px;"><i class="fas fa-image fa-2x"></i></button>';
$js = "setupFormElement('#comment', 5, commentsmaxlen, true, true);";
if (!User::canComment()) {
    $js = "";
    $disabled = "disabled='disabled'";
    if (User::isLogged()) {
        $commentButtonText = '<i class="fas fa-comment-slash"></i> <span class="hidden-md hidden-sm hidden-xs">' . __("Verify") . '</span>';
        $content = __("Verify your email to be able to comment");
        $button = '<button class="btn btn-warning " style="height: 72px;" onclick="document.location=\'' . $global['webSiteRootURL'] . 'user\';" data-toggle="tooltip" title="' . __("Verify your email to be able to comment") . '">' . $commentButtonText . '</a>';
    } else {
        $commentButtonText = '<i class="fas fa-sign-in-alt"></i> <span class="hidden-md hidden-sm hidden-xs">' . __("Login") . '</span>';
        $content = __("You must login to be able to comment on videos");
        $button = '<button class="btn btn-warning " style="height: 72px;" onclick="document.location=\'' . $global['webSiteRootURL'] . 'user\';" data-toggle="tooltip" title="' . __("Login") . '">' . $commentButtonText . '</a>';
    }
}
?>
<div class="row <?php echo $className; ?>">
    <?php
    if (User::isAdmin()) {
    ?>
        <div class="col-xs-12">
            <label for="comment_users_id"><?php echo __('Select a user to comment as if you were him'); ?></label>
        </div>
    <?php
        $users_autocomplete = Layout::getUserAutocomplete(0, 'comment_users_id');
    }
    ?>
    <div class="col-xs-8" style="padding-right: 1px;">
        <textarea class="form-control custom-control" rows="3" style="resize:none" id="comment"
            <?php echo $disabled; ?>><?php
                                        echo $content;
                                        ?></textarea>
    </div>
    <div class="col-xs-4" style="padding-left: 1px;">
        <div class="btn-group btn-group-justified" role="group">
            <?php echo $button; ?>
        </div>
    </div>
</div>

<script>
    var commentsmaxlen = <?php echo $maxLen; ?>;
    var commentVideos_id = <?php echo intval($video['id']); ?>;
    $(document).ready(function() {
        <?php
        echo $js;
        ?>
    });

    var uploadCommentImageURL = webSiteRootURL + 'view/mini-upload-form/imageUpload.json.php';
    $(document).ready(function() {

        $('#uploadImageBtn').on('click', function() {
            $('#commentImageInput').click();
        });

        $('#commentImageInput').on('change', function() {
            var fileInput = this.files[0];
            if (fileInput) {
                var formData = new FormData();
                formData.append('comment_image', fileInput);
                formData.append('videos_id', commentVideos_id); // Send the video ID

                commentUploadImage(formData)
            }
        });

        $('#comment').on('dragover', function(e) {
            e.preventDefault();
        }).on('drop', function(e) {
            e.preventDefault();
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                var fileInput = files[0];
                var formData = new FormData();
                formData.append('comment_image', fileInput);
                formData.append('videos_id', commentVideos_id); // Send the video ID
                commentUploadImage(formData);

            }
        });
    });

    function commentUploadImage(formData) {
        modal.showPleaseWait();
        $.ajax({
            url: uploadCommentImageURL,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                modal.hidePleaseWait();
                var result = JSON.parse(response);
                if (!result.error) {
                    $('#comment').val($('#comment').val() + result.commentText);
                } else {
                    avideoAlertError(result.msg);
                }
            },
            error: function() {
                modal.hidePleaseWait();
                avideoAlertError('An error occurred while uploading the image');
            }
        });
    }
</script>