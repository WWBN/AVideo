let vh = window.innerHeight * 0.01;
document.documentElement.style.setProperty("--vh", `${vh}px`);
window.addEventListener("resize", () => {
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty("--vh", `${vh}px`);
});
$(document).ready(function () {
    $('a.galleryLink').click(function (event) {
        event.preventDefault();
        var id = $(this).attr('videos_id');
        startFullScreen(webSiteRootURL + 'v/' + id);
    });

    $('a.evideo').click(function (event) {
        event.preventDefault();
        var href = $(this).attr('href');
        startFullScreen(href);
    });

    $('a.hrefLink').click(function (event) {
        event.preventDefault();
        var link = $(this).attr('href');
        startFullScreen(link);
    });

    $(document).on('keyup', function (evt) {
        if (evt.keyCode == 27) {
            closeIframe();
        }
    });

    if (typeof playVideoOnBrowserFullscreen !== 'undefined') {
        setInterval(function () {
            if (!(!window.screenTop && !window.screenY)) {
                closeIframe();
            }
        }, 1000);
    }


});

function startFullScreen(link) {
    openFullscreenVideo(link, link);
}
