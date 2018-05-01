// Extend default
if(typeof player == 'undefined'){player = videojs(videoJsId);}
var Button = videojs.getComponent('Button');
var nextButton = videojs.extend(Button, {
    //constructor: function(player, options) {
    constructor: function () {
        Button.apply(this, arguments);
        //this.addClass('vjs-chapters-button');
        this.addClass('next-button');
        this.addClass('vjs-button-fa-size');
        this.controlText("Next");
    },
    handleClick: function () {
        document.location = autoPlayVideoURL;
    }
});

// Register the new component
videojs.registerComponent('nextButton', nextButton);
player.getChild('controlBar').addChild('nextButton', {}, getPlayerButtonIndex('PlayToggle')+1);