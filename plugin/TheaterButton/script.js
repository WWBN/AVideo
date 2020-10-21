
function compress() {
    console.log("compress");
    $("#modeYoutubeTop").prependTo("#modeYoutubeBottomContent");
    $("#avideoTheaterButton").removeClass('ypt-compress');
    $("#avideoTheaterButton").addClass('ypt-expand');
    $("#avideoTheaterButton").attr("title","Switch to Theater Mode");
    $("#avideoTheaterButton").find('.vjs-control-text').text("Switch to Theater Mode");
    
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
    $("#avideoTheaterButton").attr("title","Switch to Compressed Mode");
    $("#avideoTheaterButton").find('.vjs-control-text').text("Switch to Compressed Mode");
    
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
