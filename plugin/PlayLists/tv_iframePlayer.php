<style>
    #videoIFrame{
        width: 100vw;
        height: 100vh; 
        border:none;
        overflow:hidden;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #000;
    }
</style>
<iframe width="100%" height="100%" style="display: none;" 
        src="" 
        frameborder="0" allowfullscreen="allowfullscreen" allow="autoplay" scrolling="no" id="videoIFrame">iFrame is not supported!</iframe>

<script>
    var isIframe = true;
    function loadLiveVideoIframe(embedLink) {
        showChannelTop();
        if (isIframeOpened()) {
            console.log('loadLiveVideo::isIframeOpened', embedLink);
            if ($('#videoIFrame').attr('src') !== embedLink) {
                $('#videoIFrame').attr('src', embedLink);
            }
        } else {
            console.log('loadLiveVideo', embedLink);
            $('#videoIFrame').attr('src', '');
            $('#videoIFrame').fadeIn('slow', function () {
                $('#videoIFrame').attr('src', embedLink);
                $('body').addClass('showingIframe');
            });
            undoArray.push("closeLiveVideoIframe();");
        }
    }

    function closeLiveVideoIframe() {
        $('#channelTop').fadeOut('slow');
        $('#videoIFrame').fadeOut('slow', function () {
            $('#videoIFrame').attr('src', "");
            $('body').removeClass('showingIframe');
        });
    }

    function focusIframe() {
        $('#videoIFrame').focus();
        var iframe = $('#videoIFrame')[0];
        iframe.contentWindow.focus();
    }


    function isIframeOpened() {
        return $('body').hasClass('showingIframe');
    }

</script>