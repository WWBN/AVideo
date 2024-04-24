this.tech_.el_.addEventListener('fullscreenchange', function (e) {
  if (isMobile() && this.player_.isFullscreen()) {
    this.player_.exitFullscreen();
  }
});

this.tech_.el_.addEventListener('dblclick', function (e) {
  e.preventDefault(); // Prevents double-click fullscreen on supported devices/browsers
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'F11') { // Disables the F11 key for fullscreen
    e.preventDefault();
  }
});