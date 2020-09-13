var checkFooterTimout;
$(function () {
    $("#mainFooter").hide();
    checkFooter();
    $(window).scroll(function () {
        clearTimeout(checkFooterTimout);
        checkFooterTimout = setTimeout(function () {
            checkFooter();
        }, 100);
    });
});
function checkFooter() {
    $("body .container, body .container-fluid").first().css("padding-bottom", $("#mainFooter").height() + "px");
    if (($(window).scrollTop() + $(window).height()+50) >= $(document).height()) {
        $("#mainFooter").slideDown({
            complete: function () {
                //$("html, body").animate({scrollTop: $(document).height()}, 200);
                //$("html, body").animate({scrollTop: $(document).height()}, 0);
            }
        });
    } else {
        $("#mainFooter").slideUp({
            complete: function () {
                //$("html, body").animate({scrollTop: $(document).height()+500}, 0);
            }
        });
    }
}