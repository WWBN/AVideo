$(document).ready(function () {

    var Button = videojs.getComponent('Button');

    var Logo = videojs.extend(Button, {
        //constructor: function(player, options) {
        constructor: function () {
            Button.apply(this, arguments);
            this.addClass('player-logo');
            this.controlText(PlayerSkinLogoTitle);
        },
        handleClick: function () {
            window.open(webSiteRootURL, '_blank');
        }
    });

    videojs.registerComponent('Logo', Logo);
    if (player.getChild('controlBar').getChild('PictureInPictureToggle')) {
        player.getChild('controlBar').addChild('Logo', {}, getPlayerButtonIndex('PictureInPictureToggle') + 1);
    } else {
        player.getChild('controlBar').addChild('Logo', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
    }
});