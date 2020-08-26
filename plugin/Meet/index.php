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

if (User::isAdmin() && !empty($_GET['newServer'])) {
    $p = AVideoPlugin::loadPluginIfEnabled("Meet");
    $p->setDataObjectParameter("server->value", preg_replace("/[^0-1a-z.]/i", "", $_GET['newServer']));
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Live"); ?> - <?php echo $config->getWebSiteTitle(); ?></title>
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
        <div class="container-fluid">
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
                                <li class="active"><a data-toggle="tab" href="#meetLog"><i class="far fa-clock"></i> <?php echo __("Meetings"); ?></a></li>
                                <?php
                                if (User::canCreateMeet()) {
                                    ?>
                                    <li><a data-toggle="tab" href="#createMeet"><i class="far fa-calendar-alt"></i>  <?php echo __("Schedule"); ?></a></li>
                                    <li><a data-toggle="tab" href="#" onclick="startMeetNow();return false;"><i class="far fa-comments"></i>  <?php echo __("New Meet"); ?></a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <div class="tab-content">
                                <div id="meetLog" class="tab-pane fade in active" url="<?php
                                echo $global['webSiteRootURL'] . 'plugin/Meet/meet_log.php';
                                ?>"><div class="loader"></div></div>
                                     <?php
                                     if (User::canCreateMeet()) {
                                         ?>
                                    <div id="createMeet" class="tab-pane fade" url="<?php
                                    echo $global['webSiteRootURL'] . 'plugin/Meet/meet_manager.php';
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
                                                var now_tab = e.target // activated tab
                                                if(!$(now_tab).attr('href')){
                                                    return false;
                                                }
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
                                            $('#indexTabs .nav-tabs a').first().trigger("click");

                                            serverLabels();
                                        });
                                        var serverLabelsStartTime;
                                        function serverLabels() {
                                            serverLabelsStartTime = new Date().getTime();
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/serverLabels.php',
                                                success: function (response) {
                                                    serverLabelsRequestTime = new Date().getTime() - serverLabelsStartTime;
                                                    $('.serverLabels').html(response);
                                                }
                                            });
                                        }


<?php
if (User::canCreateMeet()) {
    ?>
                                            function startMeetNow() {
                                                modal.showPleaseWait();
                                                $.ajax({
                                                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php',
                                                    data: {},
                                                    type: 'post',
                                                    success: function (response) {
                                                        if (response.error) {
                                                            swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                                            modal.hidePleaseWait();
                                                        } else {
                                                            document.location = response.link;
                                                        }
                                                    }
                                                });
                                            }
    <?php
}
?>

            </script>
    </body>
</html>
