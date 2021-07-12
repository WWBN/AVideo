<?php
// this script will be executed on the AVideo side
$meetPlugin = AVideoPlugin::getDataObjectIfEnabled("Meet");
if (empty($meetPlugin)) {
    return false;
}

$rtmpLink = "";
$livePlugin = AVideoPlugin::getDataObjectIfEnabled("Live");
if (!empty($livePlugin) && User::canStream()) {
    $trasnmition = LiveTransmition::createTransmitionIfNeed(User::getId());
    $dropURL = "{$global['webSiteRootURL']}plugin/Live/droplive.json.php?live_transmition_id={$trasnmition['id']}&live_servers_id=" . Live::getCurrentLiveServersId();
    $rtmpLink = Live::getRTMPLink(User::getId());
}

if (empty($meet_schedule_id)) {
    $meet_schedule_id = 0;
} else {
    $meet_schedule_id = intval($meet_schedule_id);
}
?>
<script src="<?php echo getCDN(); ?>plugin/Meet/external_api.js" type="text/javascript"></script>
<script src="<?php echo getCDN(); ?>view/js/seetalert/sweetalert.min.js" type="text/javascript"></script>
<script>
    var webSiteRootURL = "<?php echo $global['webSiteRootURL']; ?>";
    var webSiteTitle = "<?php echo $config->getWebSiteTitle(); ?>";
    var lastLiveStatus;
    var eventMethod = window.addEventListener
            ? "addEventListener"
            : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod === "attachEvent"
            ? "onmessage"
            : "message";
    eventer(messageEvent, function (e) {
        if (typeof e.data.isLive !== 'undefined') {
            if (lastLiveStatus !== e.data.isLive) {
                lastLiveStatus = e.data.isLive;
                console.log("YPTMeetScript live status changed");
                if (lastLiveStatus) {
                    if (typeof event_on_live !== "undefined") {
                        event_on_live();
                    }
                } else {
                    if (typeof event_on_liveStop !== "undefined") {
                        event_on_liveStop();
                    }
                }
                if (typeof event_on_liveStatusChange !== "undefined") {
                    event_on_liveStatusChange();
                }
            }
        } else if (typeof e.data.YPTisReady !== 'undefined') {
            if (typeof event_on_meetReady !== "undefined") {
                event_on_meetReady();
            }
            console.log("YPTMeetScript is loaded");
        } else if (typeof e.data.conferenceIsReady !== 'undefined') {
            if (typeof event_on_meetReady !== "undefined") {
                event_on_meetReady();
            }
            aVideoMeetCreateButtons();
            <?php
            $css = file_get_contents($global['systemRootPath'] . 'plugin/Meet/meet.mobile.css');
            if(!isMobile()){
                $css = "@media (max-width: 767px) {{$css}}";
            }
            $css .= file_get_contents($global['systemRootPath'] . 'plugin/Meet/meet.css');
            ?>
            aVideoMeetAppendElement("head", <?php echo json_encode("<style>{$css}</style>"); ?>);
            console.log("YPTMeetScript conference is ready");
        } else if (typeof e.data.aVideoMeetStartRecording !== 'undefined') {
            console.log("YPTMeetScript aVideoMeetStartRecording");
            aVideoMeetStartRecording(e.data.aVideoMeetStartRecording.RTMPLink, e.data.aVideoMeetStartRecording.dropURL);
        } else if (typeof e.data.aVideoMeetStopRecording !== 'undefined') {
            console.log("YPTMeetScript aVideoMeetStopRecording");
            aVideoMeetStopRecording(e.data.aVideoMeetStopRecording.dropURL);
        }
    });

    function aVideoMeetZoom(zoom) {
        document.querySelector("iframe").contentWindow.postMessage({zoom: zoom}, "*");
    }

    function getMeetDisplayName(domain, roomName, jwt, email, TOOLBAR_BUTTONS) {
        console.log('getMeetDisplayName');
        swal({
            text: "<?php echo __("Please, enter your name"); ?>",
            content: "input",
            button: {
                text: "<?php echo __("Start Now"); ?>",
                closeModal: true,
            },
        }).then(function (displayName) {
            displayName = displayName.trim();
            if (!displayName || /^$|^\s+$/.test(displayName)) {
                //avideoAlertError('<?php echo __("You must provide a name"); ?>');
                return getMeetDisplayName(domain, roomName, jwt, email, TOOLBAR_BUTTONS);
            } else {
                return aVideoMeetStart(domain, roomName, jwt, email, displayName, TOOLBAR_BUTTONS);
            }
        });
        return false;
    }

    var api;
    function aVideoMeetStart(domain, roomName, jwt, email, displayName, TOOLBAR_BUTTONS) {

        if (!displayName || displayName == '') {
            displayName = getMeetDisplayName();
            return getMeetDisplayName(domain, roomName, jwt, email, TOOLBAR_BUTTONS);
        }

        const options = {
            roomName: roomName,
            jwt: jwt,
            parentNode: document.querySelector('#divMeetToIFrame'),
            userInfo: {
                email: email,
                displayName: displayName
            },
            ConfigOverwrite: {
                disableDeepLinking: true,
                disableInviteFunctions: true,
                openBridgeChannel: 'websocket'
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: TOOLBAR_BUTTONS,
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                MOBILE_APP_PROMO: false,
                HIDE_INVITE_MORE_HEADER: true,
                //disableAudioLevels: true,
                requireDisplayName: true,
                enableLayerSuspension: true,
                channelLastN: 4,
                startVideoMuted: 10,
                startAudioMuted: 10,
                disableInviteFunctions: true,
                DEFAULT_LOGO_URL: webSiteRootURL + "videos/userPhoto/logo.png",
                DEFAULT_REMOTE_DISPLAY_NAME: webSiteTitle,
                JITSI_WATERMARK_LINK: webSiteRootURL,
                LIVE_STREAMING_HELP_LINK: webSiteRootURL,
                PROVIDER_NAME: webSiteTitle,
                SUPPORT_URL: webSiteRootURL,
                BRAND_WATERMARK_LINK: webSiteRootURL,
                NATIVE_APP_NAME: webSiteTitle,
                APP_NAME: webSiteTitle

            }

        };
        api = new JitsiMeetExternalAPI(domain, options);

        const iframe = api.getIFrame();

        var src = $(iframe).attr('src');
        var srcParts = src.split("#");
        var newSRC = srcParts[0] + "&getRTMPLink=<?php echo urlencode($rtmpLink); ?>#" + srcParts[1];

        $(iframe).attr('src', newSRC);

        api.addEventListeners({
            readyToClose: readyToClose,
        });

    }

    function aVideoMeetStartRecording(RTMPLink, dropURL) {
        if (typeof on_processingLive === 'function') {
            on_processingLive();
        }
        if (dropURL) {
            $.ajax({
                url: dropURL,
                success: function (response) {
                    console.log("YPTMeetScript Start Recording Drop");
                    console.log(response);
                }
            }).always(function (dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
                api.executeCommand('startRecording', {
                    mode: 'stream',
                    youtubeStreamKey: RTMPLink,
                });
            });
        } else {
            api.executeCommand('startRecording', {
                mode: 'stream',
                youtubeStreamKey: RTMPLink,
            });
        }
    }

    function aVideoMeetStopRecording(dropURL) {
        if (typeof on_processingLive === 'function') {
            on_processingLive();
        }
        api.executeCommand('stopRecording', 'stream');
        if (dropURL) {
            setTimeout(function () { // if I run the drop on the same time, the stopRecording fails
                $.ajax({
                    url: dropURL,
                    success: function (response) {
                        console.log("YPTMeetScript Stop Recording Drop");
                        console.log(response);
                    }
                });
            }, 5000);
        }

    }

    function aVideoMeetConferenceIsReady() {

    }

    function aVideoMeetHideElement(selectors) {
        document.querySelector("iframe").contentWindow.postMessage({hideElement: selectors}, "*");
    }

    function aVideoMeetAppendElement(parentSelector, html) {
        var append = {parentSelector: parentSelector, html: html};
        document.querySelector("iframe").contentWindow.postMessage({append: append}, "*");
    }

    function aVideoMeetPrependElement(parentSelector, html) {
        var prepend = {parentSelector: parentSelector, html: html};
        document.querySelector("iframe").contentWindow.postMessage({prepend: prepend}, "*");
    }

    function aVideoMeetCreateButtons() {
<?php
if (!empty($rtmpLink) && Meet::isModerator($meet_schedule_id)) {
    ?>
            aVideoMeetAppendElement(".button-group-center", <?php echo json_encode(Meet::createJitsiRecordStartStopButton($rtmpLink, $dropURL)); ?>);
    <?php
}
?>
    }

    function readyToClose() {
        window.parent.postMessage({"meetIsClosed": true}, "*");
        if (typeof _readyToClose == "function") {
            _readyToClose();
        }
    }
</script>