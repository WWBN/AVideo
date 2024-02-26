$(document).ready(function () {
    startCookieAlert();
});

function startCookieAlert(){
    if(typeof getCookie == 'undefined'){
        setTimeout(function(){
            startCookieAlert();
        },1000);
        return false;
    }
    if (!getCookie("acceptCookies") && !inIframe()) {
        $(".cookiealert").show();
        $(".cookiealert").addClass("show");
    }
    $(".acceptcookies").on('click', function(){
        setCookie("acceptCookies", true, 60);
        $(".cookiealert").removeClass("show");
    });
}