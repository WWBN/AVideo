// Function to add "Skip Intro" button functionality using jQuery
function setupSkipIntroButton(player) {
    // Create the button element using jQuery
    var $skipButton = $('<button>', {
        class: 'vjs-skip-intro-button',
        text: 'Skip Intro'
    });

    // Define the click event handler
    $skipButton.on('click', function() {
        player.currentTime(skipintroTime);
        $skipButton.hide(); // Hide the button after clicking
    });

    function addSkipButton() {
        //var $textTrackDisplay = $(player.el()).find('.vjs-text-track-display');
        var $textTrackDisplay = $(player.el());
        if ($textTrackDisplay.length && !$('body').find($skipButton).length) {
            $textTrackDisplay.append($skipButton);
        }
    }

    // Add the button when the player is ready
    player.ready(function() {
        addSkipButton();

        // Add logic to show/hide the button based on current time
        player.on('timeupdate', function() {
            var currentTime = player.currentTime();
            if (currentTime < skipintroTime) {
                $skipButton.show();
            } else {
                $skipButton.hide();
            }
        });

        // Add event listeners to handle fullscreen changes
        player.on('fullscreenchange', function() {
            addSkipButton();
        });

        // Add event listener to handle player resize (e.g., when going fullscreen)
        player.on('playerresize', function() {
            addSkipButton();
        });
    });
}
setupSkipIntroButton(player);
