<div class="panel panel-default">
    <div class="panel-heading">
        <div class="alert alert-info" style="border-left: 5px solid #31708f; padding-left: 20px;">
            <p>
                <strong><i class="fas fa-image"></i> AI-Generated Image Preview:</strong> We use your video's <strong>title</strong> and <strong>description</strong> to create a unique and visually engaging AI-generated image.
            </p>
            <p>
                These images are designed to enhance your content’s visual appeal — ideal for use as thumbnails, background posters, or video covers.
            </p>
            <p>
                For the best results, ensure your title and description clearly reflect the main subject or mood of the video.
            </p>
        </div>

        <?php echo AI::getProgressBarHTML("image_{$videos_id}", ''); ?>
    </div>
    <div class="panel-body">
        <div id="ai-images" class="row">
            <!-- As imagens serão carregadas aqui -->
        </div>
    </div>
    <div class="panel-footer">
        <button class="btn btn-success btn-block" onclick="generateAIImages()">
            <i class="fa fa-images"></i> <?php echo __('Generate Image') ?>
            <?php
            if (!empty($priceForBasic)) {
                echo "<br><span class=\"label label-success\">{$priceForBasicText}</span>";
            }
            ?>
        </button>
    </div>
</div>


<script>
    async function generateAIImages() {
        await createAISuggestions('<?php echo AI::$typeImage; ?>');
        loadAIImage();
        loadAIUsage();
    }

    function loadAIImage() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/tabs/image.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    const container = $('#ai-images');
                    container.empty();

                    response.images.forEach(function(item) {
                        const imgURL = item.url;

                        const html = `
                        <div class="col-xs-12 col-sm-6 col-md-4 text-center" style="margin-bottom: 15px;">
                            <a href="${imgURL}" target="_blank">
                                <img src="${imgURL}" class="img img-responsive img-thumbnail" style="margin: 0 auto;"/>
                            </a>
                        </div>
                    `;
                        container.append(html);
                    });
                }
                modal.hidePleaseWait();
            }
        });
    }


    $(document).ready(function() {
        loadAIImage();
    });
</script>
