var Button = videojs.getComponent('Button');

class SocialButton extends Button {
    constructor() {
        super(...arguments);
        this.addClass('social-button');
        this.controlText("social");
        setTimeout(function(){avideoTooltip(".social-button","Share");},1000);
    }

    handleClick() {
        console.log('socialButton clicked');
        togglePlayerSocial();
    }
}

videojs.registerComponent('SocialButton', SocialButton);

function addSocialButton() {
    if (typeof player !== 'undefined') {
        player.getChild('controlBar').addChild('SocialButton', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
    } else {
        setTimeout(() => { addSocialButton() }, 1000);
    }
}
addSocialButton();
