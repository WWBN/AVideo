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
<?php
include $global['systemRootPath'] . 'plugin/Meet/listener.js.php';
?>
<span class=" pull-right" style="display: none;" id="meetButtons">
    <button class="btn btn-danger btn-xs showOnLive hideOnProcessingLive hideOnMeetNotReady showOnLive hideOnNoLive" id="stopRecording" style="display: none;" onclick="stopRecording()" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
    </button>
    <button class="btn btn-success btn-xs showOnNoLive hideOnProcessingLive hideOnMeetNotReady" id="startRecording" style="display: none;" onclick="startRecording()" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <?php echo __("Start"); ?>
    </button>
    <button class="btn btn-warning btn-xs showOnProcessingLive hideOnMeetNotReady" style="display: none;">
        <i class="fas fa-circle-notch fa-spin"></i> <?php echo __("Please Wait"); ?>
    </button>
    <button class="btn btn-default btn-xs hideOnMeetReady showOnMeetNotReady hideOnProcessingMeetReady" id="startMeet" onclick="startMeetNow();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-camera"></i> <?php echo __("Webcam"); ?>/<?php echo __("Meet"); ?>
    </button>
    <button class="btn btn-warning btn-xs hideOnMeetReady showOnProcessingMeetReady" id="processMeet" style="display: none;" >
        <i class="fas fa-cog fa-spin"></i> <?php echo __("Please Wait"); ?>
    </button>
    <input type="hidden" value="" id="meetLink"/>
    <input type="hidden" value="" id="meetPassword"/>
    <?php
    getButtontCopyToClipboard('meetLink', 'class="btn btn-default btn-sm btn-xs showOnMeetReady hideOnMeetNotReady meetLink"', __("Copy Meet Link"));
    getButtontCopyToClipboard('avideoURL', 'class="btn btn-default btn-sm btn-xs  hideOnMeetNotReady showOnLive hideOnNoLive meetLink"', __("Copy Live Link"));
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
    var meetIsReady = false;
    var jitsiIsLive = false;
    var processingIsLive = false;
    var mainVideoElement;
    function startMeetNow() {
        modal.showPleaseWait();
        on_processingMeetReady();
        $('#meetLink').val('');
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php',
            data: {RoomPasswordNew: Math.random().toString(36).substring(6), RoomTopic: $('#title').val(), public: 2},
            type: 'post',
            success: function (response) {
                if (response.error) {
                    swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    on_meetStop();
                    modal.hidePleaseWait();
                } else {
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
            }
        });
    }

    function readyToClose() {
        api.dispose();
        hideMeet();
    }
    
    function event_on_liveStatusChange(){
        clearTimeout(setProcessingIsLiveTimeout);
        processingIsLive = false;
        showStopStart();
    }
    
    var showStopStartInterval;
    function on_meetReady(){
        modal.hidePleaseWait();
        $('.showOnMeetNotReady').hide();
        $('.showOnProcessingMeetReady').hide();
        $('.showOnMeetReady').show();
        clearInterval(showStopStartInterval);
        showStopStart();
    }   
    
    function event_on_meetReady(){
        document.querySelector("iframe").contentWindow.postMessage({hideElement: ".watermark, .toolbox-button-wth-dialog"},"*");
        meetIsReady = true;
        showMeet();
        on_meetReady();
    }
    
    function on_liveStop(){
        $('.showOnProcessingLive').hide();
        $('.showOnLive').hide();
        $('.showOnNoLive').show();
    }   
    function event_on_liveStop(){
        jitsiIsLive = false;
        on_liveStop();
    }   
    
    function on_meetStop(){
        clearInterval(showStopStartInterval);
        on_liveStop();
        $('.showOnMeetReady').hide();
        $('.showOnProcessingMeetReady').hide();
        $('.hideOnMeetNotReady').hide();
        $('.showOnMeetNotReady').show();
    }   
    
    function on_processingMeetReady(){
        on_liveStop();
        $('.hideOnMeetNotReady').hide();
        $('.showOnMeetReady').hide();
        $('.showOnMeetNotReady').show();
        $('.hideOnProcessingMeetReady').hide();
        $('.showOnProcessingMeetReady').show();
    }   
    
    function on_processingLive(){
        on_meetReady();
        $("#startRecording").hide();
        $("#stopRecording").hide();
        $('.hideOnProcessingLive').hide();
        $('.showOnLive').hide();
        $('.hideOnNoLive').hide();
        $('.showOnNoLive').show();
        $('.showOnProcessingLive').show();
    }   
    
    function on_live(){
        on_meetReady();
        $('.showOnMeetNotReady').hide();
        $('.showOnProcessingMeetReady').hide();
        $('.showOnMeetReady').show();
    }   
    
    function event_on_live(){
        jitsiIsLive = true;
        on_live();
    }
    
    function showMeet() {
        userIsControling = true;
        on_processingMeetReady();
        $('#mainVideo').slideUp();
        $('#divMeetToIFrame').slideDown();
        player.pause();
    }
    function hideMeet() {
        on_meetStop();
        $('#mainVideo').slideDown();
        $('#divMeetToIFrame').slideUp();
    }
    function startRecording() {
        on_processingLive();
        api.executeCommand('startRecording', {
            mode: 'stream',
            youtubeStreamKey: '<?php echo Live::getRTMPLink(); ?>',
        });
    }
    function stopRecording() {
        on_processingLive();
        on_liveStop();
        $.ajax({
            url: '<?php echo Live::getDropURL($trasnmition['key']); ?>',
            success: function (response) {}
        });
        api.executeCommand('stopRecording', 'stream');
    }    
    
    var setProcessingIsLiveTimeout;
    function setProcessingIsLive(){
        clearTimeout(setProcessingIsLiveTimeout);
        processingIsLive = true;
        setProcessingIsLiveTimeout = setTimeout(function(){processingIsLive = false;},30000);
    }
    
    function showStopStart() {
        if(!processingIsLive){
            if (typeof jitsiIsLive !== 'undefined' && typeof meetIsReady !== 'undefined' && meetIsReady) {
                if (jitsiIsLive) {
                    $("#startRecording").hide();
                    $("#stopRecording").show();
                } else {
                    $("#startRecording").show();
                    $("#stopRecording").hide();
                }
            } else {
                $("#startRecording").hide();
                $("#stopRecording").hide();
            }
        }
    }
    $(document).ready(function () {
        $('#meetButtons').fadeIn();
        $('.showOnMeetReady').hide();
        hideMeet();
    });
</script>