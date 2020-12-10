$(document).ready(function () {
    linksToFullscreen('a.canWatchPlayButton');
    $(document).on('keyup', function (evt) {
        if (evt.keyCode == 27) {
            closeFlixFullScreen();
        }
    });

});


function flixFullScreen(link) {
    $('body').addClass('fullScreen');
    var divHTML = '<div id="divIframeFull" style="background-color:black; text-align: center; position: fixed; top: 0;left: 0; z-index: 9999;">';
    divHTML += '<div id="divTopBar" style="position: fixed; top: 0; left: 0; height: 50px; width: 100vw; z-index: 99999; padding:10px; ">';
    divHTML += '<span id="closeBtnFull" class="btn pull-right" onclick="closeFlixFullScreen();" style="opacity: 0.7; filter: alpha(opacity=70);">';
    divHTML += '<i class="fa fa-times" style="text-shadow: 1px 1px rgba(255,255,255,0.7);"></i></span></div></div>';
    var div = $(divHTML).append('<iframe src="' + link + '" style="background-color:black; position: fixed; top: 0; left: 0; height: 100vh; width: 100vw; z-index: 9999; overflow: hidden;"  frameBorder="0" id="iframeFull" allow="autoplay" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen>');
    $('body').append(div);
    $('body').addClass('fullscreen');
    $("#divIframeFull").fadeIn();

}

var closeFlixFullScreenTimout;
function closeFlixFullScreen() {
    console.log("closeFlixFullScreen");
    clearTimeout(closeFlixFullScreenTimout);
    closeFlixFullScreenTimout = setTimeout(function () {

        console.log("closeFlixFullScreen timout");
        $('body').removeClass('fullscreen');
        $('body').attr('class', '');
    }, 500);

    if ($('#divIframeFull').length) {

        console.log("closeFlixFullScreen divIframeFull");
        $("#divIframeFull").fadeOut("slow", function () {
            console.log("closeFlixFullScreen divIframeFull fadeOut");
            $('#divIframeFull').remove();
        });
    }
    console.log("closeFlixFullScreen removeClass");
    $('body').removeClass('fullscreen');
}

function linksToFullscreen(selector) {
    if (playVideoOnFullscreen && typeof flixFullScreen == 'function') {
        $(selector).each(function (index) {
            if(!$(this).hasClass('linksToFullscreen')){
                $(this).addClass('linksToFullscreen');
                $(this).click(function (event) {
                    event.preventDefault();
                    var link = $(this).attr('embed');
                    if (!link) {
                        link = $(this).attr('href');
                        link = addGetParam(link, 'embed', 1);
                    }
                    flixFullScreen(link);
                });
            }
        });
    }
}
