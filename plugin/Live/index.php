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
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
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

                    <div class="col-md-6" id="yptRightBar">
                        <?php
                        if (!empty($obj->experimentalWebcam)) {
                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading"><?php echo __("WebCam Streaming"); ?></div>
                                <div class="panel-body">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <div class="embed-responsive-item"  id="webcam">
                                            <button class="btn btn-primary btn-block" id="enableWebCam">
                                                <i class="fa fa-camera"></i> <?php echo __("Enable WebCam Stream"); ?>
                                            </button>
                                            <div class="alert alert-warning">
                                                <i class="fa fa-warning"><?php echo __("We will check if there is a stream conflict before stream"); ?></i>
                                            </div>

                                            <div class="alert alert-info">
                                                <?php echo __("This is an experimental resource"); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fa fa-share"></i> <?php echo __("Share Info"); ?> (<?php echo $channelName; ?>)</div>
                            <div class="panel-body">          
                                <div class="form-group">
                                    <label for="playerURL"><i class="fa fa-play-circle"></i> <?php echo __("Player URL"); ?>:</label>
                                    <input type="text" class="form-control" id="playerURL" value="<?php echo Live::getM3U8File($trasnmition['key']); ?>"  readonly="readonly">
                                </div>       
                                <div class="form-group">
                                    <label for="avideoURL"><i class="fa fa-circle"></i> <?php echo __("Live URL"); ?>:</label>
                                    <input type="text" class="form-control" id="avideoURL" value="<?php echo Live::getLinkToLiveFromUsers_id($users_id); ?>"  readonly="readonly">
                                </div>   
                                <div class="form-group">
                                    <label for="embedStream"><i class="fa fa-code"></i> <?php echo __("Embed Stream"); ?>:</label>
                                    <input type="text" class="form-control" id="embedStream" value='<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="<?php echo Live::getLinkToLiveFromUsers_id($users_id); ?>&embed=1" frameborder="0" allowfullscreen="allowfullscreen" ></iframe>'  readonly="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fas fa-hdd"></i> <?php echo __("Devices Stream Info"); ?> (<?php echo $channelName; ?>)</div>
                            <div class="panel-body" style="overflow: hidden;">
                                <div class="form-group">
                                    <label for="server"><i class="fa fa-server"></i> <?php echo __("Server URL"); ?>:</label>
                                    <input type="text" class="form-control" id="server" value="<?php echo Live::getServer(); ?>?p=<?php echo User::getUserPass(); ?>" readonly="readonly">
                                    <small class="label label-info"><i class="fa fa-warning"></i> <?php echo __("If you change your password the Server URL parameters will be changed too."); ?></small>
                                </div>
                                <div class="form-group">
                                    <label for="streamkey"><i class="fa fa-key"></i> <?php echo __("Stream name/key"); ?>:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="streamkey" value="<?php echo $trasnmition['key']; ?>" readonly="readonly">
                                        <span class="input-group-btn">
                                            <a class="btn btn-default" href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?resetKey=1"><i class="fa fa-refresh"></i> <?php echo __("Reset Key"); ?></a>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="streamkey"><i class="fa fa-key"></i> <?php echo __("Server URL"); ?> + <?php echo __("Stream name/key"); ?>:</label>
                                    <input type="text" class="form-control" id="serverAndStreamkey" value="<?php echo Live::getServer(); ?>?p=<?php echo User::getUserPass(); ?>/<?php echo $trasnmition['key']; ?>" readonly="readonly">
                                    <span class="label label-warning"><i class="fa fa-warning"></i> <?php echo __("Keep Key Private, Anyone with key can broadcast on your account"); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                        AVideoPlugin::getLivePanel();
                        ?>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php
                                $streamName = $trasnmition['key'];
                                include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
                                ?>
                            </div>
                            <div class="panel-body">          
                                <div class="embed-responsive embed-responsive-16by9">
                                    <video poster="<?php echo $global['webSiteRootURL']; ?><?php echo $poster; ?>?<?php echo filectime($global['systemRootPath'] . $poster); ?>" controls 
                                           class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                                           id="mainVideo" >
                                        <source src="<?php echo Live::getM3U8File($trasnmition['key']); ?>" type='application/x-mpegURL'>
                                    </video>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php
                                echo __("Upload Poster Image");
                                ?>
                                <button class="btn btn-danger btn-sm btn-xs pull-right" id="removePoster">
                                    <i class="far fa-trash-alt"></i> <?php echo __("Remove Poster"); ?>
                                </button>
                            </div>
                            <div class="panel-body"> 
                                <input id="input-jpg" type="file" class="file-loading" accept="image/*">
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading"><?php echo __("Stream Settings"); ?></div>
                            <div class="panel-body"> 
                                <div class="form-group">
                                    <label for="title"><?php echo __("Title"); ?>:</label>
                                    <input type="text" class="form-control" id="title" value="<?php echo $trasnmition['title'] ?>">
                                </div>    
                                <div class="form-group">
                                    <label for="description"><?php echo __("Description"); ?>:</label>
                                    <textarea class="form-control" id="description"><?php echo $trasnmition['description'] ?></textarea>
                                </div>
                                <!--
                                -->
                                <hr>
                                <div class="form-group">
                                    <span class="fa fa-globe"></span> <?php echo __("Make Stream Publicly Listed"); ?> 
                                    <b>(<?php echo __("MAKE SURE YOU CLICK SAVE"); ?>)</b>
                                    <div class="material-switch pull-right">
                                        <input id="listed" type="checkbox" value="1" <?php echo!empty($trasnmition['public']) ? "checked" : ""; ?>/>
                                        <label for="listed" class="label-success"></label> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading"><?php echo __("Groups That Can See This Stream"); ?><br><small><?php echo __("Uncheck all to make it public"); ?></small></div>
                            <div class="panel-body"> 
                                <?php
                                $ug = UserGroups::getAllUsersGroups();
                                foreach ($ug as $value) {
                                    ?>
                                    <div class="form-group">
                                        <span class="fa fa-users"></span> <?php echo $value['group_name']; ?>
                                        <div class="material-switch pull-right">
                                            <input id="group<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="userGroups" <?php echo (in_array($value['id'], $groups) ? "checked" : "") ?>/>
                                            <label for="group<?php echo $value['id']; ?>" class="label-success"></label>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <button type="button" class="btn btn-success" id="btnSaveStream"><?php echo __("Save Stream"); ?></button>
                                <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-primary"><span class="fa fa-users"></span> <?php echo __("Add more user Groups"); ?></a>
                            </div>
                        </div>

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
                            for (i = 0; i < response.applications.length; i++) {
                                if (response.applications[i].key === "<?php echo $trasnmition['key']; ?>") {
                                    offLine = false;
                                    break;
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
                                });

                                $('#removePoster').click(function () {
                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: "<?php echo $global['webSiteRootURL']; ?>plugin/Live/removePoster.php?live_servers_id=<?php echo $_REQUEST['live_servers_id']; ?>",
                                        success: function (response) {
                                            modal.hidePleaseWait();
                                            if(response.error){
                                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                            }else{
                                                $('.vjs-poster').css('background-image', 'url("<?php echo $global['webSiteRootURL']; ?>'+response.newPoster+'")');
                                                $('.kv-file-content img').attr('src', '<?php echo $global['webSiteRootURL']; ?>'+response.newPoster);
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
                                if (typeof player === 'undefined') {
                                    player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
                                }
                            });
        </script>
    </body>
</html>
