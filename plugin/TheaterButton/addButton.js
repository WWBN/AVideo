$(document).ready(function () {
// Extend default
    if (typeof player == 'undefined') {
        player = videojs(videoJsId);
    }
// Extend default
    var Button = videojs.getComponent('Button');
    var Theater = videojs.extend(Button, {
        //constructor: function(player, options) {
        constructor: function () {
            Button.apply(this, arguments);
            //this.addClass('vjs-chapters-button');
            this.addClass('fa');
            this.addClass('fa-compress');
            this.addClass('vjs-button-fa-size');
            this.controlText("Theater");
            if (Cookies.get('compress') === "true") {
                toogleEC(this);
            }
        },
        handleClick: function () {
            toogleEC(this);
        }
    });

// Register the new component
    videojs.registerComponent('Theater', Theater);
    player.getChild('controlBar').addChild('Theater', {}, getPlayerButtonIndex('RemainingTimeDisplay') + 1);

});