
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
    //$('.AdsLeaderBoardTop').removeClass('col-lg-12 col-sm-12');
    //$('.AdsLeaderBoardTop').addClass('col-sm-offset-1 col-lg-6 col-sm-6');
    //$('.AdsLeaderBoardTop2').removeClass('col-lg-10 col-sm-10  col-md-10');
    //$('.AdsLeaderBoardTop2').addClass('col-sm-6 col-md-6 col-sm-6');
    $('.rightBar').addClass('compress');
    setInterval(function () {
        $('.principalContainer').css({'min-height': $('.rightBar').height()});
    }, 2000);
    $('#mvideo').removeClass('main-video');
    left = $('#mvideo').find('.secC').offset().left + $('#mvideo').find('.secC').width() + 30;
    $(".compress").css('left', left);
    if(t!=undefined){
        t.removeClass('ypt-compress');
        t.addClass('ypt-expand');
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
    //$('.AdsLeaderBoardTop').removeClass('col-sm-offset-1 col-lg-6 col-sm-6');
    //$('.AdsLeaderBoardTop').addClass('col-lg-12 col-sm-12 ');
    //$('.AdsLeaderBoardTop2').removeClass('col-lg-6 col-sm-6 col-md-6');
    //$('.AdsLeaderBoardTop2').addClass('col-sm-10 col-md-10 col-sm-10');
    $(".compress").css('left', "");
    $('.rightBar').removeClass('compress');
    $('#mvideo').addClass('main-video');
    console.log("expand");
    if(t!=undefined){
        t.removeClass('ypt-expand');
        t.addClass('ypt-compress');
    }
}
function toogleEC(t) {
    if(t!=undefined){
        if (t.hasClass('ypt-expand')) {
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
var oldYouTubeMenuIsOpened;
function fixCompressSize(){
    console.log("fixCompressSize "+youTubeMenuIsOpened);
    left = $('#mvideo').find('.secC').offset().left + $('#mvideo').find('.secC').width() + 30;
    if(youTubeMenuIsOpened){
        left = left-300;
    }
    $(".compress").css('left', left);
}
$(document).ready(function () {
    $(window).on('resize', function () {
        fixCompressSize();
    });
    // this is to make sure we read the correct menu state
    setInterval(function(){
        if(oldYouTubeMenuIsOpened === youTubeMenuIsOpened){
            return false;
        }
        oldYouTubeMenuIsOpened = youTubeMenuIsOpened;
        fixCompressSize();
    }, 1000);
});