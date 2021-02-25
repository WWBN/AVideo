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
        });
        player.on('pause', function () {
            //clearInterval(startAudioSpectrumProgressInterval);
        });
    }else{
        setTimeout(function(){startAudioSpectrumProgress(spectrumImage);},500);
    }
}