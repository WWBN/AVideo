$(document).ready(function () {

    var Button = videojs.getComponent('Button');

    class Logo extends Button {
        constructor() {
            super(...arguments);
            this.addClass('player-logo');
            this.controlText(PlayerSkinLogoTitle);
        }

        handleClick() {
            window.open(webSiteRootURL, '_blank');
        }
    }

    videojs.registerComponent('Logo', Logo);
    if (player.getChild('controlBar').getChild('PictureInPictureToggle')) {
        player.getChild('controlBar').addChild('Logo', {}, getPlayerButtonIndex('PictureInPictureToggle') + 1);
    } else {
        player.getChild('controlBar').addChild('Logo', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
    }
});
