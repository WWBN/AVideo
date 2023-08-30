player.ready(function () {
    player.volume(0);
    player.muted(true);
    playerPlayMutedIfAutoPlay(currentTime);
});