<?php
$paths = AI::getMP3Path($videos_id);

$mp3Exists = !empty($paths['path']) && file_exists($paths['path']);
$hlsIsEnabled = AVideoPlugin::loadPluginIfEnabled('VideoHLS');
?>

<div class="panel panel-default">
    <div class="panel-body">
        <?php if (!$mp3Exists): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> MP3 file is missing. Please ensure that the video has an MP3 version available for dubbing.
            </div>
        <?php elseif (!$hlsIsEnabled): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> The HLS plugin is not enabled. Please enable the HLS plugin to use this feature.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-sm-12">
                    <p>Use this tool to generate dubbing for your HLS video in the language of your choice. Please note that the video must be in HLS format.</p>
                </div>
                <div class="col-sm-6">
                    <label for="transcribeDubLang">Dubbing Language:</label>
                    <select class="form-control" name="transcribeDubLang" id="transcribeDubLang" required>
                        <?php
                        foreach (AI::DubbingLANGS as $key => $value) {
                            echo "<option value=\"{$value['code']}\">{$value['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label>&nbsp;</label>
                    <button class="btn btn-success btn-block" onclick="generateDubbing()">
                        <i class="fas fa-microphone-alt"></i> <?php echo __('Generate Dubbing') ?>
                        <?php
                        if (!empty($priceForDubbing)) {
                            echo "<br><span class=\"label label-success\">{$priceForDubbingText} per second</span>";
                        }
                        ?>
                    </button>
                </div>
                <div class="col-sm-12">
                    <p class="help-block">Click the button above to begin generating the dubbing for your selected language. This process may take a while depending on the length of the video.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    var hasTranscriptionRecord = false;

    async function generateDubbing() {
        await createAIDubbing();
        loadAIUsage();
    }

    async function createAIDubbing() {
        return new Promise((resolve, reject) => {
            modalContinueAISuggestions.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/AI/async.json.php',
                data: {
                    videos_id: <?php echo $videos_id; ?>,
                    type: '<?php echo AI::$typeDubbing; ?>',
                    language: $('#transcribeDubLang').val()
                },
                type: 'post',
                success: function(response) {
                    modalContinueAISuggestions.hidePleaseWait();
                    if (response.error) {
                        avideoAlertError(response.msg);
                        reject(response.msg);
                    } else {
                        avideoToast(response.msg);
                        resolve();
                    }
                },
                complete: function(resp) {
                    const response = resp.responseJSON;
                    console.log(response);
                    modalContinueAISuggestions.hidePleaseWait();
                    if (response.error) {
                        avideoAlertError(response.msg);
                        reject(response.msg);
                    }
                }
            });
        });
    }
</script>
