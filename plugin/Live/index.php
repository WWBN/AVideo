<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$isLive = 1;
$p = AVideoPlugin::loadPlugin("Live");

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if ($user['status'] !== 'a') {
        header("Location: {$global['webSiteRootURL']}");
    }
}
if (!empty($_GET['u']) && !empty($_GET['embedv2'])) {
    include $global['systemRootPath'] . 'plugin/Live/view/videoEmbededV2.php';
    exit;
} else if (!empty($_GET['u']) && !empty($_GET['embed'])) {
    include $global['systemRootPath'] . 'plugin/Live/view/videoEmbeded.php';
    exit;
} else if (!empty($_GET['u'])) {
    include $global['systemRootPath'] . 'plugin/Live/view/modeYoutubeLive.php';
    exit;
} else if (!User::canStream()) {
    header('HTTP/1.0 403 Forbidden');
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not stream live videos"));
    exit;
}

require_once $global['systemRootPath'] . 'objects/userGroups.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

$users_id = User::getId();
if (!empty($_GET['users_id']) && User::isAdmin()) {
    $users_id = intval($_GET['users_id']);
}

// if user already have a key
$trasnmition = LiveTransmition::createTransmitionIfNeed($users_id);
if (!empty($_GET['resetKey'])) {
    LiveTransmition::resetTransmitionKey($users_id);
    header("Location: {$global['webSiteRootURL']}plugin/Live/");
    exit;
}

$aspectRatio = "16:9";
$vjsClass = "vjs-16-9";

$trans = new LiveTransmition($trasnmition['id']);
$groups = $trans->getGroups();
$obj = $p->getDataObject();

//check if channel name exists
$channelName = User::getUserChannelName();
if (empty($channelName)) {
    $channelName = uniqid();
    $user = new User($users_id);
    $user->setChannelName($channelName);
    $user->save();
}

