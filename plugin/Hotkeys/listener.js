
window.addEventListener('message', function(event) {
    var message = event.data;
    console.log('key received', message.command);
    switch(message.command) {
        case 'playPause':
            if (player.paused()) {
                player.play();
            } else {
                player.pause();
            }
            break;
        case 'rewind':
            console.log('currentTime hot key rewind');
            player.currentTime(player.currentTime() - 10);
            break;
        case 'forward':
            console.log('currentTime hot key forward');
            player.currentTime(player.currentTime() + 10);
            break;
        case 'volumeUp':
            player.volume(Math.min(player.volume() + 0.1, 1));
            break;
        case 'volumeDown':
            player.volume(Math.max(player.volume() - 0.1, 0));
            break;
    }
});
