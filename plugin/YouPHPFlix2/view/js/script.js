var video;
var cat;
var videos_id;
var $carousel;

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

function isFlickityEnabled(selector) {
    var isEnabled = $(selector).hasClass('flickity-enabled');
    if (isEnabled) {
        $('#loading').fadeOut();
        $('.container-fluid').fadeIn('slow', function () {
            $carousel.flickity('resize');
        });
    } else {
        setTimeout(function () {
            isFlickityEnabled(selector)
        }, 500);
    }
}

$(function () {
    $(document).on('keyup', function (evt) {
        if (evt.keyCode == 27) {
            closeFlixFullScreen();
        }
    });
    $(".thumbsImage").on("mouseenter", function () {
        //$(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
        //$(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
        $(this).find(".thumbsGIF").stop(true, true).fadeIn();
    });

    $(".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });


    $(".thumbsImage").on("click", function () {
        var crc = $(this).attr('crc');
        var myEleTop = $('.navbar-fixed-top .items-container').outerHeight(true);   
        var row = $(this).closest('.row');
        $(this).addClass('active');
        $(this).parent().find(".arrow-down").fadeIn('slow');
        
        $(".arrow-down").fadeOut();
        $(".thumbsImage").removeClass('active');

        $('.poster').slideUp();
        
        $('#poster'+crc).slideDown('slow', function () {
            var top = row.offset().top;
            $('html, body').animate({
                scrollTop: top - myEleTop
            }, 'slow');
        });

    });

    $carousel = $('.carousel').flickity();
    isFlickityEnabled('.carousel');

});

