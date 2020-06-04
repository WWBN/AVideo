$(document).ready(function () {
// Extend default
    setTimeout(function () {
        if (typeof player == 'undefined') {
            player = videojs(videoJsId);
        }
// Extend default
        var Button = videojs.getComponent('Button');
        var Theater = videojs.extend(Button, {
            //constructor: function(player, options) {
            constructor: function () {
                Button.apply(this, arguments);
                this.addClass('ypt-compress');
                this.addClass('vjs-button-fa-size');
                this.controlText("Default view");
                if (Cookies.get('compress') === "true") {
                    toogleEC(this);
                }
            },
            handleClick: function () {
                toogleEC(this);
            }
        });

// Register the new component and set the right location as FF is not having a PIP button.
        videojs.registerComponent('Theater', Theater);
        if (player.getChild('controlBar').getChild('PictureInPictureToggle')) {
            player.getChild('controlBar').addChild('Theater', {}, getPlayerButtonIndex('PictureInPictureToggle') + 1);
        } else {
            player.getChild('controlBar').addChild('Theater', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
        }
    }, 30);
});
