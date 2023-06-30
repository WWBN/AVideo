// Extend default
$(document).ready(function () { 
    setTimeout(function() {  
        if(typeof player == 'undefined') {
            player = videojs(videoJsId);
        } 

        var Button = videojs.getComponent('Button');

        class NextButton extends Button {
            constructor() {
                super(...arguments);
                this.addClass('next-button');
                this.addClass('vjs-button-fa-size');
                this.controlText("Next");
            }
            handleClick() {
                document.location = autoPlayVideoURL;
            }
        }

        // Register the new component
        videojs.registerComponent('NextButton', NextButton);
        player.getChild('controlBar').addChild('NextButton', {}, getPlayerButtonIndex('PlayToggle')+1);
    }, 30); 
});
