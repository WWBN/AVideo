$(document).ready(function () {   
    
    $('a.canWatchPlayButton').click(function (event) {
        event.preventDefault();
        var link = $(this).attr('href');
        flixFullScreen(link);
    });

    $(document).on('keyup', function (evt) {
        if (evt.keyCode == 27) {
            closeFlixFullScreen();
        }
    });
});

