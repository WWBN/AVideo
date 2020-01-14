$(document).ready(function () {
    $(document).off('click.sidebar');
    $('#buttonMenu').off('click.sidebar');

    $('#buttonMenu').on("click.sidebar", function (event) {
        event.stopPropagation();
        if ($('body').hasClass('youtube')) {
            closeYouTubeMenu();
        } else {
            openYouTubeMenu();
        }
    });
    //$("#buttonSearch, #buttonMyNavbar").off('click');
    if ($(window).width() < 1500) {
        var youTubeMenuIsOpened = Cookies.get('youTubeMenuIsOpened');
        if(typeof youTubeMenuIsOpened === 'undefined'){
            $('#buttonMenu').trigger("click");
        }
    }
    $(window).resize(function () {
        if ($(window).width() < 1500) {
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

function closeYouTubeMenu() {
    console.log('closeYouTubeMenu');
    $('body').removeClass('youtube');
    $("#sidebar").toggle("slide");
    $('#myNavbar').removeClass("in");
    $('#mysearch').removeClass("in");    
    Cookies.set('youTubeMenuIsOpened', false, {
        path: '/',
        expires: 365
    });
}

function openYouTubeMenu() {
    console.log('openYouTubeMenu');
    $('body').addClass('youtube');
    $("#sidebar").toggle("slide");
    $('#myNavbar').removeClass("in");
    $('#mysearch').removeClass("in");
    Cookies.set('youTubeMenuIsOpened', true, {
        path: '/',
        expires: 365
    });
}