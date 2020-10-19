
function compress(t) {
    console.log("compress");
    $("#modeYoutubeTop").prependTo("#modeYoutubeBottomContent");
    if(typeof t !== 'undefined'){
        t.removeClass('ypt-compress');
        t.addClass('ypt-expand');
    }
}
function expand(t) {
    console.log("expand");
    $("#modeYoutubeTop").prependTo("#modeYoutubePrincipal");
    if(typeof t !== 'undefined'){
        t.removeClass('ypt-expand');
        t.addClass('ypt-compress');
    }
}
function toogleEC(t) {
    if(typeof t !== 'undefined'){
        if (t.hasClass('ypt-expand')) {
            expand(t);
            Cookies.set('compress', false, {
                path: '/',
                expires: 365
            });
		t.controlText('Default view');
        } else {
            compress(t);
            Cookies.set('compress', true, {
                path: '/',
                expires: 365
            });
		t.controlText('Theater mode');
        }
    }
}
$(document).ready(function () {
});
