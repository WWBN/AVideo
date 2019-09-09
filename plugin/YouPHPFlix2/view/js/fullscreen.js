$(document).ready(function () {   
    if(playVideoOnFullscreen){
        $('a.canWatchPlayButton').click(function (event) {
            event.preventDefault();
            var link = $(this).attr('href');
            flixFullScreen(link);
        });
    }
    $(document).on('keyup', function (evt) {
        if (evt.keyCode == 27) {
            closeFlixFullScreen();
        }
    });
   
});


function flixFullScreen(link){
    $('body').addClass('fullScreen');
    var div = $('<div id="divIframeFull" style="background-color:black; text-align: center; position: fixed; top: 0;left: 0; z-index: 9999;"><div id="divTopBar" style="position: fixed; top: 0; left: 0; height: 50px; width: 100vw; z-index: 99999; padding:10px; "><span id="closeBtnFull" class="btn pull-right" onclick="closeFlixFullScreen();" style="opacity: 0.5; filter: alpha(opacity=50);"><i class="fa fa-times"></i></span></div></div>').append('<iframe src="' + link + '" style="background-color:black; position: fixed; top: 0; left: 0; height: 100vh; width: 100vw; z-index: 9999; overflow: hidden;"  frameBorder="0" id="iframeFull" allow="autoplay" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen>');
    $('body').append(div);
    $('body').addClass('fullscreen');
    $("#divIframeFull").fadeIn();
    
}

function closeFlixFullScreen() {
    setTimeout(function(){
        $('body').removeClass('fullscreen');
        $('body').attr('class', '');
    },500);
    
    if($('#divIframeFull').length){
        $("#divIframeFull").fadeOut("slow", function () {
            $('#divIframeFull').remove();
        });
    }
    $('body').removeClass('fullscreen');
}