$col1Class = "col-md-6 col-lg-6";
$col2Class = "hidden";
$col3Class = "col-md-6 col-lg-6";
$chat2 = AVideoPlugin::getObjectDataIfEnabled("Chat2");
if (!empty($chat2) && !empty($chat2->useStaticLayout)) {
    $col1Class = "col-md-8 col-lg-5";
    $col2Class = "col-md-4 col-lg-3";
    $col3Class = "col-md-12 col-lg-4";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Live"); ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/swfobject.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.min.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>
        <style>
            .krajee-default.file-preview-frame .kv-file-content {
                width: auto;
                height: auto;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading tabbable-line">
                    <ul class="nav nav-tabs">
                        <?php
                        $activeServerFound = false;
                        if (!$obj->useLiveServers) {
                            $activeServerFound = true;
                            ?>
                            <li class="active">
                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?live_servers_id=0">
                                    <i class="fas fa-broadcast-tower"></i> <?php echo __("Local Server"); ?>
                                </a>
                            </li>
                            <?php
                        } else {
                            $servers = Live::getAllServers();
                            foreach ($servers as $key => $value) {
                                $active = "";
                                if (isset($_REQUEST['live_servers_id'])) {
                                    if ($_REQUEST['live_servers_id'] == $value['id']) {
                                        $activeServerFound = true;
                                        $active = "active";
                                    }
                                } else if ($key == 0) {
                                    $_REQUEST['live_servers_id'] = $value['id'];
                                    $activeServerFound = true;
                                    $active = "active";
                                }
                                ?>
                                <li class="<?php echo $active; ?>">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?live_servers_id=<?php echo $value['id']; ?>">
                                        <i class="fas fa-broadcast-tower"></i> <?php echo $value['name']; ?>
                                    </a>
                                </li>
                                <?php
                            }
                            if (User::isAdmin()) {
                                ?>
                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/editor.php" class="btn btn-primary pull-right"><i class="fa fa-edit"></i> Edit Live Servers</a>
                                <?php
                            }
                        }
                        if (empty($activeServerFound)) {
                            ?>
                            <li>
                                <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/editor.php" class="btn btn-danger">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo __("Server not found or inactive"); ?>
                                </a>
                            </li>
                            <?php
                        }

                        $_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
                        $poster = Live::getPosterImage(User::getId(), $_REQUEST['live_servers_id']);
                        ?>
                    </ul>
                </div>
                <div class="panel-body">

                    <div class="<?php echo $col1Class; ?>">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php
                                $streamName = $trasnmition['key'];
                                include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
                                ?>
                                <?php
                                if (Live::canStreamWithMeet()) {
                                    include $global['systemRootPath'] . 'plugin/Live/meet.php';
                                }
                                ?>
                            </div>
                            <div class="panel-body">          
                                <div class="embed-responsive embed-responsive-16by9">
                                    <?php
                                    if (Live::canStreamWithMeet()) {
                                        ?>
                                        <div id="divMeetToIFrame"></div> 
                                        <?php
                                    }
                                    ?>
                                    <video poster="<?php echo $global['webSiteRootURL']; ?><?php echo $poster; ?>?<?php echo filectime($global['systemRootPath'] . $poster); ?>" controls 
                                           class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                                           id="mainVideo" >
                                        <source src="<?php echo Live::getM3U8File($trasnmition['key']); ?>" type='application/x-mpegURL'>
                                    </video>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <?php
                                echo Live::getAllControlls($trasnmition['id']);
                                ?>
                            </div>
                        </div>
                        <?php
                        include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamSettings.php';
                        ?>

                    </div>
                    <div class="<?php echo $col2Class; ?>" id="yptRightBar"></div>
                    <div class="<?php echo $col3Class; ?>">

                        <?php
                        if (!empty($obj->experimentalWebcam)) {
                            include $global['systemRootPath'] . 'plugin/Live/tabs/experimentalWebCam.php';
                        }
                        ?>
                        <div class="clear clearfix"></div>

                        <div class="tabbable-line"  id="indexTabs">
                            <ul class="nav nav-tabs">
                                <li class="active" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use streaming software or hardware"); ?>"><a data-toggle="tab" href="#tabStreamKey"><i class="fas fa-key"></i> <?php echo __("Stream Key"); ?></a></li>
                                <li class="" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Share information about your live"); ?>"><a data-toggle="tab" href="#tabShare"><i class="fa fa-share"></i> <?php echo __("Share"); ?></a></li>
                                <?php
                                if (empty($obj->disableRestream)) {
                                    ?>
                                    <li class="" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Live stream to other platforms simultaneously"); ?>"><a data-toggle="tab" href="#tabRestream"><i class="fas fa-sync"></i> <?php echo __("Restream"); ?></a> </li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <div class="tab-content">
                                <div id="tabStreamKey" class="tab-pane fade in active">
                                    <?php
                                    include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamKey.php';
                                    ?>
                                </div>
                                <div id="tabShare" class="tab-pane fade"> 
                                    <?php
                                    include $global['systemRootPath'] . 'plugin/Live/tabs/tabShare.php';
                                    ?>
                                </div>
                                <?php
                                if (empty($obj->disableRestream)) {
                                    ?>
                                    <div id="tabRestream" class="tab-pane fade"> 
                                        <?php
                                        include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanel.php';
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>

                            </div> 
                        </div>  
                        <?php
                        AVideoPlugin::getLivePanel();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $p->getChat($trasnmition['key']);
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            var flashvars = {server: "<?php echo Live::getServer(); ?>?p=<?php echo User::getUserPass(); ?>", stream: "<?php echo $trasnmition['key']; ?>"};
                var params = {};
                var attributes = {};
                function amIOnline() {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/stats.json.php?checkIfYouOnline',
                        data: {"name": "<?php echo $streamName; ?>"},
                        type: 'post',
                        success: function (response) {
                            offLine = true;
                            if (response.applications) {
                                for (i = 0; i < response.applications.length; i++) {
                                    if (response.applications[i].key === "<?php echo $trasnmition['key']; ?>") {
                                        offLine = false;
                                        break;
                                    }
                                }
                            }
                            // you online do not show webcam
                            if (!offLine) {
                                $('#webcam').find('.alert').text("<?php echo __("You are online now, web cam is disabled"); ?>");
                            } else {
                                $('#webcam').find('.alert').text("<?php echo __("You are not online, loading webcam..."); ?>");
                                swfobject.embedSWF("<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/webcam.swf", "webcam", "100%", "100%", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
                            }
                        }
                    });
                }

                function saveStream() {
                    modal.showPleaseWait();

                    var selectedUserGroups = [];
                    $('.userGroups:checked').each(function () {
                        selectedUserGroups.push($(this).val());
                    });

                    $.ajax({
                        url: 'saveLive.php',
                        data: {
                            "title": $('#title').val(),
                            "description": $('#description').val(),
                            "key": "<?php echo $trasnmition['key']; ?>",
                            "listed": $('#listed').is(":checked"),
                            "userGroups": selectedUserGroups
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                }
                $(document).ready(function () {
                    $("#input-jpg").fileinput({
                        uploadUrl: "<?php echo $global['webSiteRootURL']; ?>plugin/Live/uploadPoster.php?live_servers_id=<?php echo $_REQUEST['live_servers_id']; ?>",
                                    autoReplace: true,
                                    overwriteInitial: true,
                                    showUploadedThumbs: false,
                                    showPreview: true,
                                    maxFileCount: 1,
                                    initialPreview: [
                                        "<img class='img img-responsive' src='<?php echo $global['webSiteRootURL']; ?><?php echo $poster; ?>?<?php echo filectime($global['systemRootPath'] . $poster); ?>'>",
                                    ],
                                    initialCaption: 'LiveBG.jpg',
                                    initialPreviewShowDelete: false,
                                    showRemove: false,
                                    showClose: false,
                                    layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
                                    allowedFileExtensions: ["jpg", "jpeg", "png"],
                                    //minImageWidth: 2048,
                                    //minImageHeight: 1152,
                                    //maxImageWidth: 2560,
                                    //maxImageHeight: 1440
                                }).on('fileuploaded', function (event, previewId, index, fileId) {
                                    var poster = webSiteRootURL + '<?php echo Live::_getPosterImage(User::getId(), $_REQUEST['live_servers_id']); ?>?' + Math.random();
                                    $('#mainVideo video').attr('poster', poster);
                                    $('#mainVideo .vjs-poster').css('background-image', 'url("' + poster + '"');
                                });

                                $('#removePoster').click(function () {
                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: "<?php echo $global['webSiteRootURL']; ?>plugin/Live/removePoster.php?live_servers_id=<?php echo $_REQUEST['live_servers_id']; ?>",
                                                        success: function (response) {
                                                            modal.hidePleaseWait();
                                                            if (response.error) {
                                                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                                            } else {
                                                                $('#mainVideo video').attr('poster', webSiteRootURL + response.newPoster);
                                                                $('#mainVideo .vjs-poster').css('background-image', 'url("' + webSiteRootURL + response.newPoster + '")');
                                                                $('.kv-file-content img').attr('src', '<?php echo $global['webSiteRootURL']; ?>' + response.newPoster);
                                                            }
                                                        }
                                                    });
                                                });
                                                $('#btnSaveStream').click(function () {
                                                    saveStream();
                                                });
                                                $('#enableWebCam').click(function () {
                                                    amIOnline();
                                                });
                                                
                                                <?php
                                                echo PlayerSkins::getStartPlayerJS("", "", true);
                                                ?>
                                            });
        </script>
    </body>
</html>
