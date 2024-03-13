
function compress() {
    console.log("compress");
    $("body").removeClass('ypt-is-expanded');
    $("body").addClass('ypt-is-compressed');
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
    $("body").removeClass('ypt-is-compressed');
    $("body").addClass('ypt-is-expanded');
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

function isCompressedVar(){
    if(typeof isCompressed === 'undefined'){
        return false;
    }
    return !empty(isCompressed);
}

function toogleEC() {
    if (isCompressedVar()) {
        expand();
    } else {
        compress();
    }
}
$(function () {
    if (isCompressedVar()) {
        compress();
    } else {
        expand();
    }
});
