<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!Live::canRestream()) {
    return false;
}
?>
<style>
    #livesRestreamList .livesRestreamStatus.inactive .hideWhenInactive,
    #livesRestreamList .livesRestreamStatus.active .hideWhenActive,
    #livesRestreamList .livesRestreamStatus.loading .hideWhenLoading{
        display: none;
    }

    #livesRestreamList .livesRestreamStatus.active .showWhenActive{
        display: inline-flex;
    }
</style>
<table class="table table-hover" id="livesRestreamList">
    <thead>
        <tr>
            <th colspan="4"></th>
            <th colspan="2" class="text-center">Viewers</th>
        </tr>
        <tr>
            <th>ID</th>
            <th>Title</th>
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
<script>

    var activeLiveTemplate = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Live/view/getActiveLives.template.html')); ?>;
    var activeLiveRestreamTemplate = <?php echo json_encode(file_get_contents($global['systemRootPath'] . 'plugin/Live/view/getActiveLivesRestreams.template.html')); ?>;

    $(document).ready(function () {
        getActiveLives();
    });

    function getAction(action, live_restreams_logs_id) {
        var url = webSiteRootURL + 'plugin/Live/view/Live_restreams/getAction.json.php';
        url = addQueryStringParameter(url, 'action', action);
        url = addQueryStringParameter(url, 'live_restreams_logs_id', live_restreams_logs_id);

        modal.showPleaseWait();
        $.ajax({
            url: url,
            success: function (response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(response.msg);
                    if (typeof response.eval !== 'undefined') {
                        eval(response.eval);
                    }
                }
            }
        });
    }


    function getActiveLives() {
        var url = webSiteRootURL + 'plugin/Live/view/getActiveLives.json.php';
        //modal.showPleaseWait();
        $.ajax({
            url: url,
            success: function (response) {
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
            for (var j in live.restream_log) {
                var itemsArray = live.restream_log[j];
                if (typeof itemsArray == 'function') {
                    continue;
                }
                //console.log('activeLivesToTable live', itemsArray);   
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
        $(".livesRestreamStatus").each(function (index) {
            var live_restreams_logs_id = $(this).attr('live_restreams_logs_id');
            checkIfRestreamIsActive(live_restreams_logs_id);
        });
    }
    
    var checkIfRestreamIsActiveTimeout = [];
    function checkIfRestreamIsActive(live_restreams_logs_id) {
        clearTimeout(checkIfRestreamIsActiveTimeout[live_restreams_logs_id]);
        setRestreamLogLoading(live_restreams_logs_id);
        var url = webSiteRootURL + 'plugin/Live/view/getRestream.json.php';
        url = addQueryStringParameter(url, 'live_restreams_logs_id', live_restreams_logs_id);
        $.ajax({
            url: url,
            success: function (response) {
                //console.log('checkIfRestreamIsActive', response);
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    if(response.log.error){
                        avideoAlertError('Log error');
                    }else{
                        if(response.log.isActive){
                            setRestreamLogActive(live_restreams_logs_id);
                        }else{
                            setRestreamLogInactive(live_restreams_logs_id);
                        }
                    }
                }
                checkIfRestreamIsActiveTimeout[live_restreams_logs_id] = setTimeout(function(){checkIfRestreamIsActive(live_restreams_logs_id);},10000);
            }
        });
        
        function setRestreamLogLoading(live_restreams_logs_id){
            $(".livesRestreamStatus_"+live_restreams_logs_id).removeClass('active');
            $(".livesRestreamStatus_"+live_restreams_logs_id).removeClass('inactive');
            $(".livesRestreamStatus_"+live_restreams_logs_id).addClass('loading');
        }
        function setRestreamLogActive(live_restreams_logs_id){
            $(".livesRestreamStatus_"+live_restreams_logs_id).addClass('active');
            $(".livesRestreamStatus_"+live_restreams_logs_id).removeClass('inactive');
            $(".livesRestreamStatus_"+live_restreams_logs_id).removeClass('loading');
        }
        function setRestreamLogInactive(live_restreams_logs_id){
            $(".livesRestreamStatus_"+live_restreams_logs_id).removeClass('active');
            $(".livesRestreamStatus_"+live_restreams_logs_id).addClass('inactive');
            $(".livesRestreamStatus_"+live_restreams_logs_id).removeClass('loading');
        }
    }
</script>