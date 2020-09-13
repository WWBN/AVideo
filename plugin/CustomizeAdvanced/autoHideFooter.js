var checkFooterTimout;
$(function () {
    checkFooter();
    $(window).scroll(function () {
        clearTimeout(checkFooterTimout);
        checkFooterTimout = setTimeout(function () {
            checkFooter();
        }, 100);
    });
});
function checkFooter() {
    if($(document).height() <= $(window).height()){
        $("#mainFooter").css("position", "fixed");
    }else{
        $("#mainFooter").css("position", "relative");
    }
}