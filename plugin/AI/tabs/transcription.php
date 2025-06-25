<?php
$video = new Video('', '', $videos_id);
$filename = $video->getFilename();
//var_dump($filename);exit;
$vttfile = AI::getFirstVTTFile($videos_id);
//echo $vttfile;
$hasTranscriptionFile = file_exists($vttfile) && filesize($vttfile) > 20;
//$hasTranscriptionFile = false;
$mp3s = AI::getLowerMP3($videos_id);
//var_dump($mp3s['lower']['paths']['path']);exit;
$mp3fileExists = $mp3s['isValid'];
$canTranscribe = false;
$columnCallbackFunctions = ['text'];

//var_dump($hasTranscriptionFile, $vttfile, filesize($vttfile));exit;
?>
<style>
    .showIfvttFileExists {
        display: none;
    }

    .vttFileExists #pTranscription .hideIfvttFileExists {
        display: none;
    }

    .vttFileExists .showIfvttFileExists {
        display: block;
    }
</style>
<div class="row">
    <div class="col-sm-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="alert alert-info" style="border-left: 5px solid #31708f; padding-left: 20px;">
                    <p>
                        <strong><i class="fas fa-info-circle"></i> Important:</strong> For accurate transcriptions, your videos must contain clear, audible speech.
                        Please note that videos with no spoken words — or only instrumental music or sound effects — cannot be processed by our AI transcription system.
                    </p>
                    <p>
                        Ensure that speech is present and understandable in your content to fully benefit from this feature.
                    </p>
                </div>

                <?php
                echo AI::getProgressBarHTML("transcription_{$videos_id}", __('Automatic'));
                foreach (AI::LANGS as $key => $value) {
                    echo AI::getProgressBarHTML("transcription_{$key}_{$videos_id}", $value);
                }
                ?>
            </div>
            <div class="panel-body">
                <table id="responsesT-list" class="table table-bordered table-hover">
                    <thead>
                        <!-- Headers will be added here dynamically -->
                    </thead>
                    <tbody>
                        <!-- Rows will be added here dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="panel-footer" id="transcriptionFooter">
                <?php
                echo '<div class="container-fluid">';
                if (AVideoPlugin::isEnabledByName('SubtitleSwitcher')) {
                    if ($video->getType() != Video::$videoTypeVideo) {
                        echo '<div class="alert alert-danger"><strong>Error:</strong> Transcription services are available exclusively for self-hosted videos.</div>';
                    }
                    if (!$mp3fileExists) {
                        echo '<div class="alert alert-warning"><strong>Note:</strong> An MP3 file is required for transcription. Currently, there is no MP3 file associated with this video.</div>';
                    } else {
                        $canTranscribe = true;
                    }
                    if (!$mp3s['isValid']) {
                        echo '<div class="alert alert-danger"><strong>Attention:</strong> ';
                        echo 'The MP3 is invalid.<br>';
                        echo "Regular MP3 len: {$mp3s['regular']['duration']}<br>";
                        echo "Lower MP3 len: {$mp3s['lower']['duration']}<br>";
                        echo "{$mp3s['msg']}<br>";
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger"><strong>Attention:</strong> SubtitleSwitcher is required for transcriptions.</div>';
                }
                echo '</div>';
                if ($canTranscribe) {
                ?>
                    <div class="alert alert-success">
                        <div class="row">
                            <div class="col-sm-3">
                                <select class="form-control" name="transcribeLang" id="transcribeLang">
                                    <option value=""><?php echo __("Automatic"); ?></option>
                                    <?php
                                    foreach (AI::LANGS as $key => $value) {
                                        echo "<option value=\"{$key}\">{$value}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-9">
                                <button class="btn btn-success btn-block" onclick="generateAITranscription()">
                                    <i class="fas fa-microphone-alt"></i> <?php echo __('Generate Transcription') ?>
                                    <?php
                                    if (!empty($priceForTranscription)) {
                                        echo "<br><span class=\"label label-success\">{$priceForTranscriptionText}</span>";
                                    }
                                    ?>
                                </button>
                            </div>
                            <hr>
                            <small class="col-sm-12">
                                Our AI model has the capability to automatically detect the language in an audio file and transcribe it accordingly.
                                However, if the automatic language detection is not accurately identifying the language
                                in your audio files or you want to force a translation,
                                you can specify the language manually to improve the accuracy of the transcription.
                            </small>
                        </div>
                    </div>
                <?php
                } else {
                    if ($mp3fileExists) {
                        echo '<!-- mp3fileExists -->';
                    } else {
                        echo '<!-- mp3fileExists == false -->';
                    }
                    if ($hasTranscriptionFile) {
                        echo '<!-- hasTranscriptionFile -->';
                    } else {
                        echo '<!-- hasTranscriptionFile == false -->';
                    }
                }
                ?>
            </div>
        </div>

    </div>
    <div class="col-sm-4">
        <?php
        include $global['systemRootPath'] . 'plugin/AI/tabs/translation.php';
        ?>
    </div>
</div>

<script>
    var hasTranscriptionRecord = false;

    async function generateAITranscription() {
        await createAITranscription();
        loadAITranscriptions();
        loadAIUsage();

        //$('#transcriptionFooter').slideUp();
    }


    async function createAITranscription() {
        return new Promise((resolve, reject) => {
            modalContinueAISuggestions.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/AI/async.json.php',
                data: {
                    videos_id: <?php echo $videos_id; ?>,
                    type: '<?php echo AI::$typeTranscription; ?>',
                    language: $('#transcribeLang').val()
                },
                type: 'post',
                success: function(response) {
                    modalContinueAISuggestions.hidePleaseWait();
                    if (response.error) {
                        avideoAlertError(response.msg);
                        reject(response.msg);
                    } else {
                        avideoToast(response.msg);
                        //location.reload();
                        resolve();
                    }
                    var callback = 'loadTitleDescription();';
                },
                complete: function(resp) {
                    response = resp.responseJSON
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


    function deleteTranscriptionFile() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/deleteTranscription.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                avideoResponse(response);
                loadAITranscriptions();
                modal.hidePleaseWait();
            },
            complete: function(resp) {
                response = resp.responseJSON
                modal.hidePleaseWait();
                console.log(response);
                if (response.error) {
                    avideoAlertError(response.msg);
                }
            }
        });
    }

    function loadAITranscriptions() {
        var modalloadAITranscriptions = getPleaseWait();
        modalloadAITranscriptions.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/tabs/transcriptions.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    var columnOrder = [
                        'language',
                        'size',
                        'text',
                    ];
                    var columnHeaders = {
                        'language': 'Language',
                        'size': 'Size',
                        'text': 'Text',
                    };
                    var columnCallbackFunctions = <?php echo json_encode($columnCallbackFunctions); ?>;
                    var selector = '#responsesT-list';
                    //console.log(columnCallbackFunctions);
                    //console.log(selector, response);
                    processAIResponse(selector, response, columnOrder, columnHeaders, columnCallbackFunctions);
                    if (response.vttFileExists) {
                        $('body').addClass('vttFileExists');
                    } else {
                        //$('#transcriptionFooter').slideDown();
                        $('body').removeClass('vttFileExists');
                    }
                }
                modalloadAITranscriptions.hidePleaseWait();
            },
            complete: function(resp) {
                response = resp.responseJSON
                modal.hidePleaseWait();
                console.log(response);
                if (response.error) {
                    avideoAlertError(response.msg);
                }
            }
        });
    }

    $(document).ready(function() {
        loadAITranscriptions();

    });
</script>
