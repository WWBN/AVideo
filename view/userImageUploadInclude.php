<?php
$editorID = 'imageEditor' . uniqid();
$croppie = getCroppie(__('Choose an image'), 'channelImageUpload', $imageEditor['width'], $imageEditor['height'], $imageEditor['screenWidth'], 0, 0, $imageEditor['type'] === 'photo', ['responsive' => true, 'hideDelete' => true]);
?>
<section class="panel panel-default image-editor image-editor-<?php echo $imageEditor['type']; ?>" id="<?php echo $editorID; ?>">
    <div class="panel-heading image-editor-heading">
        <h3 class="panel-title"><?php echo $imageEditor['title']; ?></h3>
        <?php echo getTourHelpButton('view/channelImages.help.json', 'btn btn-default btn-xs'); ?>
    </div>
    <div class="panel-body">
        <p class="help-block"><?php echo $imageEditor['help']; ?></p>
        <p class="help-block"><?php echo __('Choose an image, drag to position it, then adjust the zoom. Save each image separately.'); ?></p>
        <div class="image-editor-crop"><?php echo $croppie['html']; ?></div>
        <div class="image-editor-library well hidden" role="region" aria-label="<?php echo htmlspecialchars(__('Select from Library'), ENT_QUOTES, 'UTF-8'); ?>">
            <button type="button" class="btn btn-default btn-sm image-editor-library-close"><?php echo __('Close'); ?></button>
            <p class="image-editor-library-status" role="status"></p>
            <div class="image-editor-library-grid"></div>
        </div>
        <p class="help-block"><?php echo __('JPG, PNG, WebP or GIF. Maximum file size: 20 MB.'); ?></p>
        <div class="image-editor-actions">
            <button class="btn btn-success image-editor-save" type="button" disabled><i class="fas fa-save"></i> <?php echo $imageEditor['saveLabel']; ?></button>
            <button class="btn btn-default image-editor-reset" type="button" disabled><?php echo __('Cancel'); ?></button>
        </div>
        <p class="image-editor-status text-muted" role="status" aria-live="polite"><?php echo __('Loading'); ?></p>
    </div>
</section>
<script>
    $(document).ready(function () {
        initializeChannelImageEditor(<?php echo json_encode([
            'id' => $editorID,
            'cropID' => $croppie['id'],
            'restart' => $croppie['restartCroppie'],
            'width' => $imageEditor['width'],
            'height' => $imageEditor['height'],
            'type' => $imageEditor['type'],
            'url' => $imageEditor['url'],
            'endpoint' => $global['webSiteRootURL'] . $imageEditor['endpoint'],
            'libraryURL' => $global['webSiteRootURL'] . 'view/list-images.json.php',
            'maxPostBytes' => parse_size(ini_get('post_max_size')),
            'safeWidth' => User::$channel_art['DesktopMin'][1],
            'safeHeight' => User::$channel_art['DesktopMin'][2],
            'messages' => [
                'ready' => __('Choose an image to make changes.'),
                'changed' => __('Unsaved changes. Adjust the image, then save.'),
                'loading' => __('Loading'),
                'saving' => __('Saving'),
                'saved' => __('Saved'),
                'error' => __('Unable to save the image. Please try again.'),
                'invalid' => __('Choose a JPG, PNG, WebP or GIF image up to 20 MB.'),
                'loadError' => __('Unable to open this image. Please choose another image.'),
                'emptyLibrary' => __('No images found'),
                'selectImage' => __('Select an image'),
                'tooLarge' => __('Image too large or invalid'),
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
    });
</script>
