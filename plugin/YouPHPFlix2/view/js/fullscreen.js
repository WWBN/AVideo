if (typeof flixFullScreen === 'undefined') {
    var vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty("--vh", `${vh}px`);
    window.addEventListener("resize", () => {
        vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty("--vh", `${vh}px`);
    });
    $(document).ready(function () {
        transformLinksToEmbed('a.canWatchPlayButton');
        $(document).on('keyup', function (evt) {
            if (evt.keyCode == 27) {
                closeFlixFullScreen();
            }
        });
        $("body").addClass("Chat2StaticRight");
    });

    function transformLinksToEmbed(selector) {
        if (typeof playVideoOnFullscreen === 'undefined') {
            return false;
        }
        if (playVideoOnFullscreen === 1 && typeof linksToFullscreen === 'function') {
            linksToFullscreen(selector);
        } else if (playVideoOnFullscreen === 2 && typeof linksToEmbed === 'function') {
            linksToEmbed(selector);
        }
    }

    var flixFullScreenActive = false;

    function flixFullScreen(link, url) {
        if (flixFullScreenActive) {
            return false;
        }
        flixFullScreenActive = true;
        setTimeout(function () { flixFullScreenActive = false; }, 500);
        openFullscreenVideo(link, url);
    }

    var linksToFullscreenActive = false;

    function linksToFullscreen(selector) {
        $(selector).each(function (index) {
            if (!$(this).hasClass('linksToFullscreen')) {
                $(this).addClass('linksToFullscreen');
                var href = $(this).attr('href');
                //console.log("linksToFullscreen href="+href);
                //$(this).attr('href', '#');
                $(this).attr('fullhref', href);
                $(this).off("click").click(function (event) {
                    if (linksToFullscreenActive) {
                        return false;
                    }
                    linksToFullscreenActive = true;
                    setTimeout(function () { linksToFullscreenActive = false; }, 500);

                    if (!$(this).hasClass('isserie')) {
                        event.preventDefault();
                        var link = $(this).attr('embed');
                        var fullhref = $(this).attr('fullhref');
                        if (!link) {
                            //console.log("linksToFullscreen embed not found");
                            link = addGetParam(fullhref, 'embed', 1);
                        }
                        flixFullScreen(link, fullhref);
                    }
                });
            }
        });
    }

    function linksToEmbed(selector) {
        //console.log('linksToEmbed("'+selector+'")');
        $(selector).each(function (index) {
            if (!$(this).hasClass('linksToEmbed')) {
                $(this).addClass('linksToEmbed');
                if (!$(this).hasClass('isserie')) {
                    var embed = $(this).attr('embed');
                    var href = $(this).attr('href');
                    if (embed) {
                        href = embed;
                    } else {
                        href = addGetParam(href, 'embed', 1);
                    }

                    $(this).attr('href', addGetParam(href, 'showCloseButton', 1));
                }
            }
        });
    }

}
