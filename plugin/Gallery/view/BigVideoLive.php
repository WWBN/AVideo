<?php
if ($obj->BigVideoLive->value == Gallery::BigVideoLiveDisabled) {
    return '';
}
if ($obj->BigVideoLive->value == Gallery::BigVideoLiveShowLiveOnly) {
    $liveVideo = Live::getLatest(true);
    if (empty($liveVideo)) {
        return '';
    }
}

?>
<style>
    #BigVideoLive {
        position: relative;
    }

    #BigVideoLiveClose {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 999999;
        padding: 10px;
        display: none;
    }

    #BigVideoLiveClose {
        opacity: 0.5;
    }

    #BigVideoLiveClose:hover {
        opacity: 1;
    }

    #BigVideoLive.fullscreen #BigVideoLiveClose {
        display: none;
    }

    #BigVideoLiveIFrame {
        min-height: 60vh;
        max-height: 90vh;
        width: 100%;
        background-color: rgba(100, 100, 100, 0.5);
    }

    #BigVideoLiveOverlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10;
        cursor: pointer;
    }

    #BigVideoLive.fullscreen,
    #BigVideoLive.fullscreen #BigVideoLiveIFrame {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 99999;
        max-height: 100vh;
    }

    #BigVideoLive.fullscreen #BigVideoLiveOverlay {
        display: none;
    }

    #BigVideoLive.fullscreen #BigVideoLiveClose {
        display: block;
    }
</style>
<div class="container-fluid" id="BigVideoLive">
    <div id="BigVideoLiveOverlay"></div>
    <iframe  id="BigVideoLiveIFrame" class="embed-responsive-item" scrolling="no" style="border: none;" <?php echo Video::$iframeAllowAttributes; ?> src="<?php echo "{$global['webSiteRootURL']}liveNow"; ?>"></iframe>
    <div id="BigVideoLiveClose">
        <button type="button" class="btn btn-default btn-circle" onclick="BigVideoLiveFullscreen(false);" style="padding: 3px 0;">
            <i class="fas fa-times fa-2x"></i>
        </button>
    </div>
</div>
<script>
    function BigVideoLiveFullscreen(makeFull) {
        if (makeFull) {
            $('#BigVideoLive').addClass('fullscreen');
        } else {
            $('#BigVideoLive').removeClass('fullscreen');
            var message = {
                type: 'userInactive'
            };
            $('#BigVideoLiveIFrame').get(0).contentWindow.postMessage(message, '*');
        }
    }
    $(document).ready(function() {
        $('#BigVideoLiveOverlay').click(function() {
            BigVideoLiveFullscreen(true);
        });
    });
</script>