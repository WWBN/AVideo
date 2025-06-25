<div class="panel panel-default">
    <div class="panel-heading">
        <div class="alert alert-info" style="border-left: 5px solid #31708f; padding-left: 20px;">
            <h4 class="text-primary" style="margin-top: 0;">
                <strong><i class="fas fa-rocket"></i> Boost Your Video SEO with AI</strong>
            </h4>
            <p>
                We're pleased to introduce an <strong>AI-powered SEO enhancement tool</strong> for your videos.
                This new feature intelligently generates optimized:
                <em>Titles, Descriptions (Casual & Professional), Meta Descriptions, Keywords, Summaries, Ratings, and Justifications</em>.
            </p>
            <p>
                By analyzing your video's current title and description, our AI delivers tailored SEO suggestions
                to maximize discoverability. For the most accurate results, we recommend including a
                <i class="fas fa-microphone-alt"></i> <strong><?php echo __("Transcription"); ?></strong> of your video — enabling deeper content understanding.
            </p>
            <p>
                <strong>Start using AI to elevate your content and stand out in search results.</strong>
            </p>
        </div>

        <?php
        echo AI::getProgressBarHTML("basic_{$videos_id}", '');
        ?>
    </div>
    <div class="panel-body">
        <table id="responses-list" class="table table-bordered table-hover">
            <thead>
                <!-- Headers will be added here dynamically -->
            </thead>
            <tbody>
                <!-- Rows will be added here dynamically -->
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <button class="btn btn-success btn-block" onclick="generateAIIdeas()">
            <i class="fa-solid fa-lightbulb"></i> <?php echo __('Generate Basic Ideas') ?>
            <?php
            if (!empty($priceForBasic)) {
                echo "<br><span class=\"label label-success\">{$priceForBasicText}</span>";
            }
            ?>
        </button>
    </div>
</div>

<script>
    async function generateAIIdeas() {
        await createAISuggestions('<?php echo AI::$typeBasic; ?>');
        loadAIBasic();
        loadAIUsage();
    }

    function loadAIBasic() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/tabs/basic.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {

                    var columnOrder = [
                        'videoTitles',
                        'casualDescription',
                        'professionalDescription',
                        'metaDescription',
                        'keywords',
                        'shortSummary',
                        'rrating',
                        'rratingJustification',
                    ];

                    var columnHeaders = {
                        'casualDescription': 'Casual Description',
                        'professionalDescription': 'Professional Description',
                        'metaDescription': 'Meta Description',
                        'rrating': 'Rating',
                        'rratingJustification': 'Rating Justification',
                        'shortSummary': 'Summary',
                        'keywords': 'Keywords',
                        'videoTitles': 'Titles'
                    };

                    var columnCallbackFunctions = [
                        'videoTitles',
                        'casualDescription',
                        'professionalDescription',
                        'metaDescription',
                        'keywords',
                        'shortSummary',
                        'rrating',
                    ];

                    var selector = '#responses-list';
                    //console.log(selector, response);
                    processAIResponse(selector, response, columnOrder, columnHeaders, columnCallbackFunctions);

                    loadTitleDescription();
                }
                modal.hidePleaseWait();
            }
        });
    }

    $(document).ready(function() {
        loadAIBasic();
    });
</script>
