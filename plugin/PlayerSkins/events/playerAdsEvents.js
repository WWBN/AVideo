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
    console.log('ads-ad-started');
});
player.on('ads-manager', function (a) {
    console.log('ads-manager', a);
});
player.on('ads-loader', function (a) {
    console.log('ads-loader', a);
});
player.on('ads-request', function (a) {
    console.log('ads-request', a);
});
player.one(startEvent, function () { player.ima.initializeAdDisplayContainer(); });