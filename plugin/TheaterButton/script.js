
function compress(t) {
    console.log("compress");
    $('#mvideo').find('.firstC').removeClass('col-sm-2');
    $('#mvideo').find('.firstC').removeClass('col-md-2');
    $('#mvideo').find('.firstC').addClass('col-sm-1');
    $('#mvideo').find('.firstC').addClass('col-md-1');
    $('#mvideo').find('.secC').removeClass('col-sm-8');
    $('#mvideo').find('.secC').removeClass('col-md-8');
    $('#mvideo').find('.secC').addClass('col-sm-6');
    $('#mvideo').find('.secC').addClass('col-md-6');
    $('.rightBar').addClass('compress');
    setInterval(function () {
        $('.principalContainer').css({'min-height': $('.rightBar').height()});
    }, 2000);
    $('#mvideo').removeClass('main-video');
    left = $('#mvideo').find('.secC').offset().left + $('#mvideo').find('.secC').width() + 30;
    $(".compress").css('left', left);
    if(t!=undefined){
        t.removeClass('fa-compress');
        t.addClass('fa-expand');
    }
}
function expand(t) {
    $('#mvideo').find('.firstC').removeClass('col-sm-1');
    $('#mvideo').find('.firstC').removeClass('col-md-1');
    $('#mvideo').find('.firstC').addClass('col-sm-2');
    $('#mvideo').find('.firstC').addClass('col-md-2');
    $('#mvideo').find('.secC').removeClass('col-sm-6');
    $('#mvideo').find('.secC').removeClass('col-md-6');
    $('#mvideo').find('.secC').addClass('col-sm-8');
    $('#mvideo').find('.secC').addClass('col-md-8');
    $(".compress").css('left', "");
    $('.rightBar').removeClass('compress');
    $('#mvideo').addClass('main-video');
    console.log("expand");
    if(t!=undefined){
        t.removeClass('fa-expand');
        t.addClass('fa-compress');
    }
}
function toogleEC(t) {
    if(t!=undefined){
        if (t.hasClass('fa-expand')) {
            expand(t);
            Cookies.set('compress', false, {
                path: '/',
                expires: 365
            });
        } else {
            compress(t);
            Cookies.set('compress', true, {
                path: '/',
                expires: 365
            });
        }
    }
}
$(document).ready(function () {
    $(window).on('resize', function () {
        left = $('#mvideo').find('.secC').offset().left + $('#mvideo').find('.secC').width() + 30;
        $(".compress").css('left', left);
    });
});