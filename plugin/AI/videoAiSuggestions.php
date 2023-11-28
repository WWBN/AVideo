<?php
require_once '../../videos/configuration.php';

$videos_id = getVideos_id();

// each 60 of video will take about 5 minutes to complete

if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}
if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}
//$rows = Ai_responses::getTranscriptions($videos_id);var_dump($rows);exit;
//var_dump(Ai_responses::hasTranscriptions($videos_id));exit;

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}
$video = new Video('', '', $videos_id);
$_page = new Page(['Video Metatags']);
?>
<style>
    .aiItem {
        margin: 4px 0;
        border: 2px solid #CCC;
        padding: 2px;
        border-radius: 2px;
    }

    #responsesT-list>tbody>tr>td>div {
        max-height: 200px;
        overflow: auto;
    }

    .aiItem .save-btn {
        margin: 2px 0;
    }

    #pPP iframe {
        width: 100%;
        min-height: 75vh;
        border: none;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-3 col-lg-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo Video::getVideoImagewithHoverAnimationFromVideosId($videos_id); ?>
                </div>
                <div class="panel-body">
                    <h1 id="currentTitle"><?php echo $video->getTitle(); ?></h1>
                </div>
                <div class="panel-footer" id="currentDescription">
                    <?php echo $video->getDescription(); ?>
                </div>
                <div class="panel-body">
                    <strong>Meta Description:</strong>
                    <span id="currentMetaDescription"></span>
                </div>
                <div class="panel-footer">
                    <strong>Keywords:</strong>
                    <span id="currentTags"></span>
                </div>
                <div class="panel-body">
                    <strong>Summary:</strong>
                    <span id="currentShortSummary"></span>
                </div>
                <div class="panel-footer">
                    <strong>Rating:</strong>
                    <span id="currentRating"></span>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-9 col-lg-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>
                        Unlock the full potential of your video's reach!
                        Click '<i class="fas fa-robot"></i> <?php echo __('Generate AI Suggestions'); ?>'
                        now for tailored suggestions that can skyrocket your video's visibility.
                    </strong>
                </div>
                <div class="panel-heading">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a data-toggle="tab" href="#pbasic">
                                <i class="fa-solid fa-lightbulb"></i>
                                <?php echo __("Basic"); ?><br>
                                <span id="<?php echo AI::$typeBasic; ?>progress" class="badge" style="display:none;">...</span>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#pTranscription">
                                <i class="fas fa-microphone-alt"></i>
                                <?php echo __("Transcription"); ?><br>
                                <span id="<?php echo AI::$typeTranscription; ?>progress" class="badge" style="display:none;">...</span>
                            </a>
                        </li>
                        <li><a data-toggle="tab" href="#pUsage"><i class="fas fa-receipt"></i> <?php echo __("Usage"); ?></a></li>
                        <li><a data-toggle="tab" href="#pPP"><i class="fas fa-file-contract"></i> <?php echo __("Prices, Privacy Policy"); ?></a></li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div id="pbasic" class="tab-pane fade in active">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/basic.php';
                            ?>
                        </div>
                        <div id="pTranscription" class="tab-pane fade ">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/transcription.php';
                            ?>
                        </div>
                        <div id="pUsage" class="tab-pane fade ">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/usage.php';
                            ?>
                        </div>
                        <div id="pPP" class="tab-pane fade ">
                            <iframe src="https://youphp.tube/marketplace/AI/privacyPolicy.php"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    var modalContinueAISuggestions = getPleaseWait();

    function simpleTextHash(text) {
        if (typeof text === 'string' || text instanceof String) {
            text = text.toLowerCase();
            var hash = 0,
                i, chr;
            for (i = 0; i < text.length; i++) {
                chr = text.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            return 'c' + Math.abs(hash); // Ensure it's a positive number and add a prefix
        } else {
            return 'error';
        }
    }

    function processAIResponse(selector, data, columnOrder, columnHeaders, columnCalbackFunctions) {
        var tableHead = $(selector + ' thead');
        var tableBody = $(selector + ' tbody');
        tableHead.empty(); // Clear existing headers
        tableBody.empty(); // Clear existing rows

        // Create and append table headers
        var headerRow = $('<tr></tr>');
        columnOrder.forEach(function(column) {
            headerRow.append('<th>' + columnHeaders[column] + '</th>');
        });
        tableHead.append(headerRow);

        if (!empty(data.response)) {
            if (typeof data.response[0].ai_responses_id !== undefined) {
                ai_metatags_responses_id = 0;
            }
        }

        // Create and append table rows
        data.response.forEach(function(item) {
            var ai_metatags_responses_id = 0;
            var ai_transcribe_responses_id = 0;
            if (typeof item.videoTitles !== 'undefined') {
                ai_metatags_responses_id = item.ai_responses_id;
            } else if (typeof item.vtt !== 'undefined') {
                ai_transcribe_responses_id = item.ai_responses_id;
            } else {

            }
            var row = $('<tr></tr>');
            columnOrder.forEach(function(column) {
                var addButton = columnCalbackFunctions.includes(column);
                if (item[column] != null && typeof item[column] == 'object') {
                    var combinedCell = $('<td></td>');
                    item[column].forEach(function(content, index) {
                        var contentDiv = $('<div>' + content + '</div>');
                        if (addButton) {
                            contentDiv.prepend(addSaveButton(item.id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column));
                            contentDiv.addClass('aiItem');
                            var className = simpleTextHash(content);
                            contentDiv.addClass(className);
                        }
                        combinedCell.append(contentDiv);
                    });
                    row.append(combinedCell);
                } else {
                    var div = $('<div>' + (item[column] ? item[column] : '') + '</div>');
                    if (addButton) {
                        div.prepend(addSaveButton(item.id, ai_metatags_responses_id, ai_transcribe_responses_id, 0, column));
                        div.addClass('aiItem');
                        var className = simpleTextHash(item[column]);
                        div.addClass(className);
                    }
                    var cell = $('<td></td>');
                    cell.append(div);
                    row.append(cell);
                }
            });
            tableBody.append(row);
        });

        function addSaveButton(id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column) {
            var saveBtn = $('<button class="btn btn-primary btn-xs btn-block save-btn"><i class="fa-solid fa-floppy-disk"></i></button>');
            saveBtn.on('click', function() {
                confirmUpdate(id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column);
                console.log('addSaveButton', id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column);
            });
            return saveBtn;
        }
    }

    async function createAISuggestions(type) {
        return new Promise((resolve, reject) => {
            modalContinueAISuggestions.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/AI/async.json.php',
                data: {
                    videos_id: <?php echo $videos_id; ?>,
                    type: type
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
                    getProgress(type, '');
                }
            });
        });
    }

    function loadTitleDescription() {
        modal.showPleaseWait();
        $('.aiItem').slideDown();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/tabs/video.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    Object.keys(response).forEach(function(key) {
                        var selector = '#' + key;
                        var text = response[key];
                        if ($(selector).length > 0) {
                            $(selector).html(text);
                        }
                        $('.' + simpleTextHash(text)).slideUp();
                    });
                }
                modal.hidePleaseWait();
            }
        });
    }

    function confirmUpdate(id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/save.json.php',
            data: {
                id: id,
                ai_metatags_responses_id: ai_metatags_responses_id,
                ai_transcribe_responses_id: ai_transcribe_responses_id,
                index: index,
                label: column,
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                    modal.hidePleaseWait();
                } else {
                    avideoToast(__("Your register has been saved!"));
                    if (ai_metatags_responses_id) {
                        loadTitleDescription();
                    } else {
                        loadAITranscriptions();
                    }
                }
            }
        });
    }

    function getProgress(type, lang) {
        // Clear existing timeout for this language, if it exists
        if (progressTimeouts[lang]) {
            clearTimeout(progressTimeouts[lang]);
        }
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/progress.json.php',
            data: {
                type: type,
                lang: lang,
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    //avideoAlertError(response.msg);
                } else {
                    console.log(response);
                    var selector = '#' + response.prefix + 'progress';
                    if(response.hide){
                        $(selector).hide();
                    }else{
                        $(selector).show();
                    }
                    $(selector).html(response.msg);

                    if (response.timeout) {
                        // Set a new timeout for this language
                        progressTimeouts[lang] = setTimeout(function() {
                            getProgress(type, lang);
                        }, response.timeout);
                    }
                }
            }
        });
    }

    $(document).ready(function() {

    });
</script>
<?php
$_page->print();
?>