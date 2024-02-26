var Button = videojs.getComponent('Button');

class EPGButton extends Button {
    constructor() {
        super(...arguments);
        this.addClass('EPG-button');
        this.controlText("TV Guide");
        setTimeout(function(){avideoTooltip(".EPG-button","TV Guide");},1000);
    }

    handleClick() {
        var url = webSiteRootURL+'plugin/PlayerSkins/epg.php';
        url = addQueryStringParameter(url, 'videos_id', mediaId);
        console.log('epg clicked');
        avideoModalIframeFullTransparent(url);
    }
}

videojs.registerComponent('EPGButton', EPGButton);
player.getChild('controlBar').addChild('EPGButton', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
