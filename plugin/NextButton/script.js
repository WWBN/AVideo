// Extend default
$(document).ready(function () {
    setTimeout(function() {
        if(typeof player == 'undefined' && typeof videoJsId != 'undefined') {
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
                var url = getNextPlaylistUrl();
                if (empty(url)) {
                    url = autoPlayVideoURL;
                }
                document.location = url;
            }
        }

        // Register the new component
        videojs.registerComponent('NextButton', NextButton);
        player.getChild('controlBar').addChild('NextButton', {}, getPlayerButtonIndex('PlayToggle')+1);
    }, 30);
});
function getNextPlaylistUrl() {
    // Check if '.playlist-nav' exists
    if ($('.playlist-nav').length === 0) {
        // If '.playlist-nav' does not exist, return false
        return false;
    }

    // Find the active list item
    var activeLi = $('.playlist-nav .navbar-nav li.active');

    // Determine the next list item; if the active item is the last, wrap to the first list item
    var nextLi = activeLi.is(':last-child') ? $('.playlist-nav .navbar-nav li').first() : activeLi.next();

    // Get the URL from the 'a' element inside the next list item
    var nextUrl = nextLi.find('a').attr('href');

    // Return the URL, or false if it's not found
    return nextUrl || false;
}
