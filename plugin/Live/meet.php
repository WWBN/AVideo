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
    <button class="btn btn-primary btn-xs showOnMeet" onclick="stopRecording()" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
    </button>
    <button class="btn btn-danger btn-xs showOnMeet" onclick="startRecording()" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <?php echo __("Start"); ?>
    </button>
    <button class="btn btn-default btn-xs hideOnMeet" onclick="startMeetNow();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-camera"></i> <?php echo __("Webcam"); ?>/<?php echo __("Meet"); ?>
    </button>
    <input type="hidden" value="" id="meetLink"/>
    <input type="hidden" value="" id="meetPassword"/>
    <?php
    getButtontCopyToClipboard('meetLink','class="btn btn-default btn-sm btn-xs showOnMeet"', __("Copy Meet Link"));
    getButtontCopyToClipboard('meetPassword','class="btn btn-default btn-sm btn-xs showOnMeet"', __("Copy Meet Password"));
    if(Meet::isCustomJitsi() && User::isAdmin()){
        ?><i class="fas fa-exclamation-triangle" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("A custom Jitsi may not work"); ?>"></i><?php
    }
    ?>
</span>
<script>
    function startMeetNow() {
        modal.showPleaseWait();
        showMeet();
        $('.meetPassword').text('');
        $('#meetPassword').val('');
        $('#meetLink').val('');
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php',
            data: {RoomPasswordNew: Math.random().toString(36).substring(6), RoomTopic: $('#title').val()},
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

                    api.executeCommand('password', response.password);
                    
                    $('#meetPassword').val(response.password);
                    $('#meetLink').val(response.link);
                }
                modal.hidePleaseWait();
            }
        });
    }

    function readyToClose() {
        api.dispose();
        hideMeet();
    }

    function showMeet() {
        $('.meetPassword').fadeIn();
        $('#meetLink').fadeIn();
        $('#meetPassword').fadeIn();
        $('.showOnMeet').fadeIn();
        $('.hideOnMeet').fadeOut();
        $('#mainVideo').slideUp();
        $('#divMeetToIFrame').slideDown();
    }

    function hideMeet() {
        $('.meetPassword').fadeOut();
        $('#meetLink').fadeOut();
        $('#meetPassword').fadeOut();
        $('.showOnMeet').fadeOut();
        $('.hideOnMeet').fadeIn();
        $('#mainVideo').slideDown();
        $('#divMeetToIFrame').slideUp();
    }
    function startRecording() {
        api.executeCommand('startRecording', {
            mode: 'stream',
            youtubeStreamKey: '<?php echo Live::getRTMPLink(); ?>',
        });
    }
    function stopRecording() {
        api.executeCommand('stopRecording', {mode: 'stream'});
    }
$(document).ready(function () {
    hideMeet();
    setTimeout(function (){
        $('#meetButtons').fadeIn();
    },500);
    
});
</script>