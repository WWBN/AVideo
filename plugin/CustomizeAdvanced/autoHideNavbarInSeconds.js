var autoHidingNavbarTimeout;
function autoHideNavbar() {
    //console.log("autoHidingNavbar");
    autoHidingNavbarTimeout = setTimeout(function () {
        $("#mainNavBar").on("show.autoHidingNavbar", function() {
          $('body').removeClass('nopadding');
        });
        $("#mainNavBar").on("hide.autoHidingNavbar", function() {
          $('body').addClass('nopadding');
        });
        $("#mainNavBar").autoHidingNavbar("hide");
    }, autoHidingNavbarTimeoutMiliseconds);
}
$(function () {
    autoHideNavbar();
    $("#mainNavBar").mouseover(function () {
        //console.log("clearTimeout autoHidingNavbar");
        clearTimeout(autoHidingNavbarTimeout);
    });
    $("#mainNavBar").mouseout(function () {
        autoHideNavbar();
    });
    $(document).mousemove(function (event) {
        if (event.pageY - $(document).scrollTop() <= 20) {
            $("#mainNavBar").autoHidingNavbar("show");
        }
    });
    $("#mainNavBar").on("show.autoHidingNavbar", function() {
      $('body').removeClass('nopadding');
    });

    $("#mainNavBar").on("hide.autoHidingNavbar", function() {
      $('body').addClass('nopadding');
    });
});