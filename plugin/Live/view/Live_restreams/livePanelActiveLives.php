<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!Live::canRestream()) {
    return false;
}

$lives = LiveTransmitionHistory::getAllActiveFromUser();
$restreamers = Live_restreams::getAllFromUser(User::getId());
//var_dump($lives);
?>
<style>
    #livesRestreamList .livesRestreamStatus .showWhenActive,
    #livesRestreamList .livesRestreamStatus.active .hideWhenActive{
        display: none;
    }

    #livesRestreamList .livesRestreamStatus.active .showWhenActive{
        display: inline-block;
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
            <th data-toggle="tooltip" title="<?php echo __('Max at same time'); ?>">Max</th>
            <th>Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($lives as $key => $value) {
            //var_dump($value);
            ?>
            <tr>
                <td><?php echo $value['id'] ?></td>
                <td><?php echo $value['title'] ?></td>
                <td><?php echo $value['key'] ?></td>
                <td><?php echo convertFromDefaultTimezoneTimeToMyTimezone($value['created']); ?></td>
                <td><?php echo $value['max_viewers_sametime'] ?></td>
                <td><?php echo $value['total_viewers'] ?></td>
                <td>
                    <?php
                    foreach ($restreamers as $restream) {
                        $log = Live_restreams_logs::getLatest($value['id'], $restream['id']);
                        $restreamsClass = '';
                        $live_restreams_logs_id = 0;
                        if (!empty($log)) {
                            $live_restreams_logs_id = $log['id'];
                            $restreamsClass = 'active';
                        }
                        ?>
                        <div class="livesRestreamStatus <?php echo $restreamsClass; ?>">
                            <div class="btn-group showWhenActive">
                                <button class="btn btn-primary" onclick="getAction('log', <?php echo $live_restreams_logs_id ?>);">
                                    <i class="fas fa-sync faa-spin animated"></i>
                                </button>
                                <button class="btn btn-danger" onclick="getAction('stop', <?php echo $live_restreams_logs_id; ?>);">
                                    <i class="fas fa-stop"></i>
                                </button>
                            </div>
                            <button class="btn btn-success hideWhenActive" onclick="getAction('start', <?php echo $live_restreams_logs_id; ?>);">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<script>
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
</script>