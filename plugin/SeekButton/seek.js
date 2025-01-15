player.seekButtons({forward: playerSeekForward, back: playerSeekBack});
var trackDisplayTimeout;
var showingSeekButton = true;
function startTrackDisplay() {
    if ($(".vjs-text-track-display").length === 0) {
        setTimeout(function () {
            startTrackDisplay();
        }, 500);
    }
    console.log("startTrackDisplay started");
    $(".vjs-text-track-display").css('pointerEvents', "auto");
    $(".vjs-text-track-display").dblclick(function (e) {
        e.preventDefault();
        console.log("dbl click happen " + trackDisplayTimeout);
        clearTimeout(trackDisplayTimeout);
        const playerWidth = $("#mainVideo").width();
        if (0.66 * playerWidth < e.offsetX) {
            $(forwardLayer).prependTo("#mainVideo");
            setTimeout(function () {
                $("#forwardLayer i").addClass('active');
                $('#forwardLayer').fadeOut('slow', function () {
                    $('#forwardLayer').remove();
                });
            }, 100);
            console.log('currentTime seek 1');
            player.currentTime(player.currentTime() + playerSeekForward);
        } else if (e.offsetX < 0.33 * playerWidth) {
            $(backLayer).prependTo("#mainVideo");
            setTimeout(function () {
                $("#backLayer i").addClass('active');
                $('#backLayer').fadeOut('slow', function () {
                    $('#backLayer').remove();
                });
            }, 100);
            console.log('currentTime seek 2');
            player.currentTime((player.currentTime() - playerSeekBack) < 0 ? 0 : (player.currentTime() - playerSeekBack));
        } else {
            if (player.paused()) {
                player.play();
            } else {
                console.log("playerPlay: player.pause() dblclick");
                player.pause();
            }
        }
    });
    $(".vjs-text-track-display").click(function (e) {
        e.preventDefault();
        console.log("single click happen");
        clearTimeout(trackDisplayTimeout);
        trackDisplayTimeout = setTimeout(function () {

            console.log("single click timeout");
            if (player.paused()) {
                player.play();
            } else {
                console.log("playerPlay: player.pause() click");
                player.pause();
            }
        }, 300);

        console.log("single click register " + trackDisplayTimeout);

    });
    $( "<div id='seekBG'></div>" ).insertBefore( ".vjs-text-track-display" );

}
startTrackDisplay();