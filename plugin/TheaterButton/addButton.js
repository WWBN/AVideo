$(document).ready(function () {

var Button = videojs.getComponent('Button');
var Theater = videojs.extend(Button, {
    //constructor: function(player, options) {
    constructor: function () {
        Button.apply(this, arguments);
        if(isCompressed){
            this.addClass('ypt-expand'); 
            this.controlText("Switch to Compressed Mode");
        }else{
            this.addClass('ypt-compress');  
            this.controlText("Switch to Theater Mode");
        }
        this.addClass('vjs-button-fa-size');
        this.addClass('hidden-sm');
        this.addClass('hidden-xs');
        $(this).attr('id', 'avideoTheaterButton');
    },
    handleClick: function () {
        toogleEC();
    }
});

// Register the new component and set the right location as FF is not having a PIP button.
videojs.registerComponent('Theater', Theater);
});


