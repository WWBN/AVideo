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
    forbiddenPage("Plugin disabled");
}

if(!empty($_GET['redirectUri'])){
    $parts = explode('/', addLastSlash($_GET['redirectUri']));
    $name = $parts[count($parts)-2];
    if(!empty($name)){
        error_log('Meet topic: '.$name);
        $row = Meet_schedule::getFromName($name);
        if(!empty($row )){
            $link = Meet::getMeetLink($row['id']);
            error_log('Meet redirect: '.$link);
            header("Location: $link");
            exit;
        }else{
            error_log('Meet row is empty: '.$topic);
        }
    }
}

if (!User::isLogged()) {
    forbiddenPage("You can not do this");
}
$userCredentials = User::loginFromRequestToGet();
if (User::isAdmin() && !empty($_GET['newServer'])) {
    $p = AVideoPlugin::loadPluginIfEnabled("Meet");
    $p->setDataObjectParameter("server->value", preg_replace("/[^0-1a-z.]/i", "", $_GET['newServer']));
}
$_page = new Page(array('Meet'));
$_page->loadBasicCSSAndJS();
?>
<style>
    .serverLabels {
        padding-bottom: 15px;
    }

    .serverLabels .label {
        float: right;
        margin: 0 2px;
    }

    #serverProgressBar,
    #serverProgressBar .progress-bar {
        transition: width 1s linear !important;
        height: 2px;
        margin-bottom: 0;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="far fa-comments"></i> <?php echo __("Meeting"); ?>
            <span id="serverTime" class="label label-default pull-right" data-toggle="tooltip" data-placement="bottom" title="Server Time"></span>
            <span class="label label-default pull-right" data-toggle="tooltip" data-placement="bottom" title="Timezone"> <?php echo date_default_timezone_get(); ?> </span>

            <div class="clearfix"></div>
            <div class="progress" id="serverProgressBar" data-toggle="tooltip" data-placement="bottom" title="Check Server Again">
                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; ">
                </div>
            </div>
        </div>
        <div class="panel-body tabbable-line">
            <div class="row">
                <div class="col-xs-12 tabbable-line" id="indexTabs">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#meetLog"><i class="far fa-clock"></i> <span class="hidden-sm hidden-xs"><?php echo __("Meetings"); ?></span></a></li>
                        <?php
                        if (User::canCreateMeet()) {
                        ?>
                            <li><a data-toggle="tab" href="#createMeet"><i class="far fa-calendar-alt"></i> <span class="hidden-sm hidden-xs"><?php echo __("Schedule"); ?></span></a></li>
                            <li><a data-toggle="tab" href="#none" onclick="_startMeetNow();return false;"><i class="far fa-comments"></i> <?php echo __("New Meet"); ?></a></li>
                        <?php
                        }
                        ?>
                    </ul>
                    <div class="tab-content">
                        <div id="meetLog" class="tab-pane fade in active" url="<?php
                                                                                echo $global['webSiteRootURL'] . 'plugin/Meet/meet_log.php?' . $userCredentials;
                                                                                ?>">
                            <div class="loader"></div>
                        </div>
                        <?php
                        if (User::canCreateMeet()) {
                        ?>
                            <div id="createMeet" class="tab-pane fade" url="<?php
                                                                            echo $global['webSiteRootURL'] . 'plugin/Meet/meet_manager.php?' . $userCredentials; ?>">
                                <div class="loader"></div>
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
    var serverLabelsRequestTime;
    $(document).ready(function() {
        <?php $today = getdate(); ?>
        var d = new Date(<?php echo $today['year'] . "," . $today['mon'] . "," . $today['mday'] . "," . $today['hours'] . "," . $today['minutes'] . "," . $today['seconds']; ?>);
        setInterval(function() {
            d.setSeconds(d.getSeconds() + 1);
            $('#serverTime').html("<i class=\"far fa-clock\"></i> " + (d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds()));
        }, 1000);

        $('#indexTabs .nav-tabs a').click(function(e) {
            var href = $(this).attr('href');
            if (href && href !== "#") {
                var now_tab = $(href);
                console.log("tab clicked");
                if ($(now_tab).attr('url')) {
                    var url = $(now_tab).attr('url');
                    $(now_tab).attr('url', '');
                    if (url) {
                        $.ajax({
                            url: url,
                            success: function(response) {
                                $(now_tab).html(response);
                            }
                        });
                    }
                } else {
                    console.log("no URL on tab clicked");
                }
            }
        });
        try {
            $('#indexTabs .nav-tabs a').first().trigger("click");
        } catch (e) {

        }
    });
    <?php
    if (User::canCreateMeet()) {
    ?>

        function _startMeetNow() {
            var userCredentials = '<?php echo $userCredentials; ?>';
            console.log('_startMeetNow 1');
            swal({
                text: __("Meet Topic"),
                content: "input",
                button: {
                    text: __("Start Now"),
                    closeModal: false,
                },
            }).then(function(name) {
                if (!name)
                    throw null;
                var url = webSiteRootURL + 'plugin/Meet/saveMeet.json.php?' + userCredentials + '&RoomTopic=' + encodeURI(name);
                console.log('_startMeetNow 2', url);
                return fetch(url);
            }).then(function(results) {
                console.log('_startMeetNow 3', results);
                return results.json();
            }).then(function(response) {
                console.log('_startMeetNow 4', response);
                if (response.error) {
                    avideoAlertError(response.msg);
                    modal.hidePleaseWait();
                } else {
                    document.location = response.link + '?' + userCredentials;
                }

            }).catch(function(err) {
                console.log('_startMeetNow 5', err);
                if (err) {
                    swal("Oh noes!", "The AJAX request failed!", "error");
                } else {
                    swal.stopLoading();
                    swal.close();
                }
            });
            console.log('_startMeetNow 6');
            return false;

        }
    <?php
    }
    ?>
</script>
<?php
$_page->print();
?>
