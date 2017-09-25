<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$p = YouPHPTubePlugin::loadPlugin("Live");

if (!empty($_GET['u']) && !empty($_GET['embed'])) {
    include './view/videoEmbeded.php';
    exit;
} else if (!empty($_GET['u'])) {
    include './view/modeYoutubeLive.php';
    exit;
} else if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not stream live videos"));
    exit;
}

require_once $global['systemRootPath'] . 'objects/userGroups.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

// if user already have a key
$trasnmition = LiveTransmition::createTransmitionIfNeed(User::getId());
if(!empty($_GET['resetKey'])){
    LiveTransmition::resetTransmitionKey(User::getId());
    header("Location: {$global['webSiteRootURL']}plugin/Live/");
    exit;
}

$aspectRatio = "16:9";
$vjsClass = "vjs-16-9";

$trans = new LiveTransmition($trasnmition['id']);
$groups = $trans->getGroups();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Live - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/swfobject.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/videojs-contrib-hls.min.js" type="text/javascript"></script>

    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="col-md-6">
                <!--
                <div class="panel panel-default">
                    <div class="panel-heading">WebCam Streaming</div>
                    <div class="panel-body">
                        <div class="embed-responsive embed-responsive-16by9">
                            <div class="embed-responsive-item"  id="webcam">
                                <button class="btn btn-primary btn-block" id="enableWebCam">
                                    <i class="fa fa-camera"></i> Enable WebCam Stream
                                </button>
                                <div class="alert alert-warning">
                                    <i class="fa fa-warning">We will check it there is a stream conflict before stream</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                -->
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-share"></i> Share Info</div>
                    <div class="panel-body">          
                        <div class="form-group">
                            <label for="playerURL"><i class="fa fa-play-circle"></i> Player URL:</label>
                            <input type="text" class="form-control" id="playerURL" value="<?php echo $p->getPlayerServer(); ?>/<?php echo $trasnmition['key']; ?>/index.m3u8"  readonly="readonly">
                        </div>       
                        <div class="form-group">
                            <label for="youphptubeURL"><i class="fa fa-circle"></i> YouPHPTube URL:</label>
                            <input type="text" class="form-control" id="youphptubeURL" value="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?u=<?php echo User::getUserName(); ?>"  readonly="readonly">
                        </div>   
                        <div class="form-group">
                            <label for="embedStream"><i class="fa fa-code"></i> Embed Stream:</label>
                            <input type="text" class="form-control" id="embedStream" value='<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?u=<?php echo User::getUserName(); ?>&embed=1" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>'  readonly="readonly">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-hdd-o"></i> Devices Stream Info</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="server"><i class="fa fa-server"></i> Server URL:</label>
                            <input type="text" class="form-control" id="server" value="<?php echo $p->getServer(); ?>?p=<?php echo User::getUserPass(); ?>" readonly="readonly">
                            <small class="label label-info"><i class="fa fa-warning"></i> If you change your password the Server URL parameters will be changed too.</small>
                        </div>
                        <div class="form-group">
                            <label for="streamkey"><i class="fa fa-key"></i> Stream name/key:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="streamkey" value="<?php echo $trasnmition['key']; ?>" readonly="readonly">
                                <span class="input-group-btn">
                                    <a class="btn btn-default" href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?resetKey=1"><i class="fa fa-refresh"></i> Reset Key</a>
                                </span>
                            </div>
                            <span class="label label-warning"><i class="fa fa-warning"></i> Anyone with this key can watch your live stream.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php
                        $streamName = $trasnmition['key'];
                        include './view/onlineLabel.php';
                        ?>
                    </div>
                    <div class="panel-body">          
                        <div class="embed-responsive embed-responsive-16by9">
                            <video poster="<?php echo $global['webSiteRootURL']; ?>img/youphptubeLiveStreaming.jpg" controls crossorigin 
                                   class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                                   id="mainVideo" data-setup='{ aspectRatio: "<?php echo $aspectRatio; ?>",  "techorder" : ["flash", "html5"] }'>
                                <source src="<?php echo $p->getPlayerServer(); ?>/<?php echo $trasnmition['key']; ?>/index.m3u8" type='application/x-mpegURL'>
                            </video>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Stream Settings</div>
                    <div class="panel-body"> 
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" class="form-control" id="title" value="<?php echo $trasnmition['title'] ?>">
                        </div>    
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea class="form-control" id="description"><?php echo $trasnmition['description'] ?></textarea>
                        </div>
                        <!--
                        -->
                        <hr>
                        <div class="form-group">
                            <span class="fa fa-globe"></span> <?php echo __("Listed Transmition"); ?> 
                            <div class="material-switch pull-right">
                                <input id="listed" type="checkbox" value="1" <?php echo!empty($trasnmition['public']) ? "checked" : ""; ?>/>
                                <label for="listed" class="label-success"></label> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Groups That Can See This Stream<br><small>Uncheck all to make it public</small></div>
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
                        <button type="button" class="btn btn-success" id="btnSaveStream">Save Stream</button>
                        <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-primary"><span class="fa fa-users"></span> Add more user Groups</a>
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
            var flashvars = {server: "<?php echo $p->getServer(); ?>?p=<?php echo User::getUserPass(); ?>", stream: "<?php echo $trasnmition['key']; ?>"};
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
                                $('#webcam').find('.alert').text("You are online now, web cam is disabled");
                            } else {
                                $('#webcam').find('.alert').text("You are not online, loading webcam...");
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
                    $('#btnSaveStream').click(function () {
                        saveStream();
                    });
                    $('#enableWebCam').click(function () {
                        amIOnline();
                    });
                });
        </script>
    </body>
</html>
