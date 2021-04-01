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
    $('body').addClass('fullScreen');
    fullscreenOnBrowser();
    var div = $('<div id="divIframeFull" style="background-color:black;"><div id="divTopBar" style="position: fixed; top: 0; left: 0; height: 50px; width: 100vw; z-index: 99999; padding:10px; "><span id="closeBtnFull" class="btn pull-right" onclick="closeIframe();" style="opacity: 0.5; filter: alpha(opacity=50);"><i class="fa fa-times"></i></span></div></div>').append('<iframe src="' + link + '" style="background-color:black; width: 100vw; overflow: hidden;"  frameBorder="0" id="iframeFull" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen>');
    $('body').append(div);
    $("#divIframeFull").fadeIn();
    Cookies.set("autoplay", true, {path: '/', expires: 365});
}

function fullscreenOnBrowser() {
    if (typeof playVideoOnBrowserFullscreen === 'undefined') {
        return false;
    }

    var docElm = document.documentElement;
    if (docElm.requestFullscreen) {
        docElm.requestFullscreen();
    } else if (docElm.msRequestFullscreen) {
        docElm = document.body; //overwrite the element (for IE)
        docElm.msRequestFullscreen();
    } else if (docElm.mozRequestFullScreen) {
        docElm.mozRequestFullScreen();
    } else if (docElm.webkitRequestFullScreen) {
        docElm.webkitRequestFullScreen();
    }
}

function fullscreenOffBrowser() {
    if (typeof playVideoOnBrowserFullscreen === 'undefined') {
        return false;
    }
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitCancelFullScreen) {
        document.webkitCancelFullScreen();
    }
}

function closeIframe() {
    if ($('#divIframeFull').length) {
        $("#divIframeFull").fadeOut("slow", function () {
            $('body').removeClass('fullScreen');
            $('#divIframeFull').remove();
            fullscreenOffBrowser();
        });
    }

}

