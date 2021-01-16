var Button = videojs.getComponent('Button');

var LoopButton = videojs.extend(Button, {
    //constructor: function(player, options) {
    constructor: function () {
        Button.apply(this, arguments);
        this.addClass('loop-button');
        if (!isPlayerLoop()) {
            this.addClass('loop-disabled-button');
        } else {
            this.addClass('fa-spin');
        }
        this.controlText("Loop");
    },
    handleClick: function () {
        tooglePlayerLoop();
    }
});

videojs.registerComponent('LoopButton', LoopButton);
player.getChild('controlBar').addChild('LoopButton', {}, 0);