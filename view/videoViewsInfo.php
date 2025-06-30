<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!empty($_REQUEST['hash'])) {
    $string = decryptString($_REQUEST['hash']);
    $obj = json_decode($string);
    $_videos_id = intval($obj->videos_id);
} else {
    $_videos_id = intval(@$_REQUEST['videos_id']);
    if (!Video::canEdit($_videos_id)) {
        forbiddenPage("You cannot see this info");
    }
}
if (empty($_videos_id)) {
    forbiddenPage("Videos ID is required");
}
$v = new Video('', '', $_videos_id);
$_page = new Page(array('Videos View info'));
$_page->loadBasicCSSAndJS();
//var_dump($total);exit;
?>
<div class="container-fluid">
    <br>
    <div class="panel panel-default">
        <div class="panel-heading clearfix" id="viewInfoTitle">
            <div class="videoImagesAndInfo">
                <?php
                echo Video::getVideosListItem($_videos_id);
                //echo Video::getVideoImagewithHoverAnimationFromVideosId($_videos_id);
                //echo $v->getTitle();
                $obj = new stdClass();
                $obj->videos_id = $_videos_id;
                $hash = encryptString($obj);
                ?>
            </div>
            <div class="btn-group" role="group" aria-label="video views info Buttons" id="buttonsGroup">
                <button type="button" class="btn btn-default" onclick="copyToClipboard(webSiteRootURL + 'view/videoViewsInfo.php?hash=<?php echo $hash; ?>');"><i class="fas fa-copy"></i> <?php echo __('Share link'); ?></button>
                <button onclick="getVideoViewsCSV();" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> <?php echo __('CSV File'); ?>
                </button>
            </div>
        </div>
        <div class="panel-body">
            <div class="form-inline">
                <div class="form-group">
                    <label for="month">Month:</label>
                    <select class="form-control" id="month">
                        <?php
                        $currMonth = date('m');
                        echo "<option value='0'>All</option>";
                        for ($i = 1; $i <= 12; $i++) {
                            echo "<option value='{$i}'>{$i}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year:</label>
                    <select class="form-control" id="year">
                        <?php
                        $currYear = date('Y');
                        echo "<option value='0'>All</option>";
                        for ($i = $currYear; $i > $currYear - 20; $i--) {
                            echo "<option value='{$i}'>{$i}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-default" onclick="$('#month').val(<?php echo $currMonth; ?>);$('#year').val(<?php echo $currYear; ?>);$('#year').trigger('change');"><?php echo __('Current Month'); ?></button>
                <button class="btn btn-default" onclick="$('#month').val(0);$('#year').val(0);$('#year').trigger('change');"><?php echo __('All'); ?></button>
            </div>
            <hr>
            <h3>
                <?php
                echo number_format_short($v->getViews_count());
                ?>
                Views and watched
                <?php
                echo seconds2human($v->getTotal_seconds_watching());
                ?>
            </h3>
            <table class="table table-hover" id="VideoViewsInfo">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>When</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>User</th>
                        <th>When</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Device</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    function getVideoViewsAjaxURL() {
        var url = webSiteRootURL + "view/videoViewsInfo.json.php?videos_id=<?php echo $_videos_id; ?>&hash=<?php echo @$_REQUEST['hash']; ?>";
        url = addGetParam(url, 'created_year', $('#year').val());
        url = addGetParam(url, 'created_month', $('#month').val());
        return url;
    }

    function getVideoViewsCSV() {
        var url = webSiteRootURL + "view/videoViewsInfo.csv.php?videos_id=<?php echo $_videos_id; ?>&hash=<?php echo @$_REQUEST['hash']; ?>";
        url = addGetParam(url, 'created_year', $('#year').val());
        url = addGetParam(url, 'created_month', $('#month').val());
        document.location = url;
    }
    var VideoViewsInfo;
    $(document).ready(function() {
        VideoViewsInfo = $('#VideoViewsInfo').DataTable({
            "order": [
                [1, "desc"]
            ],
            serverSide: true,
            "ajax": getVideoViewsAjaxURL(),
            "columns": [{
                    data: 'users_id',
                    render: function(data, type, row) {
                        return row.users
                    }
                },
                {
                    data: 'when',
                    render: function(data, type, row) {
                        return row.when_human
                    }
                },
                {
                    data: 'seconds_watching_video',
                    render: function(data, type, row) {
                        return row.seconds_watching_video_human
                    }
                },
                {
                    orderable: false,
                    render: function(data, type, row) {
                        return row.location_name
                    }
                },
                {
                    data: 'app',
                    render: function(data, type, row) {
                        return row.app
                    }
                }
            ],
            select: true,
        });
        $('#year, #month').change(function() {
            VideoViewsInfo.ajax.url(getVideoViewsAjaxURL()).load();
        });
    });
</script>
<?php
$_page->print();
?>
