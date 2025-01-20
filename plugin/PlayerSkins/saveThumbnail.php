<?php
$videos_id = getVideos_id();
?>
<button class="btn btn-success btn-block btn-lg" id="captureScreenshot" data-toggle="tooltip"  title="<?php echo __('Save this moment as Thumbnail'); ?>">
    <i class="fa fa-camera"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __('Save this moment as Thumbnail'); ?></span>
</button>

<script>
    $(document).ready(function () {
        $('#captureScreenshot').on('click', async function () {
            player.pause();
            const video = $('#mainVideo_html5_api')[0]; // Get the video element

            if (!video || video.readyState < 2) {
                avideoAlertError('The video is not ready to capture a frame.');
                return;
            }

            // Create a canvas element
            const canvas = $('<canvas>')[0];
            const context = canvas.getContext('2d');

            // Set canvas size to match the video resolution
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw the current video frame onto the canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Determine if the image is portrait or not
            const isPortrait = canvas.height > canvas.width;

            // Convert canvas content to a data URL
            const dataURL = canvas.toDataURL('image/png');

            // Show confirmation dialog before saving
            const imgHtml = `<div style='text-align: center;'>
                                <img src='${dataURL}' style='max-width: 100%; max-height: 300px; border: 1px solid #ddd;' />
                             </div>`;
            const confirmSave = await avideoConfirm(imgHtml+'Do you want to save this frame as the thumbnail?');
            if (!confirmSave) {
                return; // Exit if the user cancels
            }

            // Save the image via AJAX
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/videoEditLight.php',
                data: {
                    videos_id: <?php echo $videos_id; ?>,
                    image: dataURL.split(',')[1], // Send Base64 data without prefix
                    portrait: isPortrait ? 1 : 0, // Send portrait flag
                },
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                    avideoResponse(response);
                }
            });
        });
    });
</script>
