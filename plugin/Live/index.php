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

$aspectRatio = "16:9";
$vjsClass = "vjs-16-9";
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
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="col-md-6">
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

                <div class="panel panel-default">
                    <div class="panel-heading">Start Stream</div>
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
                        <hr>
                        <div class="form-group">
                            <span class="fa fa-globe"></span> <?php echo __("Public Video"); ?>
                            <div class="material-switch pull-right">
                                <input id="public" type="checkbox" value="0" class="userGroups"/>
                                <label for="public" class="label-success"></label>
                            </div>
                        </div>
                        <?php
                        $ug = UserGroups::getAllUsersGroups();
                        foreach ($ug as $value) {
                            ?>
                                                    <div class="form-group">
                                                        <span class="fa fa-users"></span> <?php echo $value['group_name']; ?>
                                                        <div class="material-switch pull-right">
                                                            <input id="public" type="checkbox" value="0" class="userGroups"/>
                                                            <label for="public" class="label-success"></label>
                                                        </div>
                                                    </div>    
                            <?php
                        }
                        ?>
                        -->
                        <button type="button" class="btn btn-danger" id="btnSaveStream">Save Stream</button>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-share"></i> Share Info</div>
                    <div class="panel-body">          
                        <div class="form-group">
                            <label for="streamURL"><i class="fa fa-circle"></i> Stream URL:</label>
                            <input type="text" class="form-control" id="streamURL" value="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?u=<?php echo User::getUserName(); ?>"  readonly="readonly">
                        </div>   
                        <div class="form-group">
                            <label for="streamURL"><i class="fa fa-code"></i> Embed Stream:</label>
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
                            <input type="text" class="form-control" id="streamkey" value="<?php echo $trasnmition['key']; ?>" readonly="readonly">
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
                                <source src="<?php echo $p->getPlayerServer(); ?>/<?php echo $trasnmition['key']; ?>" type='rtmp/flv'>
                            </video>
                        </div>
                    </div>
                </div>

                <?php
                $p->getChat($trasnmition['key']);
                ?>

                <div class="alert alert-warning">
                    <?php echo __("For Live streaming is necessary enable flash in your browser."); ?>
                    <?php echo __("Make sure you have it enabled:"); ?>

                    <a href="https://helpx.adobe.com/flash-player/kb/enabling-flash-player-chrome.html" target="_blank" class="btn btn-warning">
                        <i class="fa fa-chrome"></i> Chrome Users
                    </a>
                    <a href="https://helpx.adobe.com/flash-player/kb/enabling-flash-player-firefox.html" target="_blank" class="btn btn-warning">
                        <i class="fa fa-firefox"></i> Firefox users
                    </a>
                    <a href="https://helpx.adobe.com/flash-player/kb/install-flash-player-windows.html" target="_blank" class="btn btn-warning">
                        <i class="fa fa-internet-explorer"></i> IE Users
                    </a>
                </div>
            </div>
        </div>
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
                    $.ajax({
                        url: 'saveLive.php',
                        data: {"title": $('#title').val(), "description": $('#description').val(), "key": "<?php echo $trasnmition['key']; ?>"},
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
