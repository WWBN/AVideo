<?php

$videos_id = getVideos_id();
$isModeYouTube = 1;
$trascription = Ai_responses::getTranscriptionVtt($videos_id);
if (empty($trascription)) {
?>
    <a href="#" onclick="$('#pTranscriptionLink').click();">
        <div class="alert alert-info" role="alert">
            <strong>Transcription Needed!</strong>
            A transcription is required to create the short suggestions with AI.
            Please ensure the text is accurately transcribed for the best results.
        </div>
    </a>
<?php
    return false;
}
$video = Video::getVideoLight($videos_id);
$rows = Ai_responses_json::getAllFromAIType(AI::$typeShorts, $videos_id);
$responses = array();
foreach ($rows as $key => $value) {
    if (!empty($value['response'])) {
        $response = json_decode($value['response']);
        foreach ($response as $key2 => $shorts) {
            foreach ($shorts as $key3 => $short) {
                $responses[] = $short;
            }
        }
    }
}
$bookmark = AVideoPlugin::isEnabledByName('Bookmark');
?>
<style>
    #shortsPlayer.panel {
        height: 100vh;
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
</style>
<link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css" />

<div class="panel panel-default" id="<?php echo empty($responses) ? '' : 'shortsPlayer'; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-sm-2 col-lg-3"></div>
            <div class="col-sm-8 col-lg-6">
                <?php
                echo PlayerSkins::getMediaTag($video['filename']);
                ?>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (empty($responses)) {
        ?>
            <div class="alert alert-info">
                Currently, there are no AI-generated video shorts suggestions available for this video.
            </div>
        <?php
        } else {
        ?>
            <div class="row">
                <?php
                usort($responses, function ($a, $b) {
                    return $a->startTimeInSeconds - $b->startTimeInSeconds;
                });

                foreach ($responses as $key => $value) {
                    $buttons = array();
                    $buttons[] = '<button class="btn btn-primary" onclick="playVideoSegmentFromIndex(' . $key . ');" data-toggle="tooltip" title="' . __('Play') . '"><i class="fa-solid fa-play"></i></button>';
                    $buttons[] = '<button class="btn btn-success" onclick="submitVideoForm(' . $key . ');" data-toggle="tooltip" title="' . __('Save Cut') . '"><i class="fa-solid fa-scissors"></i></button>';
                    if ($bookmark) {
                        $buttons[] = '<button class="btn btn-warning" onclick="bookmarkFromIndex(' . $key . ');" data-toggle="tooltip" title="' . __('Bookmark') . '"><i class="fa-solid fa-bookmark"></i></button>';
                    }
                ?>
                    <div class="col-sm-3">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <h3 class="panel-title">
                                    <strong>
                                        <?= htmlspecialchars($value->shortTitle) ?>
                                    </strong>
                                </h3>
                                <div class="clearfix"></div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <input id="startTimeInSeconds<?= $key ?>" class="form-control" value="<?= secondsToDuration($value->startTimeInSeconds) ?>" />
                                    </div>
                                    <div class="col-sm-6">
                                        <input id="endTimeInSeconds<?= $key ?>" class="form-control" value="<?= secondsToDuration($value->endTimeInSeconds) ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <button type="button" class="pull-right btn btn-default btn-xs showMoreButton collapsed" data-toggle="collapse" data-target="#collapseBody<?= $key ?>" aria-expanded="false">
                                    <i class="fa-solid fa-plus"></i>
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <div id="collapseBody<?= $key ?>" class="collapse"> <!-- Make sure this ID matches the button's data-target -->
                                    <p><?= htmlspecialchars($value->textTranscription) ?></p>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="btn-group btn-group-justified">
                                    <?php
                                    foreach ($buttons as $btn) {
                                        echo $btn;
                                    }
                                    ?>
                                </div>
                            </div>
                            <form id="cutVideoForm<?= $key ?>" class="hidden" style="display: none;">
                                <textarea name="title"><?= $value->shortTitle ?></textarea>
                                <textarea name="videos_id"><?= $videos_id ?></textarea>
                                <textarea name="startTimeInSeconds"><?= $value->startTimeInSeconds ?></textarea>
                                <textarea name="endTimeInSeconds"><?= $value->endTimeInSeconds ?></textarea>
                                <textarea name="description"><?= htmlspecialchars($value->shortDescription) ?></textarea>
                            </form>
                        </div>
                    </div>
                <?php
                    if (($key + 1) % 4 == 0) {
                        echo '<div class="clearfix"></div>';
                    }
                }
                ?>
            </div>

        <?php
        }
        ?>
    </div>
    <?php
    if (empty($doNotGetShorts)) {
    ?>
        <div class="panel-footer">
            <button class="btn btn-success btn-block" onclick="suggestShorts()">
                <i class="fa-solid fa-lightbulb"></i> <?php echo __('Get shorts suggestions') ?>
            </button>
        </div>
    <?php
    }
    ?>
</div>

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
        var url = webSiteRootURL+"plugin/Bookmark/page/bookmarkSave.json.php";
        avideoAjax(url, {
            name: $('#cutVideoForm'+index+' textarea[name="title"]').text(),
            timeInSeconds: startTimeInSeconds,
            videos_id: <?= $videos_id ?>
        });
    }

    function submitVideoForm(index) {
        modal.showPleaseWait();
        var startTimeInSeconds = durationToSeconds($('#startTimeInSeconds' + index).val());
        var endTimeInSeconds = durationToSeconds($('#endTimeInSeconds' + index).val());     

        $('#cutVideoForm'+index+' textarea[name="startTimeInSeconds"]').text(startTimeInSeconds);
        $('#cutVideoForm'+index+' textarea[name="endTimeInSeconds"]').text(endTimeInSeconds);

        var formData = $('#cutVideoForm' + index).serialize(); // Serialize the form data

        // Perform the AJAX request
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/cutVideo.json.php', // Replace with your server endpoint
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
    $(document).ready(function() {
        $('#shortsPlayer [data-toggle="collapse"]').click(function() {
            var target = $(this).attr('data-target');
            $(target).collapse('toggle'); // Toggle the collapse state

            // Optionally, toggle aria-expanded attribute for accessibility
            var isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
        });
    });
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
</script>