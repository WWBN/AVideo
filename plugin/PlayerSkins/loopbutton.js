var Button = videojs.getComponent('Button');

class LoopButton extends Button {
    constructor() {
        super(...arguments);
        this.addClass('loop-button');
        if (!isPlayerLoop()) {
            this.addClass('loop-disabled-button');
        } else {
            this.addClass('fa-spin');
        }
        this.controlText("Loop");
    }
    handleClick() {
        tooglePlayerLoop();
    }
}

videojs.registerComponent('LoopButton', LoopButton);
player.getChild('controlBar').addChild('LoopButton', {}, 0);
