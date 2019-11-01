var video;
var cat;
var videos_id;
var $carousel;

function isFlickityEnabled(selector) {
    var isEnabled = $(selector).hasClass('flickity-enabled');
    if (isEnabled) {
        $('#loading').fadeOut();
        $('#footerDiv').fadeIn();
        
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
        if($('#poster'+crc).is(":hidden")){
            $('#poster'+crc).css('background-image', 'url(' + $('#poster'+crc).attr('poster') + ')');
            $('#poster'+crc).slideDown('slow', function () {
                var top = row.offset().top;
                $('html, body').animate({
                    scrollTop: top - myEleTop
                }, 'slow');
            });
        }else{
            $carousel.flickity('playPlayer');
        }

    });
    
    setTimeout(function () {
        $('#loading').fadeOut();
        $('#footerDiv').fadeIn();
        $('.container-fluid').fadeIn('slow', function () {
            $carousel.flickity('resize');
        });
    }, 2000);
    
    $carousel = $('.carousel').flickity();
    isFlickityEnabled('.carousel');

});

