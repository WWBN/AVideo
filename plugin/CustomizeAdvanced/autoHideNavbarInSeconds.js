var autoHidingNavbarTimeout;
function autoHideNavbar() {
    //console.log("autoHidingNavbar");
    autoHidingNavbarTimeout = setTimeout(function () {
        $("#mainNavBar").on("show.autoHidingNavbar", function () {
            if ($(window).scrollTop() < 10) {
                $('body').removeClass('nopadding');
            }
        });
        $("#mainNavBar").on("hide.autoHidingNavbar", function () {
            if ($(window).scrollTop() < 10) {
                $('body').addClass('nopadding');
             }
        });
        $("#mainNavBar").autoHidingNavbar("hide");
    }, autoHidingNavbarTimeoutMiliseconds);
}
$(function () {
    if ($("#mainNavBar").length) {
        autoHideNavbar();
        $("#mainNavBar").mouseover(function () {
            //console.log("clearTimeout autoHidingNavbar");
            clearTimeout(autoHidingNavbarTimeout);
        });
        $("#mainNavBar").mouseout(function () {
            autoHideNavbar();
        });
        $(document).mousemove(function (event) {
            if (event.pageY - $(document).scrollTop() <= 10) {
                $("#mainNavBar").autoHidingNavbar("show");
            }
        });
        $("#mainNavBar").on("show.autoHidingNavbar", function () {
            if ($(window).scrollTop() < 10) {
                $('body').removeClass('nopadding');
            }
        });

        $("#mainNavBar").on("hide.autoHidingNavbar", function () {
            if ($(window).scrollTop() < 10) {
                $('body').addClass('nopadding');
             }
        });
    }
});