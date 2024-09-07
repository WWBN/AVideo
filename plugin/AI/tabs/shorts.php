<?php
$videos_id = getVideos_id();
$isModeYouTube = 1;
$video = Video::getVideoLight($videos_id);
$bookmark = AVideoPlugin::isEnabledByName('Bookmark');
//var_dump($transcriptionJson);
?>
<script src="<?php echo getURL('node_modules/jquery-mask-plugin/dist/jquery.mask.min.js'); ?>"></script>
<style>
    #shortsPlayer.panel {
        height: calc(100vh - 150px);
        /* Make the panel take the full viewport height */
        display: flex;
        flex-direction: column;
    }

    #shortsPlayer .panel-body {
        overflow-y: auto;
        flex-grow: 1;
    }

    #shortsPlayer .showMoreButton.collapsed .fa-minus,
    #shortsPlayer .showMoreButton .fa-plus {
        display: none;
    }

    #shortsPlayer .showMoreButton.collapsed .fa-plus {
        display: inline-block;
    }

    #shortsPlayer .shortDescription {
        height: 100px;
        overflow-y: scroll;
    }

    #shortsPlayer .transcription {
        height: 150px;
        overflow-y: scroll;
    }
</style>
<link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css" />
<div class="row">
    <div class="col-sm-2 col-lg-3"></div>
    <div class="col-sm-8 col-lg-6">
        <?php
        echo PlayerSkins::getMediaTag($video['filename']);
        ?>
    </div>
</div>
<div id="shortsContainer"></div>
<?php
include $global['systemRootPath'] . 'view/include/video.min.js.php';
?>
<?php
echo AVideoPlugin::afterVideoJS();
?>
<script>
    function playVideoSegmentFromIndex(index) {
        var startTimeInSeconds = durationToSeconds($('#startTimeInSeconds' + index).val());
        var endTimeInSeconds = durationToSeconds($('#endTimeInSeconds' + index).val());
        playVideoSegment(startTimeInSeconds, endTimeInSeconds);
    }

    function bookmarkFromIndex(index) {
        var startTimeInSeconds = durationToSeconds($('#startTimeInSeconds' + index).val());
        var url = webSiteRootURL + "plugin/Bookmark/page/bookmarkSave.json.php";
        avideoAjax(url, {
            name: $('#cutVideoForm' + index + ' textarea[name="title"]').text(),
            timeInSeconds: startTimeInSeconds,
            videos_id: <?= $videos_id ?>
        });
    }

    function submitVideoForm(index, aspectRatio) {
        modal.showPleaseWait();
        var startTimeInSeconds = durationToSeconds($('#startTimeInSeconds' + index).val());
        var endTimeInSeconds = durationToSeconds($('#endTimeInSeconds' + index).val());

        var startSelector = '#cutVideoForm' + index + ' [name="startTimeInSeconds"]';
        var endSelector = '#cutVideoForm' + index + ' [name="endTimeInSeconds"]';

        $(startSelector).text(startTimeInSeconds);
        $(startSelector).val(startTimeInSeconds);
        $(endSelector).text(endTimeInSeconds);
        $(endSelector).val(endTimeInSeconds);

        var formData = $('#cutVideoForm' + index).serialize(); // Serialize the form data

        // Perform the AJAX request
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/cutVideo.json.php?aspectRatio='+aspectRatio, // Replace with your server endpoint
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(response.msg);
                    if (typeof response.eval !== 'undefined') {
                        eval(response.eval);
                    }
                }
            },
            error: function(xhr, status, error) {
                if (xhr.responseJSON.error) {
                    avideoAlertError(xhr.responseJSON.msg);
                } else {
                    avideoToastError(xhr.responseJSON.msg);
                }
            },
            complete: function(response) {
                modal.hidePleaseWait();
            }
        });
    }
    <?php
    if (empty($doNotGetShorts)) {
    ?>
        async function suggestShorts() {
            await createAISuggestions('<?php echo AI::$typeShorts; ?>');
            loadAIUsage();
        }
    <?php
    }
    ?>

    function loadAIShorts() {
        var modalloadAIShorts = getPleaseWait();
        modalloadAIShorts.showPleaseWait();
        var url = webSiteRootURL + 'plugin/AI/tabs/shorts.ajax.php';
        $.ajax({
            url: url,
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                $('#shortsContainer').html(response);
                modalloadAIShorts.hidePleaseWait();
            }
        });
    }
    $(document).ready(function() {
        loadAIShorts();
    });
    var autoplay = false;var forceautoplay = false;var forceNotautoplay = true;
</script>