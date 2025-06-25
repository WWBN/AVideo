<?php
require_once '../../videos/configuration.php';

$videos_id = getVideos_id();

// each 60 of video will take about 5 minutes to complete
if (!User::isLogged()) {
    forbiddenPage('You must login first');
}
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

$objWallet = AVideoPlugin::getObjectData('YPTWallet');

$objAI = AVideoPlugin::getObjectDataIfEnabled('AI');

if (empty($objAI)) {
    forbiddenPage('AI plugin is disabled');
}

if(!AI::canUseAI()){
    forbiddenPage('You cannot use AI');
}

//$rows = Ai_responses::getTranscriptions($videos_id);var_dump($rows);exit;
//var_dump(Ai_responses::hasTranscriptions($videos_id));exit;

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$priceForBasic = $objAI->priceForBasic;
$priceForTranscription = $objAI->priceForTranscription;
$priceForTranslation = $objAI->priceForTranslation;
$priceForShorts = $objAI->priceForShorts;
$priceForDubbing = $objAI->priceForDubbing;
$priceForAll = $priceForTranscription + $priceForBasic + $priceForShorts;

$priceForBasicText = YPTWallet::formatCurrency($priceForBasic);
$priceForTranscriptionText = YPTWallet::formatCurrency($priceForTranscription);
$priceForTranslationText = YPTWallet::formatCurrency($priceForTranslation);
$priceForShortsText = YPTWallet::formatCurrency($priceForShorts);
$priceForAllText = YPTWallet::formatCurrency($priceForAll);
$priceForDubbingText = YPTWallet::formatCurrency($priceForDubbing);
/*
if (User::isAdmin()) {
    $_1hour = 60 * 60;
    $pricesJson = url_get_contents_with_cache('https://youphp.tube/marketplace/AI/prices.json.php', $_1hour * 6);
    //$pricesJ = json_decode($pricesJson);
    $pricesJ = ($pricesJson);
    $adminPriceForBasic = $pricesJ->priceForBasic;
    $adminPriceForTranscription = $pricesJ->priceForTranscription;
    $adminPriceForTranslation = $pricesJ->priceForTranslation;
    $adminPriceForShorts = $pricesJ->priceForShorts;
    $adminPriceForAll = $adminPriceForTranscription + $adminPriceForBasic + $adminPriceForShorts;

    $adminPriceForBasicText = YPTWallet::formatCurrency($adminPriceForBasic);
    $adminPriceForTranscriptionText = YPTWallet::formatCurrency($adminPriceForTranscription);
    $adminPriceForTranslationText = YPTWallet::formatCurrency($adminPriceForTranslation);
    $adminPriceForShortsText = YPTWallet::formatCurrency($adminPriceForShorts);
    $adminPriceForAllText = YPTWallet::formatCurrency($adminPriceForAll);
}
*/



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

    .progress-bar-animated {
        transition: width 30s ease;
    }

    .progressAIText {
        float: right;
    }

    .progressAITitle {
        float: left;
        padding: 5px 10px;
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
                    <a href="<?php echo Video::getLinkToVideo($videos_id); ?>" target="_blank">
                        <h1 id="currentTitle">
                            <?php echo $video->getTitle(); ?>
                        </h1>
                    </a>
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
                <div class="panel-heading clearfix">
                    <button class="btn btn-primary pull-right" onclick="createAllAI();" data-toggle="tooltip" data-placement="bottom" title="Transcription + basic + Shorts">
                        <i class="fa-solid fa-check-double"></i>
                        <?php echo __("Create all"); ?>
                        <?php
                        if(!empty($priceForAll)){
                            echo "<br><span class=\"label label-primary\">{$priceForAllText}</span>";
                        }
                        ?>
                    </button>
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a data-toggle="tab" href="#pTranscription" id="pTranscriptionLink">
                                <i class="fas fa-microphone-alt"></i>
                                <?php echo __("Transcription"); ?>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#pbasic">
                                <i class="fa-solid fa-lightbulb"></i>
                                <?php echo __("Basic"); ?>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#pimage">
                                <i class="fa-solid fa-image"></i>
                                <?php echo __("Image"); ?>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#pShorts">
                                <i class="fa-solid fa-scissors"></i>
                                <?php echo __("Shorts"); ?>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#pDubbing">
                                <i class="fa-solid fa-headphones"></i>
                                <?php echo __("Dubbing"); ?>
                            </a>
                        </li>
                        <?php
                        if (User::isAdmin()) {
                        ?>
                            <li>
                                <a data-toggle="tab" href="#pUsage">
                                    <i class="fas fa-receipt"></i>
                                    <?php echo __("Usage and details"); ?>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#pPP">
                                    <i class="fas fa-file-contract"></i>
                                    <?php echo __("Prices, Privacy Policy"); ?>
                                </a>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div id="pTranscription" class="tab-pane fade in active ">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/transcription.php';
                            ?>
                        </div>
                        <div id="pbasic" class="tab-pane fade">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/basic.php';
                            ?>
                        </div>
                        <div id="pimage" class="tab-pane fade">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/image.php';
                            ?>
                        </div>
                        <div id="pShorts" class="tab-pane fade">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/shorts.php';
                            ?>
                        </div>
                        <div id="pDubbing" class="tab-pane fade">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/tabs/dubbing.php';
                            ?>
                        </div>
                        <?php
                        if (User::isAdmin()) {
                        ?>
                            <div id="pUsage" class="tab-pane fade ">
                                <?php
                                include $global['systemRootPath'] . 'plugin/AI/tabs/usage.php';
                                ?>
                            </div>
                            <div id="pPP" class="tab-pane fade ">
                                <iframe src="https://youphp.tube/marketplace/AI/privacyPolicy.php"></iframe>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var modalContinueAISuggestions = getPleaseWait();

    function createAllAI() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/createAll.json..php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            complete: function(resp) {
                avideoResponse(resp);
                modal.hidePleaseWait();
            }
        });
    }

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

    function processAIResponse(selector, data, columnOrder, columnHeaders, columnCallbackFunctions) {
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

        // Create and append table rows
        data.response.forEach(function(item) {
            var ai_metatags_responses_id = 0;
            var ai_transcribe_responses_id = 0;
            if (!empty(item.ai_metatags_responses_id)) {
                ai_metatags_responses_id = item.ai_metatags_responses_id;
            }
            if (!empty(item.ai_transcribe_responses_id)) {
                ai_transcribe_responses_id = item.ai_transcribe_responses_id;
            }
            var row = $('<tr></tr>');
            columnOrder.forEach(function(column) {
                var addButton = columnCallbackFunctions.includes(column);
                //console.log(addButton, column, columnCallbackFunctions);
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
            var saveBtn = $('<button class="btn btn-primary btn-xs btn-block save-btn"><i class="fa-solid fa-floppy-disk"></i> ' + __('Save') + '</button>');
            saveBtn.on('click', function() {
                confirmUpdate(id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column);
                console.log('addSaveButton', id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column);
            });

            if (empty(ai_transcribe_responses_id)) {
                return saveBtn;
            }

            var btnGroup = $('<div class="btn-group btn-group-justified" role="group"></div>');
            var deleteBtn = $('<button class="btn btn-danger btn-xs btn-block save-btn"><i class="fa-solid fa-trash"></i> ' + __('Delete') + '</button>');
            deleteBtn.on('click', function() {
                deleteAI(id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column);
                console.log('deleteAI', id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column);
            });
            btnGroup.append(saveBtn);
            btnGroup.append(deleteBtn);
            return btnGroup;
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

    var modalloadTitleDescription = getPleaseWait();

    function loadTitleDescription() {
        modalloadTitleDescription.showPleaseWait();
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
                modalloadTitleDescription.hidePleaseWait();
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
            complete: function(resp) {
                response = resp.responseJSON
                console.log(response);
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast(__("Your register has been saved!"));
                    if (ai_metatags_responses_id) {
                        loadTitleDescription();
                    } else {
                        loadAITranscriptions();
                    }
                }
                modal.hidePleaseWait();
            }
        });
    }


    function deleteAI(id, ai_metatags_responses_id, ai_transcribe_responses_id, index, column) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/delete.json.php',
            data: {
                id: id,
                ai_metatags_responses_id: ai_metatags_responses_id,
                ai_transcribe_responses_id: ai_transcribe_responses_id,
                index: index,
                label: column,
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            complete: function(resp) {
                response = resp.responseJSON
                console.log(response);
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast(__("Your register has been saved!"));
                    if (ai_metatags_responses_id) {
                        loadTitleDescription();
                    } else {
                        loadAITranscriptions();
                    }
                }
                modal.hidePleaseWait();
            }
        });
    }
    var getAIProgressTimeout;

    function getAIProgress() {
        clearTimeout(getAIProgressTimeout);
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/progress.json.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    //avideoAlertError(response.msg);
                } else {
                    console.log(response);
                    if (response.services) {
                        // Iterate through each service category (e.g., 'cron', 'processing')
                        $.each(response.services, function(index, serviceCategory) {
                            $.each(serviceCategory, function(index, service) {
                                if (service.classname) {
                                    // Find elements with this classname and update their content
                                    var selector = '.' + service.classname;
                                    $(selector).addClass('updated');
                                    $(selector).slideDown();
                                    updateProgress(selector, service.progress)
                                }
                            });
                        });
                    }
                    $('.progressAI').each(function() {
                        if (!$(this).hasClass('updated')) {
                            $(this).slideUp(); // Or use hide(), depending on the effect you want
                        } else {
                            // Reset the updated flag for the next operation
                            $(this).removeClass('updated');
                        }
                    });
                    if (response.timeout) {
                        // Set a new timeout for this language
                        getAIProgressTimeout = setTimeout(function() {
                            getAIProgress();
                        }, response.timeout);
                    }

                }
            }
        });
    }

    function updateProgress(selector, newVal) {
        //console.log('updateProgress("'+selector+'", "'+newVal+'", "'+message+'");', selector, newVal, message);
        $(selector + ' .progress-bar').css('width', newVal + '%');
        $(selector + ' .progress-bar').attr('aria-valuenow', newVal);
    }

    $(document).ready(function() {
        getAIProgress();
    });
</script>
<?php
$_page->print();
?>
