$(document).ready(function () {
    $('body').addClass('youtube')
    $(document).off('click.sidebar');
    $('#buttonMenu').off('click.sidebar');

    $('#buttonMenu').on("click.sidebar", function (event) {
        event.stopPropagation();
        if ($('body').hasClass('youtube')) {
            $('body').removeClass('youtube');
        } else {
            $('body').addClass('youtube');
        }

        $("#sidebar").toggle("slide");
        $('#myNavbar').removeClass("in");
        $('#mysearch').removeClass("in");
    });
    //$("#buttonSearch, #buttonMyNavbar").off('click');
    if ($(window).width() < 960) {
        $('#buttonMenu').trigger("click");
    }
    $(window).resize(function () {
        if ($(window).width() < 960) {
            if ($('body').hasClass('youtube')) {
                $('#buttonMenu').trigger("click");
            }
        } else {
            if (!$('body').hasClass('youtube')) {
                $('#buttonMenu').trigger("click");
            }
        }
    });
});