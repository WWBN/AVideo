<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($obj)) {
    die("Plugin disabled");
}

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$userCredentials = User::loginFromRequestToGet();
?>

<div class="row">
    <div class="col-sm-5">
        <div class="panel panel-default" id="roomConfiguration">
            <div class="panel-heading">
                <i class="fas fa-check-double"></i> <?php echo __("Last 10 Attends"); ?>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                    <?php
                    $count = 0;
                    $r = $_REQUEST;
                    $p = $_POST;
                    $r = $_REQUEST['rowCount'] = 10;
                    $p = $_POST['sort']['ml.created'] = 'DESC';
                    $list = Meet_join_log::getAllFromUser(User::getId());
                    $_REQUEST = $r;
                    $_POST = $p;
                    foreach ($list as $value) {
                        $count++;
                        echo '<li class="list-group-item">#' . $count . " - " . $value['topic'] . ' <span class="badge">' . $value['created'] . '</span><br><small class="text-muted">' . $value['user_agent'] . '</small></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-sm-7">
        <div class="panel panel-default" id="roomConfiguration">
            <div class="panel-heading">
                <i class="fas fa-check"></i> <?php echo __("Meetings you can attend"); ?>
            </div>
            <div class="panel-body tabbable-line" id="logTabs">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#mUGToday"><?php echo __("Today"); ?></a></li>
                    <li><a data-toggle="tab" href="#mUGUpcoming"><?php echo __("Upcoming"); ?></a></li>
                    <li><a data-toggle="tab" href="#mUGPast"><?php echo __("Past"); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div id="mUGToday" class="tab-pane fade in active" style="padding: 10px;" url="<?php
                    echo $global['webSiteRootURL'] . 'plugin/Meet/meet_scheduled.php?meet_scheduled=today&manageMeetings=0&'.$userCredentials;
                    ?>"><div class="loader"></div></div>
                    <div id="mUGUpcoming" class="tab-pane fade" style="padding: 10px;" url="<?php
                    echo $global['webSiteRootURL'] . 'plugin/Meet/meet_scheduled.php?meet_scheduled=upcoming&manageMeetings=0&'.$userCredentials;
                    ?>"><div class="loader"></div></div>
                    <div id="mUGPast" class="tab-pane fade" style="padding: 10px;" url="<?php
                         echo $global['webSiteRootURL'] . 'plugin/Meet/meet_scheduled.php?meet_scheduled=past&manageMeetings=0&'.$userCredentials;
                         ?>"><div class="loader"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function () {
        $('#logTabs .nav-tabs a').click(function (e) {
            var now_tab = e.target // activated tab

            // get the div's id
            var divid = $(now_tab).attr('href').substr(1);
            var url = $("#" + divid).attr('url');
            $("#" + divid).attr('url', '');
            if (url) {
                $.ajax({
                    url: url,
                    success: function (response) {
                        $("#" + divid).html(response);
                    }
                });
            }
        });
        $('#logTabs .nav-tabs a').first().trigger("click");
    });
</script>