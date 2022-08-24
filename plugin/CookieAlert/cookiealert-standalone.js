$(document).ready(function () {
    if (!getCookie("acceptCookies") && !inIframe()) {
        $(".cookiealert").addClass("show");
    }
    $(".acceptcookies").on('click', function(){
        setCookie("acceptCookies", true, 60);
        $(".cookiealert").removeClass("show");
    });
});