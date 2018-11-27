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


function flixFullScreen(link){
    $('body').addClass('fullScreen');
    var div = $('<div id="divIframeFull" style="background-color:black;height: 100vh; width: 100vw; text-align: center; position: fixed; top: 0;left: 0; z-index: 9999;"><div id="divTopBar" style="position: fixed; top: 0; left: 0; height: 50px; width: 100vw; z-index: 99999; padding:10px; "><span id="closeBtnFull" class="btn pull-right" onclick="closeFlixFullScreen();" style="opacity: 0.5; filter: alpha(opacity=50);"><i class="fa fa-times"></i></span></div></div>').append('<iframe src="' + link + '" style="background-color:black; position: fixed; top: 0; left: 0; height: 100vh; width: 100vw; z-index: 9999; overflow: hidden;" id="iframeFull" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen>');
    $('body').append(div);
    $("#divIframeFull").fadeIn();
}

function closeFlixFullScreen() {
    if($('#divIframeFull').length){
        $("#divIframeFull").fadeOut("slow", function () {
            $('body').removeClass('fullScreen');
            $('#divIframeFull').remove();
        });
    }

}


