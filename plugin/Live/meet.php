<?php
$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($objM)) {
    die("Plugin disabled");
}

$meetDomain = Meet::getDomain();
if (empty($meetDomain)) {
    echo "<span class='label label-danger'>" . __("The server is not ready") . "</span>";
    return '';
}

if ($meetDomain == 'custom') {
    $domain = $objM->CUSTOM_JITSI_DOMAIN;
} else {
    $domain = "{$meetDomain}?getRTMPLink=" . urlencode(Live::getRTMPLink(User::getId()));
}

$dropURL = "{$global['webSiteRootURL']}plugin/Live/droplive.json.php?live_transmition_id={$trasnmition['id']}&live_servers_id=" . Live::getCurrentLiveServersId();
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
<?php
include $global['systemRootPath'] . 'plugin/Meet/api.js.php';
?>
<span class=" pull-right" style="display: none;" id="meetButtons">
    <button class="btn btn-danger btn-xs showOnLive hideOnProcessingLive hideOnMeetNotReady hideOnNoLive" id="stopRecording" style="display: none;" onclick="aVideoMeetStopRecording('<?php echo $dropURL; ?>')" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
    </button>
    <button class="btn btn-success btn-xs showOnNoLive hideOnProcessingLive hideOnMeetNotReady" id="startRecording" style="display: none;" onclick="aVideoMeetStartRecording('<?php echo Live::getRTMPLink(User::getId()); ?>', '<?php echo $dropURL; ?>');" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <?php echo __("Go Live"); ?>
    </button>
    <button class="btn btn-warning btn-xs showOnProcessingLive hideOnMeetNotReady" style="display: none;">
        <i class="fas fa-circle-notch fa-spin"></i> <?php echo __("Please Wait"); ?>
    </button>
    <button class="btn btn-default btn-xs hideOnMeetReady showOnMeetNotReady hideOnProcessingMeetReady" id="startMeet" onclick="startMeetNow();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-comments"></i> <?php echo __("Meet"); ?>
    </button>
    <button class="btn btn-warning btn-xs hideOnMeetReady showOnProcessingMeetReady" id="processMeet" style="display: none;" >
        <i class="fas fa-cog fa-spin"></i> <?php echo __("Please Wait"); ?>
    </button>
    <input type="hidden" value="" id="meetLink"/>
    <input type="hidden" value="" id="meetPassword"/>
    <?php
    getButtontCopyToClipboard('meetLink', 'class="btn btn-default btn-sm btn-xs showOnMeetReady hideOnMeetNotReady meetLink"', __("Copy Meet Link"));
    getButtontCopyToClipboard('meetPassword', 'class="btn btn-default btn-sm btn-xs showOnMeetReady hideOnMeetNotReady meetLink"', __("Copy Meet Password"));
    getButtontCopyToClipboard('avideoURL', 'class="btn btn-default btn-sm btn-xs  hideOnMeetNotReady showOnLive hideOnNoLive meetLink"', __("Copy Live Link"));
    if (Meet::isCustomJitsi() && User::isAdmin()) {
        ?>
        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/checkServers.php" class="btn btn-xs btn-default"
           data-toggle="tooltip" data-placement="bottom" title="You need to use one of our servers, your selfhosted jitsi will not work, you can disable this feature on Plugins->Live->disableMeetCamera">
            <i class="fas fa-exclamation-triangle"></i> <span class="hidden-sm hidden-xs">Use our servers</span>
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

    function event_on_liveStatusChange() {
        console.log("YPTMeetScript event_on_liveStatusChange");
        clearTimeout(setProcessingIsLiveTimeout);
        processingIsLive = false;
        showStopStart();
    }

    function event_on_meetReady() {
        console.log("YPTMeetScript event_on_meetReady");
        aVideoMeetHideElement(".watermark")
        meetIsReady = true;
        showMeet();
        on_meetReady();
    }
    function event_on_liveStop() {
        console.log("YPTMeetScript event_on_liveStop");
        jitsiIsLive = false;
        on_liveStop();
    }

    function event_on_live() {
        console.log("YPTMeetScript event_on_live");
        jitsiIsLive = true;
        on_live();
    }

    function _readyToClose() {
        api.dispose();
        hideMeet();
    }

    function startMeetNow() {
        modal.showPleaseWait();
        on_processingMeetReady();
        $('#meetLink').val('');
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/saveMeet.json.php',
            data: {RoomPasswordNew: Math.random().toString(36).substring(6), RoomTopic: $('#title').val(), public: 2},
            //data: {RoomTopic: $('#title').val(), public: 2},
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    on_meetStop();
                    modal.hidePleaseWait();
                } else {
                    aVideoMeetStart('<?php echo $domain; ?>', response.roomName, response.jwt, '<?php echo User::getEmail_(); ?>', '<?php echo addcslashes(User::getNameIdentification(), "'"); ?>', <?php echo json_encode(Meet::getButtons(0)); ?>);
                    
                    if (typeof hideWebcam == 'function') {
                        hideWebcam();
                    }
                    meetPassword = response.password;
                    $('#meetPassword').val(meetPassword);

                    meetLink = response.link;
                    $('#meetLink').val(meetLink);
<?php echo (Meet::isCustomJitsi() ? 'event_on_meetReady();$("#startRecording").hide();$("#stopRecording").hide();' : "") ?>
                }
            }
        });
    }
    
    function stopMeetNow() {
        $('#divMeetToIFrame iframe').remove();
        on_meetStop();
    }

    var showStopStartInterval;
    function on_meetReady() {
        modal.hidePleaseWait();
        $('.showOnMeetNotReady').hide();
        $('.showOnProcessingMeetReady').hide();
        $('.showOnMeetReady').show();
        clearInterval(showStopStartInterval);
        showStopStart();
    }

    function on_liveStop() {
        $('.showOnProcessingLive').hide();
        $('.showOnLive').hide();
        $('.showOnNoLive').show();
    }

    function on_meetStop() {
        clearInterval(showStopStartInterval);
        on_liveStop();
        $('.showOnMeetReady').hide();
        $('.showOnProcessingMeetReady').hide();
        $('.hideOnMeetNotReady').hide();
        $('.showOnMeetNotReady').show();
    }

    function on_processingMeetReady() {
        on_liveStop();
        $('.hideOnMeetNotReady').hide();
        $('.showOnMeetReady').hide();
        $('.showOnMeetNotReady').show();
        $('.hideOnProcessingMeetReady').hide();
        $('.showOnProcessingMeetReady').show();
    }

    function on_processingLive() {
        on_meetReady();
        $('.hideOnProcessingLive').hide();
        $('.showOnLive').hide();
        $('.hideOnNoLive').hide();
        $('.showOnNoLive').show();
        $("#startRecording").hide();
        $("#stopRecording").hide();
        $('.showOnProcessingLive').show();
    }

    function on_live() {
        on_meetReady();
        $('.showOnMeetNotReady').hide();
        $('.showOnProcessingMeetReady').hide();
        $('.showOnProcessingLive').hide();
        $('.showOnLive').show();
    }

    function showMeet() {
        userIsControling = true;
        on_processingMeetReady();
        $('#mainVideo').hide();
        $('#divMeetToIFrame').show();
        $('#divWebcamIFrame').hide();
        player.pause();
    }

    function hideMeet() {
        on_meetStop();
        $('#mainVideo').show();
        $('#divMeetToIFrame').hide();
        $('#divWebcamIFrame').hide();
    }

    var setProcessingIsLiveTimeout;
    function setProcessingIsLive() {
        clearTimeout(setProcessingIsLiveTimeout);
        processingIsLive = true;
        setProcessingIsLiveTimeout = setTimeout(function () {
            processingIsLive = false;
        }, 30000);
    }

    function showStopStart() {
        if (!processingIsLive) {
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