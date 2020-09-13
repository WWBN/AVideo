$(function () {
    $("#mainNavBar").on("show.autoHidingNavbar", function () {
        $("body").removeClass("nopadding");
    });

    $("#mainNavBar").on("hide.autoHidingNavbar", function () {
        $("body").addClass("nopadding");
    });
});