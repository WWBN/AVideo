var Button = videojs.getComponent('Button');

class PlayListProgramButton extends Button {
    constructor() {
        super(...arguments);
        this.addClass('playListProgram-button');
        this.controlText("playListProgram");
    }

    handleClick() {
        console.log('playListProgramButton clicked');
        $('#playListHolder').fadeToggle();
    }
}

videojs.registerComponent('PlayListProgramButton', PlayListProgramButton);
player.getChild('controlBar').addChild('PlayListProgramButton', {}, getPlayerButtonIndex('fullscreenToggle') - 1);

function playListFadeIn() {
    $('#playListHolder').fadeIn();
}

function playListFadeOut() {
    $('#playListHolder').fadeOut();
}

function startTrackDisplayPlayListHolder() {
    if ($(".vjs-text-track-display").length === 0) {
        setTimeout(function () {
            startTrackDisplayPlayListHolder();
        }, 1000);
    }
}