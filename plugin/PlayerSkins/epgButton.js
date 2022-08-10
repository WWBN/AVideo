var Button = videojs.getComponent('Button');

var EPGButton = videojs.extend(Button, {
    //constructor: function(player, options) {
    constructor: function () {
        Button.apply(this, arguments);
        this.addClass('EPG-button');
        this.controlText("EPG");
        setTimeout(function(){avideoTooltip(".EPG-button","EPG");},1000);
    },
    handleClick: function () {
        var url = webSiteRootURL+'plugin/PlayerSkins/epg.php';
        url = addQueryStringParameter(url, 'videos_id', mediaId);
        console.log('epg clicked');
        avideoModalIframeFullTransparent(url);
    }
});

videojs.registerComponent('EPGButton', EPGButton);
player.getChild('controlBar').addChild('EPGButton', {}, getPlayerButtonIndex('fullscreenToggle') - 1);