$(function () {
    $("#mainNavBar").on("show.autoHidingNavbar", function () {
        if ($(window).scrollTop() < 120) {
            $("body").removeClass("nopadding");
        }
    });

    $("#mainNavBar").on("hide.autoHidingNavbar", function () {
        if ($(window).scrollTop() < 120) {
            $("body").addClass("nopadding");
        }
    });
});