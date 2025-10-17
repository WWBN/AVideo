var startAudioSpectrumProgressInterval;
function startAudioSpectrumProgress(spectrumImage) {
    if($('#mainVideo .vjs-play-progress').length){
        $('#mainVideo .vjs-poster').append('<div id="avideo-audio-progress" style="display:none;"></div>');
        player.on('play', function () {
            $('#avideo-audio-progress').fadeIn();
            $('#mainVideo .vjs-poster').css('background-image', "url("+spectrumImage+")");
            $('#mainVideo .vjs-poster').css('background-size', "cover");
            //clearInterval(startAudioSpectrumProgressInterval);
            startAudioSpectrumProgressInterval = setInterval(function () {
                var style = $('#mainVideo .vjs-play-progress').attr('style');
                var percentage = style.replace("width:", "");
                $('#avideo-audio-progress').css('width', percentage.replace(";", ""));
            }, 100);

            // I need to add this because some versions of android chrome keep the error state even after play is pressed, so I cannot see the controls bar
            if ($(player.el()).hasClass('vjs-error')) {
                $(player.el()).removeClass('vjs-error');
                player.error(null);
            }

        });
        player.on('pause', function () {
            //clearInterval(startAudioSpectrumProgressInterval);
        });
    }else{
        setTimeout(function(){startAudioSpectrumProgress(spectrumImage);},500);
    }
}

function appendOnPlayer(element){
    if (typeof player !== 'undefined') {
        $(element).insertBefore($(player.el()).find('.vjs-control-bar'));
    } else {
        setTimeout(function () { appendOnPlayer(element); }, 1000);
    }
}
