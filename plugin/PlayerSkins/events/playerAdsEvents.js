setInterval(function () { fixAdSize(); }, 300);
player.on('adsready', function () {
    console.log('adsready');
    player.ima.setAdBreakReadyListener(function (e) {
        if (!_adWasPlayed) {
            console.log('ADs !_adWasPlayed player.ima.playAdBreak();', e);
            //player.ima.requestAds();
            player.on('play', function () {
                if (!_adWasPlayed) {
                    player.ima.playAdBreak();
                    _adWasPlayed = 1;
                }
            });
        } else {
            console.log('ADs _adWasPlayed player.ima.playAdBreak();', e);
            player.ima.playAdBreak();
        }
    });
});
player.on('ads-ad-started', function () {
    console.log('ADS: ads-ad-started');
});
player.on('ads-manager', function (a) {
    console.log('ADS: ads-manager', a);
});
player.on('ads-loader', function (a) {
    console.log('ADS: ads-loader', a);
});
player.on('ads-request', function (a) {
    console.log('ADS: ads-request', a);
});

// Event fired when an ad starts playing
player.on('adstart', function() {
    console.log('ADS: playback has started.');
});

// Event fired when an ad is paused
player.on('adpause', function() {
    console.log('ADS: playback has been paused.');
});

// Event fired when an ad is resumed
player.on('adresume', function() {
    console.log('ADS: playback has been resumed.');
});

// Event fired when an ad finishes playing
player.on('adend', function() {
    console.log('ADS: playback has finished.');
    player.play();
});

// Event fired when an ad is clicked
player.on('adclick', function() {
    console.log('ADS: was clicked.');
});

// Event fired if there's an error during ad playback
player.on('adserror', function(event) {
    console.log('ADS: error:', event.error);
});

player.one(startEvent, function () { player.ima.initializeAdDisplayContainer(); });