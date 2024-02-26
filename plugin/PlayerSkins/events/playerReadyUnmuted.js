player.ready(function () {
    playerPlayIfAutoPlay(currentTime);
    player.persistvolume({ namespace: 'AVideo' });
});