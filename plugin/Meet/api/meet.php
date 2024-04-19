<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../../videos/configuration.php';
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

// print_r(base64_decode($end_meet_redirect));

$domain = $_REQUEST['domain'];

?>
        <link rel="stylesheet"  type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-toast/jquery.toast.min.css">    
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
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
    <!-- </head>
    <body> -->
        
        <div class="container-fluid nopadding">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-comments"></i> <?php echo __("Meeting"); ?> <span id="serverTime" class="label label-default pull-right"  data-toggle="tooltip" data-placement="bottom" title="Server Time"></span>
                    
                    <?php 

                    if (User::isAdmin()) {

                    ?>
                    <div class="pull-right serverLabels">
                        <span class="label label-warning"><i class="fa fa-cog fa-spin"></i> <?php echo __("Loading Server Info"); ?></span>
                    </div>
                    <div class="clearfix"></div>
                    <div class="progress" id="serverProgressBar"  data-toggle="tooltip" data-placement="bottom" title="Check Server Again" >
                        <div class="progress-bar" role="progressbar" aria-valuenow="100"
                             aria-valuemin="0" aria-valuemax="100" style="width:100%; ">
                        </div>
                    </div>

                    <?php } ?>

                </div>
                <div class="panel-body tabbable-line">
                    <div class="row">
                        <div class="col-xs-12 tabbable-line"  id="indexTabs">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#meetLog"><i class="fa fa-clock-o"></i>   <span class="hidden-sm hidden-xs"><?php echo __("Meetings"); ?></span></a></li>
                                <?php
                                if (User::canCreateMeet()) {
                                    ?>
                                    <li><a data-toggle="tab" href="#createMeet"><i class="fa fa-calendar"></i>  <span class="hidden-sm hidden-xs"><?php echo __("Schedule"); ?></span></a></li>
                                    <li><a data-toggle="tab" href="#" onclick="_startMeetNow();return false;" style="cursor: pointer;"><i class="fa fa-comments"></i>  <?php echo __("New Meet"); ?></a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <div class="tab-content">
                                <div id="meetLog" class="tab-pane fade in active" url="<?php echo $global['webSiteRootURL'] . 'plugin/Meet/api/meeting_tab.php?' . $userCredentials. '&end_meet_redirect='.$end_meet_redirect;
                                ?>">
                                    <div class="loader"></div>
                                </div>
                                <?php
                                    if (User::canCreateMeet()) {
                                ?>
                                    <div id="createMeet" class="tab-pane fade" url="<?php echo $global['webSiteRootURL'] . 'plugin/Meet/api/schedule_tab.php?' . $userCredentials. '&end_meet_redirect='.$end_meet_redirect.'&domain='.$domain; ?>">
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
            <textarea id="elementToCopy" style="
                filter: alpha(opacity=0);
                -moz-opacity: 0;
                -khtml-opacity: 0;
                opacity: 0;
                position: absolute;
                z-index: -9999;
                top: 0;
                left: 0;
                pointer-events: none;"></textarea>

            <div id="swal-div"></div>

            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>node_modules/js-cookie/dist/js.cookie.js"></script>
            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/js/script.js?<?php echo filectime("{$global['systemRootPath']}view/js/script.js"); ?>"></script>
            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-toast/jquery.toast.min.js"></script>
            <!-- <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/js/seetalert/sweetalert.min.js"></script> -->
            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
            <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>node_modules/sweetalert/dist/sweetalert.min.js"></script>

            <?php
            // include $global['systemRootPath'] . 'view/include/footer.php';
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
                                                    
                                                    if ($(now_tab).attr('url')) {
                                                        var url = $(now_tab).attr('url');
                                                        $(now_tab).attr('url', '');
                                                        if (url) {
                                                            $.ajax({
                                                                url: url,
                                                                data: {domain: "<?php echo $domain ?>"},
                                                                xhrFields: {
                                                                    withCredentials: true
                                                                },
                                                                crossDomain: true,
		                                                        dataType : 'html',
                                                                success: function (response) {
                                                                    $(now_tab).html(response);
                                                                }, 
                                                                error: function(err) {
                                                                    console.log(err)
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
                                                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/api/serverLabels.php?<?php echo $userCredentials; ?>',
                                                data: {domain: "<?= $domain ?>", sitelinkid: "<?= $_REQUEST['sitelinkid'] ?>", changeServerURL: "<?= $_REQUEST['changeServerURL'] ?>"},
                                                xhrFields: {
                                                    withCredentials: true
                                                },
                                                crossDomain: true,
                                                dataType : 'html',
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
    function addScript(src, yp_swal = false) {
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');

            if (yp_swal) {
                var d = document.getElementById('swal-div');
            } else {
                var d = document.body;
            }

            s.setAttribute('src', src);
            s.addEventListener('load', resolve);
            s.addEventListener('error', reject);

            d.appendChild(s);
        });
    }

    function removeScript(yp_swal = false) {
        return new Promise((resolve, reject) => {
            if (yp_swal) {
                $('script[src="<?php echo $global['webSiteRootURL']; ?>view/js/seetalert/sweetalert.min.js"]').remove();
            } else {
                $('script[src="/platform/assets/plugins/bootstrap-sweetalert/sweet-alert.js"]').remove();
            }
        });
    }
                                                        function _startMeetNow() {
                                                            console.log('_startMeetNow');

                                                            removeScript()
                                                            addScript('<?php echo $global['webSiteRootURL']; ?>view/js/seetalert/sweetalert.min.js')
                                                            
                                                            setTimeout(() => {
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
                                                                        // console.log(response.link+'?<?php echo $userCredentials; ?>');
                                                                        var link = response.link.replace("<?php echo $global['webSiteRootURL'] ?>", "<?php echo $_REQUEST['domain'] ?>/");
                                                                        link = link + "?redirect=<?php echo $end_meet_redirect; ?>";
                                                                        window.open(link, '_self'); 
                                                                        // location.reload();
                                                                        // document.location = response.link + '?<?php echo $userCredentials; ?>';
                                                                        //avideoAlertError(response.link + '?<?php echo $userCredentials; ?>');
                                                                        swal.close();
                                                                    }

                                                                }).catch(function (err) {
                                                                    addScript('/platform/assets/plugins/bootstrap-sweetalert/sweet-alert.js', true)
                                                                    removeScript(true)
                                                                    if (err) {
                                                                        swal("Oh noes!", "The AJAX request failed!", "error");
                                                                    } else {
                                                                        swal.stopLoading();
                                                                        swal.close();
                                                                    }
                                                                });
                                                            }, 2000);
                                                            return false;

                                                        }
    <?php
}
?>

            </script>