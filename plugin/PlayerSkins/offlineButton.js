var Button = videojs.getComponent('Button');

var offlineButton = videojs.extend(Button, {
    //constructor: function(player, options) {
    constructor: function () {
        Button.apply(this, arguments);
        this.addClass('offline-button');
        this.controlText("offline");
    },
    handleClick: function () {
        console.log('offlineButton clicked');
        openDownloadOfflineVideoPage();
    }
});

videojs.registerComponent('offlineButton', offlineButton);
player.getChild('controlBar').addChild('offlineButton', {}, getPlayerButtonIndex('fullscreenToggle') - 1);

player.on('resolutionchange', function (event) {
    if(isOfflineSourceSelectedToPlay()){
        setOfflineButton('playingOffline', false);
    }else{
        setOfflineButton('readyToPlayOffline', false);
    }
});
