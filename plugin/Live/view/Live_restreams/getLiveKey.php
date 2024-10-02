<?php
$restreamerURL = 'http://localhost/Restreamer/';
$restreamerURL = 'http://127.0.0.1/Restreamer/';
if (empty($global['local_test_server'])) {
    $restreamerURL = 'https://restream.ypt.me/';
}

if (!Live::canRestream()) {
    return false;
}
?>
<div class="social-network" id="LiveKeysDivs">
    <button type="button" class="btn btn-default icoFacebook" onclick="openRestream('facebook')">
        <i class="fab fa-facebook-f mediumSocialIcon"></i><br>
        Facebook
    </button>
    <button type="button" class="btn btn-default icoYoutube " onclick="openRestream('youtube')">
        <i class="fab fa-youtube mediumSocialIcon"></i><br>
        YouTube
    </button>
    <button type="button" class="btn btn-default icoTwitch" onclick="openRestream('twitch')">
        <i class="fab fa-twitch mediumSocialIcon"></i><br>
        Twitch
    </button>
</div>
<!--
<button type="button" class="btn btn-default" onclick="openRestream('')">
    <i class="fas fa-cog"></i>
    Open restream
</button>
-->
<script>
    var restreamPopupOpened = false;
    // Create browser compatible event handler.
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
    // Listen for a message from the iframe.
    eventer(messageEvent, function(e) {
        console.log('EventListener restreamer', e.data);
        if (e.data.stream_key && e.data.name) {
            saveRestreamer(e.data.stream_key, e.data.stream_url, e.data.name, e.data.parameters);
        }
    }, false);

    var restreamWin;

    function openRestream(provider) {
        restreamPopupOpened = 1;
        modal.showPleaseWait();
        $('#newLive_restreamsLink').trigger("click");
        var url = "<?php echo $restreamerURL; ?>confirm/" + provider;
        var name = "theRestreamerPopUp";
        var params = {
            title: $('#title').val(),
            description: $('#description').val()
        };
        var strWindowFeatures = "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,resizable=no,height=600,width=800";
        restreamWin = openWindowWithPost(url, name, params, strWindowFeatures);
        var pollTimer = window.setInterval(function() {
            if (restreamWin.closed !== false) { // !== is required for compatibility with Opera
                window.clearInterval(pollTimer);
                modal.hidePleaseWait();
                restreamPopupOpened = 0;
                //avideoToast('closed');
            }
        }, 200);
    }

    function saveRestreamer(stream_key, stream_url, name, parameters) {
        console.log('saveRestreamer', stream_key, stream_url, name, parameters);
        restreamPopupOpened = 0;
        modal.hidePleaseWait();
        if (empty(stream_url)) {
            avideoAlertError(stream_key);
        } else {
            $('#Live_restreamsname').val(name);
            $('#Live_restreamsstatus').val('a');
            $('#Live_restreamsstream_url').val(stream_url);
            $('#Live_restreamsstream_key').val(stream_key);
            $('#Live_restreamsparameters').val(parameters);
            $('#panelLive_restreamsForm').submit();
        }
        restreamWin.close();
    }
</script>