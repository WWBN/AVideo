player.on('fullscreenchange', function(e, data) {
  console.log('player fullscreenchange isFullscreen', player.isFullscreen());
  player.exitFullscreen();
});
