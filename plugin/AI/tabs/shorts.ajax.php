<?php

require_once '../../../videos/configuration.php';
$objAI = AVideoPlugin::getObjectDataIfEnabled('AI');

if (empty($objAI)) {
    forbiddenPage('AI plugin is disabled');
}

if(!AI::canUseAI()){
    forbiddenPage('You cannot use AI');
}

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
$transcriptionJson = array();
foreach ($rows as $key => $value) {
    if (!empty($value['response'])) {
        $response = json_decode($value['response']);
        $transcriptionJson = json_decode($response->transcriptionJson);
        foreach ($response->shorts as $key2 => $shorts) {
            foreach ($shorts as $key3 => $short) {
                if ($short->endTimeInSeconds - $short->startTimeInSeconds < 30) {
                    continue;
                }
                $responses[] = $short;
            }
        }
    }
}

function getTranscriptionJson($start, $end, $transcriptionJson)
{
    $lines = array();

    foreach ($transcriptionJson as $key => $value) {
        if ($start <= $value->startInSeconds && $end >= $value->endInSeconds) {
            $parts = explode('.', $value->start);
            $lines[] = "<p><strong>{$parts[0]}</strong> $value->text</p>";
            //$lines[] = "<p><strong>{$value->start}:</strong> $start <= $value->startInSeconds && $end >= $value->endInSeconds</p>";
        }
    }
    return $lines;
}

function getShortsButtons($key)
{
    global $bookmark;
    $buttons = array();
    $buttons[] = '<button class="btn btn-primary" onclick="playVideoSegmentFromIndex(' . $key . ');" data-toggle="tooltip" title="' . __('Play') . '" type="button"><i class="fa-solid fa-play"></i></button>';
    $buttons[] = '<button class="btn btn-success" onclick="submitVideoForm(' . $key . ', \''.Video::ASPECT_RATIO_ORIGINAL.'\');" data-toggle="tooltip" title="' . __('Save Cut Original') . '" type="button"><i class="fa-solid fa-scissors"></i></button>';
    $buttons[] = '<button class="btn btn-success" onclick="submitVideoForm(' . $key . ', \''.Video::ASPECT_RATIO_HORIZONTAL.'\');" data-toggle="tooltip" title="' . __('Save Cut Horizontal') . '" type="button"><i class="fa-solid fa-desktop"></i></button>';
    $buttons[] = '<button class="btn btn-success" onclick="submitVideoForm(' . $key . ', \''.Video::ASPECT_RATIO_VERTICAL.'\');" data-toggle="tooltip" title="' . __('Save Cut Vertical') . '" type="button"><i class="fa-solid fa-mobile-screen-button"></i></button>';
    $buttons[] = '<button class="btn btn-success" onclick="submitVideoForm(' . $key . ', \''.Video::ASPECT_RATIO_SQUARE.'\');" data-toggle="tooltip" title="' . __('Save Cut Square') . '" type="button"><i class="fa-regular fa-square-full"></i></button>';
    if ($bookmark) {
        $buttons[] = '<button class="btn btn-warning" onclick="bookmarkFromIndex(' . $key . ');" data-toggle="tooltip" title="' . __('Bookmark') . '" type="button"><i class="fa-solid fa-bookmark"></i></button>';
    }
    return implode(PHP_EOL, $buttons);
}

