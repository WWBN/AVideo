<?php
if ($obj->BigVideoLiveForLoggedUsersOnly) {
    if (!User::isLogged()) {
        echo '<!-- BigVideoLive is disabled for non-logged users -->';
        return '';
    }
}

if ($obj->BigVideoLive->value == Gallery::BigVideoLiveDisabled) {
    echo '<!-- BigVideoLive is disabled -->';
    return '';
}

if ($obj->BigVideoLiveOnFirstPageOnly && (!isFirstPage() || !empty($_GET['catName']) || !empty($_GET['showOnly']) || !empty($_GET['tags_id']))) {
    echo '<!-- BigVideoLive is disabled on this page -->';
    return '';
}

if ($obj->BigVideoLive->value == Gallery::BigVideoLiveShowLiveOnly) {
    $liveVideo = Live::getLatest(true);
    if (empty($liveVideo)) {
        echo '<!-- BigVideoLive is disabled because there is no live video -->';
        return '';
    }
}
$urlLiveNow = "{$global['webSiteRootURL']}liveNow?muted=1";
$urlLiveNow = addQueryStringParameter($urlLiveNow, 'muted', 1);
$urlLiveNow = addQueryStringParameter($urlLiveNow, 'isClosed', 1);
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
    <iframe id="BigVideoLiveIFrame" class="embed-responsive-item" scrolling="no" style="border: none;" <?php echo Video::$iframeAllowAttributes; ?> src="<?php echo $urlLiveNow; ?>"></iframe>
    <div id="BigVideoLiveClose">
        <button type="button" class="btn btn-default btn-circle" onclick="BigVideoLiveFullscreen(false);" style="padding: 3px 0;">
            <i class="fas fa-times fa-2x"></i>
        </button>
    </div>
</div>
<script>
    function sendPlayerMessage(msg) {
        var message = {
            type: msg
        };
        $('#BigVideoLiveIFrame').get(0).contentWindow.postMessage(message, '*');
    }

    function BigVideoLiveFullscreen(makeFull) {
        if (makeFull) {
            $('#BigVideoLive').addClass('fullscreen');
            sendPlayerMessage('playerUnmute');
            sendPlayerMessage('open');
        } else {
            $('#BigVideoLive').removeClass('fullscreen');
            sendPlayerMessage('userInactive');
            sendPlayerMessage('playerMute');
            sendPlayerMessage('close');
        }
    }
    $(document).ready(function() {
        $('#BigVideoLiveOverlay').click(function() {
            BigVideoLiveFullscreen(true);
        });
    });

    window.addEventListener('message', event => {
        switch (event.data.type) {
            case 'showBigVideo':
                $('#BigVideoLive').slideDown();
                break;
            case 'hideBigVideo':
                $('#BigVideoLive').slideUp();
                break;

            default:
                break;
        }
    });
</script>
