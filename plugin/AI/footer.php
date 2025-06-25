<script>
    function aiSocketMessage(json) {
        console.log(json);
        switch (json.type) {
            case '<?php echo AI::$typeTranslation; ?>':
                if (typeof loadLangs == 'function') {
                    loadLangs();
                }
                break;
            case '<?php echo AI::$typeBasic; ?>':
                if (typeof loadAIBasic == 'function') {
                    loadAIBasic();
                }
                break;
            case '<?php echo AI::$typeTranscription; ?>':
                if (typeof loadAITranscriptions == 'function') {
                    loadAITranscriptions();
                }
                break;
            case '<?php echo AI::$typeShorts; ?>':
                if (typeof loadAIShorts == 'function') {
                    loadAIShorts();
                }
                break;
            case '<?php echo AI::$typeImage; ?>':
                if (typeof loadAIImage == 'function') {
                    loadAIImage();
                }
                break;
            default:
                break;
        }
        if (typeof loadAIUsage == 'function') {
            loadAIUsage();
        }
        avideoToastSuccess('AI ' + json.type + ' received');
    }
</script>
