<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__ . '/../../../../videos/configuration.php';
}

if (!Live::canRestream()) {
    forbiddenPage('You cannot restream');
}
$_page = new Page(array('Active Lives'));
$_page->setExtraStyles(array('plugin/Live/view/Live_restreams/activeLives.css'));
?>
<div class="container-fluid">
    <div class="panel panel-default restream-monitor">
        <div class="panel-heading restream-toolbar">
            <h1 class="panel-title"><i class="fas fa-broadcast-tower" aria-hidden="true"></i> <?php echo __('Active Lives'); ?></h1>
            <div class="restream-actions">
                <?php echo getTourHelpButton('plugin/Live/view/Live_restreams/activeLives.help.json', 'btn btn-default btn-sm'); ?>
                <button type="button" class="btn btn-default btn-sm" id="reloadActiveLives" onclick="getActiveLives();">
                    <i class="fas fa-sync" aria-hidden="true"></i> <?php echo __('Reload'); ?>
                </button>
            </div>
        </div>
        <div class="panel-body">
            <div class="restream-list-status text-muted" id="activeLivesStatus" role="status"><?php echo __('Loading...'); ?></div>
            <div class="restream-live-list" id="livesRestreamList"></div>
        </div>
    </div>
</div>
<script>
    var activeLiveTemplate = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Live/view/getActiveLives.template.html')); ?>;
    var activeLiveRestreamTemplate = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Live/view/getActiveLivesRestreams.template.html')); ?>;
    var activeLivesLabels = <?php echo json_encode(array(
        'label_started' => __('Started'),
        'label_key' => __('Key'),
        'label_max' => __('Max at same time'),
        'label_total' => __('Total views'),
        'label_restream' => __('Restream'),
        'label_start' => __('Start'),
        'label_stop' => __('Stop'),
        'label_loading' => __('Loading...'),
        'label_log' => __('Log'),
        'label_open' => __('Open'),
        'label_no_destinations' => __('No restream destinations configured')
    )); ?>;

    var getActiveLivesRefreshInterval = null;
    var activeLivesLoading = false;

    $(document).ready(function() {
        getActiveLives();
        // Periodically refresh the whole list so the Start/Stop buttons and the
        // "checkIfRestreamIsActive" polling never keep using a stale/finished
        // live_transmitions_history_id if the page is left open across live sessions.
        clearInterval(getActiveLivesRefreshInterval);
        getActiveLivesRefreshInterval = setInterval(getActiveLives, 60000);
    });

    function getAction(action, live_transmitions_history_id, live_restreams_id) {
        var url = webSiteRootURL + 'plugin/Live/view/Live_restreams/getAction.json.php';
        url = addQueryStringParameter(url, 'action', action);
        url = addQueryStringParameter(url, 'live_transmitions_history_id', live_transmitions_history_id);
        url = addQueryStringParameter(url, 'live_restreams_id', live_restreams_id);

        console.log('getAction()', action, live_transmitions_history_id, live_restreams_id, url);
        modal.showPleaseWait();
        $.ajax({
            url: url,
            success: function(response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(response.msg);
                }
                getActiveLives();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                modal.hidePleaseWait();
                console.error('getAction() ajax failed', textStatus, errorThrown, jqXHR.status, jqXHR.responseText);
                avideoAlertError('Error: ' + textStatus + ' ' + (jqXHR.status || '') + ' ' + (errorThrown || ''));
            }
        });
    }

    function getActiveLives() {
        if (activeLivesLoading) {
            return;
        }
        activeLivesLoading = true;
        $('#reloadActiveLives').prop('disabled', true);
        $('#activeLivesStatus').removeClass('hidden').text(<?php echo json_encode(__('Loading...')); ?>);
        var url = webSiteRootURL + 'plugin/Live/view/getActiveLives.json.php';
        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if (!response || response.error || !Array.isArray(response.lives)) {
                    $('#activeLivesStatus').text(<?php echo json_encode(__('Could not load live streams. Please reload.')); ?>);
                } else {
                    activeLivesToTable(response.lives);
                    loadIfRestreamIsActive();
                }
            },
            error: function() {
                $('#activeLivesStatus').text(<?php echo json_encode(__('Could not load live streams. Please reload.')); ?>);
            },
            complete: function() {
                activeLivesLoading = false;
                $('#reloadActiveLives').prop('disabled', false);
            }
        });
    }

    function activeLivesToTable(lives) {
        var liveTemplate = arrayToTemplate(activeLivesLabels, activeLiveTemplate);
        var restreamTemplate = arrayToTemplate(activeLivesLabels, activeLiveRestreamTemplate);
        $('#livesRestreamList').empty();
        $('#activeLivesStatus').toggleClass('hidden', lives.length > 0)
            .text(<?php echo json_encode(__('No live streams to show yet.')); ?>);
        //console.log('activeLivesToTable', lives);
        for (var i in lives) {
            var live = lives[i];
            if (typeof live == 'function') {
                continue;
            }
            //console.log('activeLivesToTable restream_log', live.restream_log);
            var restream = '';
            for (var j in live.restream) {
                var itemsArray = live.restream[j];
                if (typeof itemsArray == 'function') {
                    continue;
                }
                itemsArray.live_transmitions_history_id = live.id;
                itemsArray.live_restream_id = itemsArray.live_transmitions_history_id + '_' + itemsArray.id;
                itemsArray.openLinkClass = empty(itemsArray.live_url) ? 'hidden' : '';
                console.log('activeLivesToTable live', itemsArray);
                restream += arrayToTemplate(itemsArray, restreamTemplate);
            }
            //console.log('activeLivesToTable restreams', restream);
            live['restream'] = restream;
            live['noDestinationsClass'] = restream ? 'hidden' : '';
            live['statusClass'] = empty(live.finished) ? 'label-success' : 'label-default';
            live['class'] = '';
            var liveHTML = arrayToTemplate(live, liveTemplate);
            $('#livesRestreamList').append(liveHTML);
        }
    }

    function loadIfRestreamIsActive() {
        $(".livesRestreamStatus").each(function(index) {
            var restreams_id = $(this).attr('restreams_id');
            var live_transmitions_history_id = $(this).attr('live_transmitions_history_id');
            checkIfRestreamIsActive(live_transmitions_history_id, restreams_id);
        });
    }

    var checkIfRestreamIsActiveTimeout = [];

    function checkIfRestreamIsActive(live_transmitions_history_id, restreams_id) {
        var live_restream_id = live_transmitions_history_id + '_' + restreams_id;
        clearTimeout(checkIfRestreamIsActiveTimeout[live_restream_id]);
        setRestreamLogLoading(live_transmitions_history_id, restreams_id);
        var url = webSiteRootURL + 'plugin/Live/view/getRestream.json.php';
        url = addQueryStringParameter(url, 'live_transmitions_history_id', live_transmitions_history_id);
        url = addQueryStringParameter(url, 'restreams_id', restreams_id);
        $.ajax({
            url: url,
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    if (response.log.error) {
                        avideoAlertError('Log error');
                    } else {
                        if (empty(response.log)) {
                            setRestreamHasNoLog(live_transmitions_history_id, restreams_id);
                        } else if (response.log.isActive) {
                            setRestreamLogActive(live_transmitions_history_id, restreams_id);
                        } else {
                            console.log('checkIfRestreamIsActive', live_transmitions_history_id, restreams_id, response.log, empty(response.log));
                            setRestreamLogInactive(live_transmitions_history_id, restreams_id);
                        }
                    }
                }
                checkIfRestreamIsActiveTimeout[live_restream_id] = setTimeout(function() {
                    checkIfRestreamIsActive(live_transmitions_history_id, restreams_id);
                }, 120000);
            }
        });

        function setRestreamHasNoLog(live_transmitions_history_id, restreams_id) {
            removeAllClassesAndAdd(live_transmitions_history_id, restreams_id, 'hasNoLog');
        }

        function setRestreamHasLog(live_transmitions_history_id, restreams_id) {
            removeAllClassesAndAdd(live_transmitions_history_id, restreams_id, 'hasLog');
        }

        function setRestreamLogLoading(live_transmitions_history_id, restreams_id) {
            removeAllClassesAndAdd(live_transmitions_history_id, restreams_id, 'loading');
        }

        function setRestreamLogActive(live_transmitions_history_id, restreams_id) {
            removeAllClassesAndAdd(live_transmitions_history_id, restreams_id, 'active');
        }

        function setRestreamLogInactive(live_transmitions_history_id, restreams_id) {
            removeAllClassesAndAdd(live_transmitions_history_id, restreams_id, 'inactive');
        }

        function removeAllClassesAndAdd(live_transmitions_history_id, restreams_id, addClass) {
            var live_restream_id = live_transmitions_history_id + '_' + restreams_id;
            $(".livesRestreamStatus_" + live_restream_id).removeClass('active');
            $(".livesRestreamStatus_" + live_restream_id).removeClass('inactive');
            $(".livesRestreamStatus_" + live_restream_id).removeClass('loading');
            $(".livesRestreamStatus_" + live_restream_id).removeClass('hasNoLog');
            $(".livesRestreamStatus_" + live_restream_id).removeClass('hasLog');
            $(".livesRestreamStatus_" + live_restream_id).addClass(addClass);
        }
    }

    function showRestreamLog(live_transmitions_history_id, live_restreams_id) {
        var url = webSiteRootURL + 'plugin/Live/view/Live_restreams/logViewer.php';
        url = addQueryStringParameter(url, 'live_transmitions_history_id', live_transmitions_history_id);
        url = addQueryStringParameter(url, 'live_restreams_id', live_restreams_id);
        avideoModalIframe(url);
    }
</script>
<?php
$_page->print();
?>
