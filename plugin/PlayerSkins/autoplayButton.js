var Button = videojs.getComponent('Button');

class AutoplayButton extends Button {
    constructor() {
        super(...arguments);
        this.addClass('autoplay-button');
        this.controlText("autoplay");
        setTimeout(function(){avideoTooltip(".autoplay-button","Autoplay");},1000);
    }
    handleClick() {
        console.log('autoplayButton clicked');
        if($('.autoplay-button').hasClass('checked')){
            disableAutoPlay();
        }else{
            enableAutoPlay();
        }
    }
}

videojs.registerComponent('AutoplayButton', AutoplayButton);
player.getChild('controlBar').addChild('AutoplayButton', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
checkAutoPlay();