$bookmark = AVideoPlugin::isEnabledByName('Bookmark');
//var_dump($transcriptionJson);
?>
<div class="panel panel-default" id="<?php echo empty($responses) ? '' : 'shortsPlayer'; ?>">
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
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <form id="cutVideoForm">
                        <input type="hidden" name="videos_id" value="<?= $videos_id ?>">
                        <input type="hidden" name="startTimeInSeconds" value="">
                        <input type="hidden" name="endTimeInSeconds" value="">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <input type="text" class="form-control" name="title" value="" placeholder="Custom Title">
                            </div>
                            <div class="panel-body">
                                <div class="btn-group btn-group-justified">
                                    <?php
                                    echo getShortsButtons("''");
                                    ?>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="row buttonsShorts">
                                    <div class="col-xs-6">
                                        <input id="startTimeInSeconds" class="maskTime form-control" value="00:00:00" placeholder="Start HH:MM:SS" />
                                    </div>
                                    <div class="col-xs-6">
                                        <input id="endTimeInSeconds" class="maskTime form-control" value="00:00:00" placeholder="END HH:MM:SS" />
                                    </div>
                                    <div class="col-xs-12">
                                        <button type="button" class="btn btn-default btn-block showMoreButton collapsed" data-toggle="collapse" data-target="#collapseBody" aria-expanded="false">
                                            <i class="fa-solid fa-plus"></i>
                                            <i class="fa-solid fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div id="collapseBody" class="collapse">
                                    <textarea name="description" class="form-control" placeholder="Custom Description"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
                usort($responses, function ($a, $b) {
                    return $a->startTimeInSeconds - $b->startTimeInSeconds;
                });

                $countCols = 1;
                foreach ($responses as $key => $value) {
                    $countCols++;
                ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <h3 class="panel-title">
                                    <small class="text-muted pull-right">
                                        <?php echo secondsToHumanTiming($value->endTimeInSeconds - $value->startTimeInSeconds); ?>
                                    </small>
                                    <strong>
                                        <?= htmlspecialchars($value->shortTitle) ?>
                                    </strong>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="btn-group btn-group-justified">
                                    <?php
                                    echo getShortsButtons($key);
                                    ?>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="row buttonsShorts">
                                    <div class="col-xs-6">
                                        <input id="startTimeInSeconds<?= $key ?>" class="maskTime form-control" value="<?= secondsToDuration($value->startTimeInSeconds) ?>" />
                                    </div>
                                    <div class="col-xs-6">
                                        <input id="endTimeInSeconds<?= $key ?>" class="maskTime form-control" value="<?= secondsToDuration($value->endTimeInSeconds) ?>" />
                                    </div>
                                    <div class="col-xs-12">
                                        <button type="button" class="btn btn-default btn-block showMoreButton collapsed" data-toggle="collapse" data-target="#collapseBody<?= $key ?>" aria-expanded="false">
                                            <i class="fa-solid fa-plus"></i>
                                            <i class="fa-solid fa-minus"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <div class="panel-footer">
                                <div id="collapseBody<?= $key ?>" class="collapse"> <!-- Make sure this ID matches the button's data-target -->
                                    <p class="shortDescription"><?= htmlspecialchars($value->shortDescription) ?></p>
                                    <div class="transcription">
                                        <?php
                                        $lines = getTranscriptionJson($value->startTimeInSeconds, $value->endTimeInSeconds, $transcriptionJson);
                                        echo implode(PHP_EOL, $lines);
                                        ?>
                                    </div>
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
                    if (($countCols) % 4 == 0) {
                        echo '<div class="clearfix hidden-sm hidden-md"></div>';
                    }
                    if (($countCols) % 3 == 0) {
                        echo '<div class="clearfix hidden-sm hidden-lg hidden-xl"></div>';
                    }
                    if (($countCols) % 2 == 0) {
                        echo '<div class="clearfix hidden-md hidden-lg hidden-xl"></div>';
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
                <?php
                if (!empty($priceForShorts)) {
                    echo "<br><span class=\"label label-success\">{$priceForShortsText}</span>";
                }
                ?>
            </button>
        </div>
    <?php
    }
    ?>
</div>
<script>
    $(document).ready(function() {
        $('#shortsPlayer [data-toggle="collapse"]').click(function() {
            var target = $(this).attr('data-target');
            $(target).collapse('toggle'); // Toggle the collapse state

            // Optionally, toggle aria-expanded attribute for accessibility
            var isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
        });

        $('.maskTime').mask('00:00:00');
    });
</script>