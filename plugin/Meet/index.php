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

if (!User::isLogged()) {
    forbiddenPage("You can not do this");
}
$userCredentials = User::loginFromRequestToGet();
if (User::isAdmin() && !empty($_GET['newServer'])) {
    $p = AVideoPlugin::loadPluginIfEnabled("Meet");
    $p->setDataObjectParameter("server->value", preg_replace("/[^0-1a-z.]/i", "", $_GET['newServer']));
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Meet"); ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
        <style>
            .serverLabels{
                padding-bottom: 15px;
            }
            .serverLabels .label{
                float: right;
                margin: 0 2px;
            }
            #serverProgressBar, #serverProgressBar .progress-bar {
                transition: width 1s linear !important;
                height: 2px;
                margin-bottom: 0;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid nopadding">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="far fa-comments"></i> <?php echo __("Meeting"); ?> <span id="serverTime" class="label label-default pull-right"  data-toggle="tooltip" data-placement="bottom" title="Server Time"></span>
                    <div class="pull-right serverLabels">
                        <span class="label label-warning"><i class="fas fa-cog fa-spin"></i> <?php echo __("Loading Server Info"); ?></span>
                    </div>
                    <div class="clearfix"></div>
                    <div class="progress" id="serverProgressBar"  data-toggle="tooltip" data-placement="bottom" title="Check Server Again" >
                        <div class="progress-bar" role="progressbar" aria-valuenow="100"
                             aria-valuemin="0" aria-valuemax="100" style="width:100%; ">
                        </div>
                    </div>
                </div>
                <div class="panel-body tabbable-line">
                    <div class="row">
                        <div class="col-xs-12 tabbable-line"  id="indexTabs">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#meetLog"><i class="far fa-clock"></i>   <span class="hidden-sm hidden-xs"><?php echo __("Meetings"); ?></span></a></li>
                                <?php
                                if (User::canCreateMeet()) {
                                    ?>
                                    <li><a data-toggle="tab" href="#createMeet"><i class="far fa-calendar-alt"></i>  <span class="hidden-sm hidden-xs"><?php echo __("Schedule"); ?></span></a></li>
                                    <li><a data-toggle="tab" href="#" onclick="_startMeetNow();return false;"><i class="far fa-comments"></i>  <?php echo __("New Meet"); ?></a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <div class="tab-content">
                                <div id="meetLog" class="tab-pane fade in active" url="<?php
                                echo $global['webSiteRootURL'] . 'plugin/Meet/meet_log.php?' . $userCredentials;
                                ?>"><div class="loader"></div></div>
                                     <?php
                                     if (User::canCreateMeet()) {
                                         ?>
                                    <div id="createMeet" class="tab-pane fade" url="<?php
                                    echo $global['webSiteRootURL'] . 'plugin/Meet/meet_manager.php?' . $userCredentials;
                                    ?>"><div class="loader"></div></div>
                                        <?php
                                    }
                                    ?>
                            </div> 
                        </div>   
                    </div>
                </div>
            </div>


            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
            <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

            <?php
            include $global['systemRootPath'] . 'view/include/footer.php';
            ?>
            <script>
                                        var serverLabelsRequestTime;
                                        $(document).ready(function () {
<?php $today = getdate(); ?>
                                            var d = new Date(<?php echo $today['year'] . "," . $today['mon'] . "," . $today['mday'] . "," . $today['hours'] . "," . $today['minutes'] . "," . $today['seconds']; ?>);
                                            setInterval(function () {
                                                d.setSeconds(d.getSeconds() + 1);
                                                $('#serverTime').html("<i class=\"far fa-clock\"></i> " + (d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds()));
                                            }, 1000);

                                            $('#indexTabs .nav-tabs a').click(function (e) {
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
                                                                success: function (response) {
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
                                            serverLabels();
                                        });
                                        var serverLabelsStartTime;
                                        var serverLabelsRunning = false;
                                        function serverLabels() {
                                            if (serverLabelsRunning) {
                                                return false;
                                            }
                                            serverLabelsRunning = true;
                                            serverLabelsStartTime = new Date().getTime();
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/serverLabels.php?<?php echo $userCredentials; ?>',
                                                            success: function (response) {
                                                                setTimeout(function () {
                                                                    serverLabelsRunning = false;
                                                                }, 2000);
                                                                serverLabelsRequestTime = new Date().getTime() - serverLabelsStartTime;
                                                                $('.serverLabels').html(response);
                                                            }
                                                        });
                                                    }


<?php
if (User::canCreateMeet()) {
    ?>
                                                        function _startMeetNow() {
                                                            console.log('_startMeetNow');
                                                            swal({
                                                                text: "<?php echo __("Meet Topic"); ?>",
                                                                content: "input",
                                                                button: {
                                                                    text: "<?php echo __("Start Now"); ?>",
                                                                    closeModal: false,
                                                                },
                                                            }).then(function (name) {
                                                                if (!name)
                                                                    throw null;
                                                                return fetch('<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php?<?php echo $userCredentials; ?>&RoomTopic=' + encodeURI(name));
                                                            }).then(function (results) {
                                                                return results.json();
                                                            }).then(function (response) {
                                                                if (response.error) {
                                                                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                                                    modal.hidePleaseWait();
                                                                } else {
                                                                    //console.log(response.link);
                                                                    //console.log(response.link+'?<?php echo $userCredentials; ?>');
                                                                    document.location = response.link + '?<?php echo $userCredentials; ?>';
                                                                    //avideoAlertError(response.link + '?<?php echo $userCredentials; ?>');
                                                                }

                                                            }).catch(function (err) {
                                                                if (err) {
                                                                    swal("Oh noes!", "The AJAX request failed!", "error");
                                                                } else {
                                                                    swal.stopLoading();
                                                                    swal.close();
                                                                }
                                                            });
                                                            return false;

                                                        }
    <?php
}
?>

            </script>
    </body>
</html>
