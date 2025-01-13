<?php
require_once __DIR__ . '/../../videos/configuration.php';
require_once __DIR__ . '/functions.php';
$_page = new Page(array('WebRTC Server Status'));
?>
<link href="<?php echo getURL('plugin/WebRTC/style.css'); ?>" rel="stylesheet" type="text/css" />
<script class="doNotSepareteTag">
    var WebRTC2RTMPURL = '<?php echo getWebRTC2RTMPURL(); ?>';
</script>
<style>
    .premium-enabled {
        color: green;
        font-weight: bold;
    }

    .premium-disabled {
        color: red;
        font-weight: bold;
    }

    .status-good {
        color: green;
        font-weight: bold;
    }

    .status-bad {
        color: red;
        font-weight: bold;
    }
</style>
<div class="container-fluid">
    <div id="status" class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-sm-3">
                    <p><i class="fa fa-plug"></i> <strong>Port:</strong> <span class="port">Loading...</span></p>
                </div>
                <div class="col-sm-3">
                    <p><i class="fa fa-globe"></i> <strong>Domain:</strong> <span class="domain">Loading...</span></p>
                </div>
                <div class="col-sm-3">
                    <p><i class="fa fa-code"></i> <strong>Version:</strong> <span class="version">Loading...</span></p>
                </div>
                <div class="col-sm-3">
                    <p><i class="fa-regular fa-clock"></i> <strong>Active State:</strong> <span class="active-state">Loading...</span></p>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-3">
                    <p><i class="fa fa-hourglass-half"></i> <strong>Max RTMP Runtime:</strong> <span class="max-runtime">Loading...</span> minutes</p>
                </div>
                <div class="col-sm-3">
                    <p><i class="fa fa-tasks"></i> <strong>Max Concurrent RTMP:</strong> <span class="max-concurrent">Loading...</span> processes</p>
                </div>
                <div class="col-sm-3">
                    <p><i class="fa fa-network-wired"></i> <strong>Port Status:</strong> <span class="port-status">Loading...</span></p>
                </div>
                <div class="col-sm-3">
                    <p><i class="fa fa-file"></i> <strong>Server File Permission:</strong> <span class="file-status">Loading...</span></p>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6">
                    <p><i class="fa fa-star"></i> <strong>Premium Features:</strong> <span class="premium-status">Loading...</span></p>
                </div>
                <div class="col-sm-6">
                    <p class="showWhenWebRTCIsConnected ">
                        <span class="liveIndicator" style="padding: 2px 5px;">
                            <i class="fa fa-check-circle"></i> Server Connected
                        </span>
                    </p>
                    <p class="showWhenWebRTCIsNotConnected ">
                        <span class="offLineIndicator" style="padding: 2px 5px;">
                            <i class="fa fa-times-circle"></i> Server Disconnected
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <pre id="log" style="max-height: 300px; overflow-y: auto;">Loading logs...</pre>
        </div>
    </div>
    <div id="premium-offer" class="panel panel-warning" style="display: none;">
        <div class="panel-heading">
            <h4><i class="fa fa-star"></i> Upgrade to Premium</h4>
        </div>
        <div class="panel-body">
            <p>Remove the watermark from your livestreams and enjoy increased limits</p>
            <button id="upgrade-button" class="btn btn-success"><i class="fa fa-arrow-up"></i> Upgrade to Premium</button>
        </div>
    </div>
</div>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/api.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/WebRTC/events.js'); ?>" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        // Fetch the JSON data
        $.getJSON(webSiteRootURL + "plugin/WebRTC/status.json.php", function (data) {
            if (data.error) {
                $("#status").html('<div class="panel panel-danger"><div class="panel-heading"><strong>Error:</strong></div><div class="panel-body">Unable to fetch server data.</div></div>');
                return;
            }

            // Update elements by class
            $(".port").text(data.json.serverPort);
            $(".domain").text(data.json.domain);
            $(".version").text(data.json.version);
            $(".active-state").text(new Date(data.json.phpTimestamp * 1000).toLocaleString());
            $(".max-runtime").text(data.json.maxRtmpRuntimeMinutes);
            $(".max-concurrent").text(data.json.maxConcurrentRtmp);
            $(".premium-status").text(data.json.isPremium ? 'Enabled' : 'Disabled')
                .addClass(data.json.isPremium ? 'premium-enabled' : 'premium-disabled');

            // Check port status with detailed icons and messages
            let portStatusMessage = "";
            if (data.portOpenInternally && data.portOpenExternally) {
                portStatusMessage = '<span class="status-good"><i class="fa fa-check-circle"></i> Open (Internal and External)</span>';
            } else if (data.portOpenInternally) {
                portStatusMessage = '<span class="status-bad"><i class="fa fa-times-circle"></i> Open (Internal Only)</span>';
            } else if (data.portOpenExternally) {
                portStatusMessage = '<span class="status-bad"><i class="fa fa-times-circle"></i> Open (External Only)</span>';
            } else {
                portStatusMessage = '<span class="status-bad"><i class="fa fa-times-circle"></i> Closed</span>';
            }
            $(".port-status").html(portStatusMessage);

            // Check file executable status with icons and messages
            const fileStatusMessage = data.is_executable
                ? '<span class="status-good"><i class="fa fa-check-circle"></i> File is executable.</span>'
                : '<span class="status-bad"><i class="fa fa-times-circle"></i> File is not executable. Please check permissions manually.</span>';
            $(".file-status").html(fileStatusMessage);

            // Show the premium offer if the user is not premium
            if (!data.json.isPremium) {
                $("#premium-offer").show();
            }

            // Populate the server logs
            $("#log").text(data.log);
        });

        // Handle the upgrade button
        $("#upgrade-button").click(function () {
            alert("Redirecting to the premium subscription page...");
            window.location.href = "https://youphp.tube/marketplace/WebRTC2RTMP/";
        });
    });
</script>

<?php

$_page->print();
?>
