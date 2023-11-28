<div class="panel panel-default">
    <div class="panel-heading">
        <div class="alert alert-info">
            <h4><strong>Enhance Your Video SEO with AI</strong></h4>
            <p>We're excited to announce a new AI-driven feature to enhance your video SEO! This tool will automatically suggest optimized <strong>Titles, Casual Descriptions, Professional Descriptions, Meta Descriptions, Keywords, Summaries, Ratings, and Rating Justifications</strong> for your videos.</p>
            <p>Our AI analyzes your video's existing title and description to generate these SEO elements. For even more precise and tailored suggestions, we recommend providing a <i class="fas fa-microphone-alt"></i> <?php echo __("Transcription"); ?> of your video. This additional information allows our AI to better understand and optimize your content for search engines, boosting your video's visibility and reach.</p>
            <p>Start leveraging the power of AI to make your videos stand out in search results!</p>
        </div>
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
                    
                    var columnCalbackFunctions = [
                        'videoTitles',
                        'casualDescription',
                        'professionalDescription',
                        'metaDescription',
                        'keywords',
                        'shortSummary',
                        'rrating',
                    ];

                    var selector = '#responses-list';
                    processAIResponse(selector, response, columnOrder, columnHeaders, columnCalbackFunctions);

                    loadTitleDescription();
                }
                modal.hidePleaseWait();
            }
        });
    }

    $(document).ready(function() {
        loadAIBasic();
        getProgress('<?php echo AI::$typeBasic; ?>', '');
    });
</script>