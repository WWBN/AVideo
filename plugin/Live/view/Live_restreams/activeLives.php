<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__ . '/../../../../videos/configuration.php';
}

if (!Live::canRestream()) {
    forbiddenPage('You cannot restream');
}
$_page = new Page(array('Active Lives'));
?>
<style>
    #livesRestreamList .livesRestreamStatus.inactive .hideWhenInactive,
    #livesRestreamList .livesRestreamStatus.active .hideWhenActive,
    #livesRestreamList .livesRestreamStatus.loading .hideWhenLoading,
    #livesRestreamList .livesRestreamStatus.hasLog .hideWhenHasLog,
    #livesRestreamList .livesRestreamStatus.hasNoLog .hideWhenHasNoLog {
        display: none;
    }

    #livesRestreamList .livesRestreamStatus.active .showWhenActive {
        display: inline-flex;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">

        </div>
        <div class="panel-body">
            <table class="table table-hover" id="livesRestreamList">
                <thead>
                    <tr>
                        <th colspan="4"></th>
                        <th colspan="2" class="text-center">Viewers</th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Live Status</th>
                        <th>Key</th>
                        <th>Started</th>
                        <th><abbr title="<?php echo __('Max at same time'); ?>">Max</abbr></th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-info " onclick="getActiveLives();">
                <i class="fas fa-sync faa-spin"></i>
                <?php echo __('Reload'); ?>
            </button>
        </div>
    </div>
</div>
<script>
    var activeLiveTemplate = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Live/view/getActiveLives.template.html')); ?>;
    var activeLiveRestreamTemplate = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Live/view/getActiveLivesRestreams.template.html')); ?>;

    $(document).ready(function() {
        getActiveLives();
    });

    function getAction(action, live_transmitions_history_id, live_restreams_id) {
        var url = webSiteRootURL + 'plugin/Live/view/Live_restreams/getAction.json.php';
        url = addQueryStringParameter(url, 'action', action);
        url = addQueryStringParameter(url, 'live_transmitions_history_id', live_transmitions_history_id);
        url = addQueryStringParameter(url, 'live_restreams_id', live_restreams_id);

        modal.showPleaseWait();
        $.ajax({
            url: url,
            success: function(response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(response.msg);
                    if (typeof response.eval !== 'undefined') {
                        eval(response.eval);
                    }
                }
                getActiveLives();
            }
        });
    }

    function getActiveLives() {
        var url = webSiteRootURL + 'plugin/Live/view/getActiveLives.json.php';
        //modal.showPleaseWait();
        $.ajax({
            url: url,
            success: function(response) {
                console.log('getActiveLives', response);
                //modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    activeLivesToTable(response.lives);
                    loadIfRestreamIsActive();
                }
            }
        });
    }

    function activeLivesToTable(lives) {
        var liveTemplate = activeLiveTemplate;
        var restreamTemplate = activeLiveRestreamTemplate;
        $('#livesRestreamList tbody').empty();
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
                console.log('activeLivesToTable live', itemsArray);
                restream += arrayToTemplate(itemsArray, restreamTemplate);
            }
            //console.log('activeLivesToTable restreams', restream);
            live['restream'] = restream;
            live['class'] = '';
            liveHTML = arrayToTemplate(live, liveTemplate);
            $('#livesRestreamList tbody').append(liveHTML);
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
</script>
<?php
$_page->print();
?>