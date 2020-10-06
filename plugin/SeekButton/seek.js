player.seekButtons({forward: playerSeekForward, back: playerSeekBack});
this.el_.querySelector(".vjs-text-track-display").style.pointerEvents = "auto"
document.querySelector(".vjs-text-track-display").addEventListener("dblclick", function (e) {
    const playerWidth = document.querySelector("#mainVideo").getBoundingClientRect().width;
    if (0.66 * playerWidth < e.offsetX) {
        $(forwardLayer).prependTo("#mainVideo");
        setTimeout(function () {
            $("#forwardLayer i").addClass('active');
            $('#forwardLayer').fadeOut('slow', function () {
                $('#forwardLayer').remove();
            });
        }, 100);
        player.currentTime(player.currentTime() + playerSeekForward);
    } else if (e.offsetX < 0.33 * playerWidth) {
        $(backLayer).prependTo("#mainVideo");
        setTimeout(function () {
            $("#backLayer i").addClass('active');
            $('#backLayer').fadeOut('slow', function () {
                $('#backLayer').remove();
            });
        }, 100);

        player.currentTime((player.currentTime() - playerSeekBack) < 0 ? 0 : (player.currentTime() - playerSeekBack));
    } else {
        if (player.paused()) {
            player.play();
        } else {
            player.pause();
        }
    }
});