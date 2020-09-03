<?php
$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($objM)) {
    die("Plugin disabled");
}

$meetDomain = Meet::getDomain();
if (empty($meetDomain)) {
    header("Location: {$global['webSiteRootURL']}plugin/Meet/?error=The Server is Not ready");
    exit;
}

if ($meetDomain == 'custom') {
    $domain = $objM->CUSTOM_JITSI_DOMAIN;
} else {
    $domain = "{$meetDomain}?getRTMPLink=" . urlencode(Live::getRTMPLink());
}
?>
<a href="{$global['webSiteRootURL']}plugin/Meet/Meet/listener.js.php"></a>
<script>
    var api;
</script>
<style>

    #divMeetToIFrame {
        height: 100%;
        background: #000;
        min-height: 300px;
        min-width: 400px;
    }

</style>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/external_api.js" type="text/javascript"></script>
<span class=" pull-right" style="display: none;" id="meetButtons">
    <button class="btn btn-primary btn-xs showOnLive" id="stopRecording" style="display: none;" onclick="stopRecording()" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
    </button>
    <button class="btn btn-danger btn-xs showOnNoLive" id="startRecording" style="display: none;" onclick="startRecording()" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <?php echo __("Start"); ?>
    </button>
    <button class="btn btn-default btn-xs hideOnMeet" onclick="startMeetNow();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-camera"></i> <?php echo __("Webcam"); ?>/<?php echo __("Meet"); ?>
    </button>
    <input type="hidden" value="" id="meetLink"/>
    <input type="hidden" value="" id="meetPassword"/>
    <?php
    getButtontCopyToClipboard('meetLink', 'class="btn btn-default btn-sm btn-xs showOnMeet meetLink"', __("Copy Meet Link"));
    getButtontCopyToClipboard('avideoURL', 'class="btn btn-default btn-sm btn-xs showOnLive meetLink"', __("Copy Live Link"));
    if (Meet::isCustomJitsi() && User::isAdmin()) {
        ?>
        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/checkServers.php" class="btn btn-xs btn-default"
           data-toggle="tooltip" data-placement="bottom" title="You need to use one of our servers, your selfhosted jitsi will not work, you can disable this feature on Plugins->Live->disableMeetCamera">
            <i class="fas fa-exclamation-triangle"></i> Use our servers
        </a>
        <?php
    }
    ?>
</span>
<script>
    var meetPassword;
    var meetLink;

    var mainVideoElement;
    function startMeetNow() {
        modal.showPleaseWait();
        showMeet();
        $('#meetLink').val('');
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php',
            data: {RoomPasswordNew: Math.random().toString(36).substring(6), RoomTopic: $('#title').val(), public: 2},
            type: 'post',
            success: function (response) {
                if (response.error) {
                    swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    hideMeet();
                } else {
                    showMeet();
                    const domain = '<?php echo $domain; ?>';
                    const options = {
                        roomName: response.roomName,
                        jwt: response.jwt,
                        parentNode: document.querySelector('#divMeetToIFrame'),
                        userInfo: {
                            email: '<?php echo User::getEmail_(); ?>',
                            displayName: '<?php echo User::getNameIdentification(); ?>'
                        },
                        interfaceConfigOverwrite: {
                            TOOLBAR_BUTTONS: <?php echo json_encode(Meet::getButtons(0)); ?>,
                            //SET_FILMSTRIP_ENABLED: false,
                            //DISABLE_FOCUS_INDICATOR: true,
                            //DISABLE_DOMINANT_SPEAKER_INDICATOR: true,
                            //DISABLE_VIDEO_BACKGROUND: true,
                            DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                            SHOW_JITSI_WATERMARK: false,
                            SHOW_BRAND_WATERMARK: false,
                            disableAudioLevels: true,
                            requireDisplayName: true,
                            enableLayerSuspension: true,
                            channelLastN: 4,
                            startVideoMuted: 10,
                            startAudioMuted: 10,
                        }

                    };
                    api = new JitsiMeetExternalAPI(domain, options);

                    api.addEventListeners({
                        readyToClose: readyToClose,
                    });
                    meetPassword = response.password;
                    meetLink = response.link;
                    $('#meetLink').val(meetLink);
                }
                modal.hidePleaseWait();
            }
        });
    }

    function readyToClose() {
        api.dispose();
        hideMeet();
    }
    var mainVideoTagSRC;
    function showMeet() {
        userIsControling = true;
        $('.showOnMeet').show();
        $('.hideOnMeet').hide();
        $('#mainVideo').slideUp();
        mainVideoTagSRC = $('#mainVideo video').attr('src');
        $('#mainVideo video').attr('src', '');
        $('#mainVideo source').attr('src', '');
        $('#divMeetToIFrame').slideDown();
        showStopStart();
    }
    function hideMeet() {
        userIsControling = true;
        $('.showOnMeet').hide();
        $('.hideOnMeet').show();
        $('#mainVideo').slideDown();
        $('#mainVideo video').attr('src', mainVideoTagSRC);
        $('#mainVideo source').attr('src', '<?php echo Live::getM3U8File($trasnmition['key']); ?>');
        $('#divMeetToIFrame').slideUp();
        showStopStart();
    }
    function startRecording() {
        api.executeCommand('startRecording', {
            mode: 'stream',
            youtubeStreamKey: '<?php echo Live::getRTMPLink(); ?>',
        });
    }
    function stopRecording() {
        $.ajax({
            url: '<?php echo Live::getDropURL($trasnmition['key']); ?>',
            success: function (response) {}
        });
        api.executeCommand('stopRecording', 'stream');
    }
    function showStopStart() {
        if (typeof jitsiIsLive !== 'undefined' && $(".showOnMeet").is(":visible")) {
            if (jitsiIsLive) {
                $('.showOnLive').show();
                $('.showOnNoLive').hide();
            } else {
                $('.showOnLive').hide();
                $('.showOnNoLive').show();
            }
        } else {
            $('.showOnLive,.showOnNoLive').hide();
        }
    }
    $(document).ready(function () {
        hideMeet();
        setTimeout(function () {
            $('#meetButtons').fadeIn();
        }, 500);
        showStopStart();
        setTimeout(function () {
            showStopStart();
        }, 1000);

    });
</script>