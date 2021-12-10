
<div class="row">
    <div class="form-group col-sm-6">
        <label for="title"><?php echo __('Title'); ?></label>
        <input type="text" class="form-control" id="title" placeholder="Enter email" value="<?php echo $title; ?>">
    </div>
    <div class="form-group col-sm-6">
        <label for="categories_id"><?php echo __('Categories'); ?></label>
        <?php echo Layout::getCategorySelect('categories_id', $categories_id, 'categories_id'); ?>
    </div>
    <div class="form-group col-sm-12">
        <label for="description"><?php echo __('Description'); ?></label>
        <textarea class="form-control" id="description" rows="10" ><?php echo $description; ?></textarea>
        <?php
        if (empty($advancedCustom->disableHTMLDescription)) {
            echo getTinyMCE("description");
        }
        ?>
    </div>
</div>
<button class="btn btn-success btn-lg btn-block" onclick="saveVideo();"><i class="fas fa-save"></i> <?php echo __('Save'); ?></button>

<script>
    function saveVideo() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'objects/videoEditLight.php',
            data: {
                videos_id: <?php echo $videos_id; ?>,
                title: $('#title').val(),
                categories_id: $('#categories_id').val(),
                description: tinymce.get('description').getContent()
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


    });
</script>