
function compress() {
    console.log("compress");
    $("#modeYoutubeTop").prependTo("#modeYoutubeBottomContent");
    $("#avideoTheaterButton").removeClass('ypt-compress');
    $("#avideoTheaterButton").addClass('ypt-expand');
    
    Cookies.set('compress', true, {
        path: '/',
        expires: 365
    });
    isCompressed = true;
}
function expand() {
    console.log("expand");
    $("#modeYoutubeTop").prependTo("#modeYoutubePrincipal");
    $("#avideoTheaterButton").removeClass('ypt-expand');
    $("#avideoTheaterButton").addClass('ypt-compress');
    
    Cookies.set('compress', false, {
        path: '/',
        expires: 365
    });
    isCompressed = false;
}
function toogleEC() {
    if (isCompressed) {
        expand();
    } else {
        compress();
    }
}
$(document).ready(function () {
});
