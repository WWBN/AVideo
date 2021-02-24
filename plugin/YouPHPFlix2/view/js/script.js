var video;
var cat;
var videos_id;
var $carousel = [];

function isFlickityEnabled(selector) {
    var isEnabled = $(selector).hasClass('flickity-enabled');
    if (isEnabled) {
        $('#loading').fadeOut();
        $('#footerDiv').fadeIn();

        $('.container-fluid').fadeIn('slow', function () {
            for (i = 0; i < $carousel.length; i++) {
                $carousel[i].flickity('resize');
            }
        });
    } else {
        setTimeout(function () {
            isFlickityEnabled(selector)
        }, 500);
    }
}
$(function () {
    startModeFlix("");

    setTimeout(function () {
        $('#loading').fadeOut();
        $('#footerDiv').fadeIn();
        $('.container-fluid').fadeIn('slow', function () {
            for (i = 0; i < $carousel.length; i++) {
                $carousel[i].flickity('resize');
            }
        });
    }, 2000);

    isFlickityEnabled('.carousel');
    if ($("body.userChannel").length === 0) {
        if ($(window).scrollTop() < 60) {
            $("#mainNavBar").addClass("bgTransparent");
        }
        $(window).scroll(function () {
            if ($(window).scrollTop() < 60) {
                $("#mainNavBar").addClass("bgTransparent");
            } else {
                $("#mainNavBar").removeClass("bgTransparent");
            }
        });
    }
});

function startModeFlix(container) {

    if ($(container).attr('startModeFlix') == 1) {
        return false;
    }

    $(container + ".thumbsImage").on("mouseenter", function () {
        //$(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
        //$(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
        $(this).find(".thumbsGIF").stop(true, true).fadeIn();
    });

    $(container + ".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });


    $(container + ".thumbsImage").on("click", function () {
        var crc = $(this).attr('crc');
        var ajaxLoad = $(this).attr('ajaxLoad');
        var myEleTop = $('.navbar-fixed-top .items-container').outerHeight(true);
        var row = $(this).closest('.row');
        $(this).addClass('active');
        $(this).parent().find(".arrow-down").fadeIn('slow');

        var ajaxLoadID = "#ajaxLoad-" + crc;
        if (ajaxLoad && !$(ajaxLoadID).attr('ajaxLoaded')) {
            $(ajaxLoadID).load(ajaxLoad);
            $(ajaxLoadID).attr('ajaxLoaded', 1);
        }

        $(".arrow-down").fadeOut();
        $(".thumbsImage").removeClass('active');
        console.log("crc", crc);
        $(this).closest('.flickity-area').find('.poster').not('#poster' + crc).slideUp();
        if ($('#poster' + crc).is(":hidden")) {
            $('#poster' + crc).css('background-image', 'url(' + $('#poster' + crc).attr('poster') + ')');
            $('#poster' + crc).slideDown('fast', function () {
                var top = row.offset().top;
                $('html, body').animate({
                    scrollTop: top - myEleTop
                }, 'fast');
            });
        } else {
            $(this).closest('.flickity-area').find('#poster' + crc).slideUp();
            for (i = 0; i < $carousel.length; i++) {
                $carousel[i].flickity('playPlayer');
            }
        }

    });

    $(container + '.carousel').each(function (index) {
        var dataFlickirty = $(this).attr('data-flickity');
        if (typeof dataFlickirty != 'undefined') {
            var json = JSON.parse($(this).attr('data-flickity'));
            $carousel.push($(this).flickity(json));
        }
    });

    $(container).attr('startModeFlix', 1);
    if (typeof transformLinksToEmbed == 'function') {
        transformLinksToEmbed(container + ' a.canWatchPlayButton');
    }

    $("img.thumbsJPG").not('flickity-lazyloaded').each(function (index) {
        $(this).attr('src', $(this).attr('data-flickity-lazyload'));
        $(this).addClass('flickity-lazyloaded');
    });
}