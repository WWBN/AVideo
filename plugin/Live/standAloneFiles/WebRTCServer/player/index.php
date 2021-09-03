<?php
require_once '../functions.php';
$global['webSiteRootURL'] = @$_REQUEST['webSiteRootURL'];
$global['userHash'] = @$_REQUEST['userHash'];

if (empty($global['webSiteRootURL'])) {
    $global['webSiteRootURL'] = 'http://192.168.1.4/YouPHPTube/';
}
if (empty($global['userHash'])) {
    $global['userHash'] = 'test';
}

$global['WebRTCserver'] = $webRTCServerURL;
$host = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
$stream = "{$host}_" . uniqid();
$hidden = 'hidden';

if (!function_exists('__')) {

    function __($text) {
        return $text;
    }

}

$links = getLinks($stream);

$httpsWebSiteRootURL = $global['webSiteRootURL'];
if(!preg_match('/^https/', $httpsWebSiteRootURL)){
    $httpsWebSiteRootURL = 'https://tutorialsavideocom.cdn.ypt.me/';
}

if(empty($webRTCServerCDNURL)){
    $webRTCServerCDNURL = $global['WebRTCserver'];
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo $httpsWebSiteRootURL; ?>view/img/favicon.ico">
        <title>Live Cam</title>
        <link href="<?php echo $httpsWebSiteRootURL; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $httpsWebSiteRootURL; ?>view/css/custom/netflix.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $httpsWebSiteRootURL; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $httpsWebSiteRootURL; ?>view/js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <link href="<?php echo $webRTCServerCDNURL; ?>player/playerButtons.css?cache=<?php echo filemtime('playerButtons.css'); ?>" rel="stylesheet" type="text/css"/>
        <script>
            var webSiteRootURL = '<?php echo $httpsWebSiteRootURL; ?>';
            var WebRTCserver = '<?php echo $global['WebRTCserver']; ?>';
            var userHash = '<?php echo $global['userHash']; ?>';
            var stream = '<?php echo $stream; ?>';
        </script>
        <script>
            if (window.location.href.indexOf('192.168.1') < 0 && window.location.href.indexOf('localhost') < 0 && window.location.protocol !== 'https:') {
                location.href = location.href.replace('http://', 'https://');
            }
        </script>
    </head>
    <body>
        <!-- WebRTC -->
        <video controls muted playsinline webkit-playsinline="webkit-playsinline" 
               class="video-js vjs-default-skin vjs-big-play-centered"
               id="mainVideo"></video>
        <!-- WebRTC finish -->
        <div class="modal fade" id="webRTCModalConfig" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#basic">Basic</a></li>
                            <li><a data-toggle="tab" href="#advanced">Advanced</a></li>
                            <li><a data-toggle="tab" href="#links">Links</a></li>
                        </ul>
                    </div>
                    <div class="modal-body">
                        <div class="tab-content">
                            <div id="basic" class="tab-pane fade in active">
                                <div class="row">
                                    <div class="col-sm-12 <?php echo $hidden; ?>" >
                                        <div class="">
                                            <span >WebRTC Input URL</span>
                                            <input id="webRtcUrlInput" type="text" class="form-control" placeholder="Please enter the OME WebRTC input URL." value="<?php echo $links['publish_webrtc']; ?>">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label  for="videoSourceSelect">Video Source</label>
                                        <select class="custom-select constraintSelect form-control" id="videoSourceSelect">
                                            <option selected>Choose...</option>
                                        </select>
                                    </div>
                                    <div id="audioSourceSelectArea" class="col-sm-6">
                                        <label for="audioSourceSelect">Audio Source</label>
                                        <select class="custom-select constraintSelect  form-control" id="audioSourceSelect">
                                            <option selected>Choose...</option>
                                        </select>
                                    </div>
                                    <small id="errorText" class="form-text text-danger text-center mt-2"></small>


                                </div>
                            </div>
                            <div id="advanced" class="tab-pane fade">
                                <div class="row">

                                    <div class="col-sm-6">
                                        <label  for="videoResolutionSelect">Video Resolution</label>
                                        <select class="custom-select constraintSelect form-control" id="videoResolutionSelect">
                                            <option value="">Not Set</option>
                                            <option value="fhd">Full HD (1920x1080)</option>
                                            <option value="hd">HD (1280x720)</option>
                                            <option value="vga" selected="selected">VGA (640x480)</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label  for="videoFrameInput">Video <small>(fps)</small></label>
                                        <input id="videoFrameInput" type="number" class="form-control constraintSelect" placeholder="Not Set" 
                                               value="15">
                                    </div>
                                    <div class="col-sm-3">
                                        <label  for="videoBitrateInput">Bitrate <small>(kbps)</small></label>
                                        <input id="videoBitrateInput" type="number" class="form-control constraintSelect" placeholder="Unlimited" 
                                               value="1500">
                                    </div>
                                </div>
                            </div>
                            <div id="links" class="tab-pane fade">
                                <ul class="list-group">
                                    <?php
                                    foreach ($links as $key => $value) {
                                        $btn = '';
                                        if ($key === 'hls') {
                                            $btn = '<a class="btn btn-xs btn-default" href="' . $global['webSiteRootURL'] . 'plugin/LiveLinks/play.php?url=' . urlencode($value) . '" target="_blank"><i class="fas fa-play"></i></a>';
                                        }
                                        echo '<li class="list-group-item"><strong>' . $key . ':</strong><br><small>' . $value . '</small> ' . $btn . '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-xs-3">
                                <strong>Actual Resolution
                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="This may be different from the resolution you set. Because the browser will get the ideal resolution from the input device."></i>:
                                </strong>
                                <span id="videoResolutionSpan" class="">-</span>
                            </div>
                            <div class="col-xs-3">
                                <strong>Actual Frame Rate: </strong>
                                <span id="videoFrameRateSpan" class="">-</span>
                            </div>
                            <div class="col-xs-3">
                                <strong>Bitrate(video): </strong>
                                <span id="bitrateSpan" class="">-</span>
                            </div>
                            <div class="col-xs-3">
                                <strong>State: </strong>
                                <span id="iceStateSpan" class="">-</span>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="col-xs-8">
                                <button id="streamingButton" type="button" class="btn btn-primary btn-block" disabled="disabled">Start Streaming</button>
                            </div>
                            <div class="col-xs-4">
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo $httpsWebSiteRootURL; ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $httpsWebSiteRootURL; ?>view/js/script.js" type="text/javascript"></script>
    <script src="<?php echo $httpsWebSiteRootURL; ?>view/js/js-cookie/js.cookie.js" type="text/javascript"></script>
    <script src="<?php echo $httpsWebSiteRootURL; ?>view/js/jquery-toast/jquery.toast.min.js" type="text/javascript"></script>
    <script src="<?php echo $httpsWebSiteRootURL; ?>view/js/seetalert/sweetalert.min.js" type="text/javascript"></script>
    <script src="<?php echo $webRTCServerCDNURL; ?>player/adapter.js?cache=<?php echo filemtime('adapter.js'); ?>"></script>
    <script src="<?php echo $webRTCServerCDNURL; ?>player/underscore-min.js?cache=<?php echo filemtime('underscore-min.js'); ?>"></script>
    <script src="<?php echo $webRTCServerCDNURL; ?>player/OvenWebRTCInput.js?cache=<?php echo filemtime('OvenWebRTCInput.js'); ?>"></script>
    <script src="<?php echo $webRTCServerCDNURL; ?>player/playerButtons.js?cache=<?php echo filemtime('playerButtons.js'); ?>"></script>
    <script>

            window.addEventListener('message', event => {
                if (event.data.setLiveStart) {
                    setLiveStart();
                } else if (event.data.setLiveStop) {
                    setLiveStop();
                } else if (event.data.setConfiguration) {
                    setConfiguration();
                } else if (event.data.startPushRTMP) {
                    startLive();
                } else if (event.data.stopPushRTMP) {
                    stopLive();
                }
            });

            function onStreamConnected() {
<?php
if (!empty($pushRTMP)) {
    ?>
                    console.log('Live will start with pushRTMP');
                    startLive();
    <?php
} else {
    if($localServer){
        $hls = $links['hls_local'];
    }else{
        $hls = $links['hls'];
    }
    ?>
                    console.log('Live will start with restream <?php echo $hls; ?>');
                    window.parent.postMessage({startLiveRestream: 1, m3u8: '<?php echo $hls; ?>'}, '*');
    <?php
}
?>

            }
            function setLiveStart() {
                startStreaming();
            }
            function setLiveStop() {
                stopStreaming();
            }

            function setConfiguration() {
                $('#webRTCModalConfig').modal({
                    show: true
                });
            }

            function startLive() {
                //console.log('WebRTCLiveCam: startLive');
                modal.showPleaseWait();
                window.parent.postMessage({showPleaseWait: 1}, '*');
                $.ajax({
                    url: WebRTCserver + '?command=start&webSiteRootURL=<?php echo urlencode($global['webSiteRootURL']); ?>',
                    method: 'POST',
                    data: {
                        'stream': stream,
                        'token': userHash
                    },
                    success: function (response) {
                        if (response.error) {
                            avideoAlertError(response.msg);
                            stopStreaming();
                        } else {
                            avideoToastSuccess(response.msg);
                        }
                        modal.hidePleaseWait();
                        window.parent.postMessage({hidePleaseWait: 1}, '*');
                    }
                });
            }

            function stopLive() {
                console.log('WebRTCLiveCam: stopLive');
                modal.showPleaseWait();
                $.ajax({
                    url: WebRTCserver + '?command=stop&webSiteRootURL=<?php echo urlencode($global['webSiteRootURL']); ?>',
                    method: 'POST',
                    data: {
                        'token': userHash
                    },
                    success: function (response) {
                        if (response.error) {
                            avideoAlertError(response.msg);
                        } else {
                            avideoToastSuccess(response.msg);
                        }
                        modal.hidePleaseWait();
                    }
                });
            }
    </script>
</body>
</html>