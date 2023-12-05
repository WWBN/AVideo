<?php
$video = new Video('', '', $videos_id);
$filename = $video->getFilename();
//var_dump($filename);exit;
$vttfile = getVideosDir() . "{$filename}/{$filename}.vtt";
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

    <div class="col-sm-6">

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="alert alert-info">
                    <h4><strong>AI-Powered Video Transcriptions!</strong></h4>
                    <p>We are thrilled to announce that we are now utilizing advanced AI technology to extract transcriptions from your videos! This powerful feature is designed to recognize and transcribe speech in any language, making your content more accessible and engaging.</p>
                    <p><strong>Note:</strong> To ensure accurate transcription, your videos should contain clear speech. Please be aware that videos without any spoken words, or those containing only sounds and instrumental music, cannot be transcribed by our AI system. Make sure your videos have audible and clear speech to take full advantage of this feature.</p>
                </div>
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
                    if ($hasTranscriptionFile) {
                        echo '<div class="alert alert-success"><strong>Success:</strong> A transcription has already been prepared for this video.</div>';
                    }
                    if (!$mp3fileExists) {
                        echo '<div class="alert alert-warning"><strong>Note:</strong> An MP3 file is required for transcription. Currently, there is no MP3 file associated with this video.</div>';
                    }
                    if (!$mp3s['isValid']) {
                        echo '<div class="alert alert-danger"><strong>Attention:</strong> ';
                        echo 'The MP3 is invalid.<br>';
                        echo "Regular MP3 len: {$mp3s['regular']['duration']}<br>";
                        echo "Lower MP3 len: {$mp3s['lower']['duration']}<br>";
                        echo "{$mp3s['msg']}<br>";
                        echo '</div>';
                    }
                    if ($mp3fileExists) {
                        $canTranscribe = true;
                        echo '<div class="alert alert-info hideIfvttFileExists"><strong>Ready for Transcription:</strong> Your video meets all the requirements and is now ready to be transcribed.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger"><strong>Attention:</strong> SubtitleSwitcher is required for transcriptions.</div>';
                }
                echo '</div>';
                if ($canTranscribe) {
                ?>
                    <button class="btn btn-danger btn-block showIfvttFileExists" onclick="deleteTranscriptionFile()">
                        <i class="fas fa-trash"></i> <?php echo __('Delete Transcription') ?>
                    </button>
                    <div class="alert alert-success hideIfvttFileExists">
                        <div class="row">
                            <div class="col-sm-4">
                                <select class="form-control hideIfvttFileExists" name="transcribeLang" id="transcribeLang">
                                    <option value=""><?php echo __("Automatic"); ?></option>
                                    <?php
                                    foreach (AI::$languages as $key => $value) {
                                        echo "<option value=\"{$key}\">{$value}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <button class="btn btn-success btn-block hideIfvttFileExists" onclick="generateAITranscription()">
                                    <i class="fas fa-microphone-alt"></i> <?php echo __('Generate Transcription') ?>
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
    <div class="col-sm-6">
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
                    startProgress(callback);
                    getProgress(type, callback, '');
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
            }
        });
    }

    function loadAITranscriptions() {
        modal.showPleaseWait();
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
                    processAIResponse(selector, response, columnOrder, columnHeaders, columnCallbackFunctions);
                    if (response.vttFileExists) {
                        $('body').addClass('vttFileExists');
                    } else {
                        //$('#transcriptionFooter').slideDown();
                        $('body').removeClass('vttFileExists');
                    }
                }
                modal.hidePleaseWait();
            }
        });
    }

    $(document).ready(function() {
        loadAITranscriptions();
        var callback = 'loadAITranscriptions();';
        startProgress(callback);
        getProgress('<?php echo AI::$typeTranscription; ?>', callback, '');

    });
</script>