
var seachFormIsRunning = 0;
var youTubeMenuIsOpened = false;
var youTubeMenuIsCompressed = false;

$(document).ready(function () {
    setTimeout(function () {
        $('.nav li.navsub-toggle a:not(.selected) + ul').hide();
        var navsub_toggle_selected = $('.nav li.navsub-toggle a.selected');
        navsub_toggle_selected.next().show();
        navsub_toggle_selected = navsub_toggle_selected.parent();

        var navsub_toggle_selected_stop = 24;
        while (navsub_toggle_selected.length) {
            if ($.inArray(navsub_toggle_selected.prop('localName'), ['li', 'ul']) == -1)
                break;
            if (navsub_toggle_selected.prop('localName') == 'ul') {
                navsub_toggle_selected.show().prev().addClass('selected');
            }
            navsub_toggle_selected = navsub_toggle_selected.parent();

            navsub_toggle_selected_stop--;
            if (navsub_toggle_selected_stop < 0)
                break;
        }
    }, 500);


    $('.nav').on('click', 'li.navsub-toggle a:not(.selected)', function (e) {
        var a = $(this),
            b = a.next();
        if (b.length) {
            e.preventDefault();

            a.addClass('selected');
            b.slideDown();

            var c = a.closest('.nav').find('li.navsub-toggle a.selected').not(a).removeClass('selected').next();

            if (c.length)
                c.slideUp();
        }
    });

    $('#searchForm').on('submit', function (event) {
        if (seachFormIsRunning) {
            event.preventDefault();
            return false;
        }
        seachFormIsRunning = 1;
        var str = $('#searchFormInput').val();
        if (isMediaSiteURL(str)) {
            event.preventDefault();
            console.log("searchForm is URL " + str);
            seachFormPlayURL(str);
            return false;
        } else {
            console.log("searchForm submit " + str);
            this.submit();
            //document.location = webSiteRootURL + "?search=" + str;
        }
    });

    $('#buttonMenu').on("click.sidebar", function (event) {
        event.stopPropagation();
        YPTSidebarToggle();
    });
    $("#sidebar").on("click", function (event) {
        event.stopPropagation();
    });
    $("#buttonSearch").click(function (event) {
        event.stopPropagation();
        if (isSearchOpen()) {
            modal.showPleaseWait();
            //closeSearchMenu();
        } else {
            openSearchMenu();
        }
    });
    $("#buttonMyNavbar").click(function (event) {
        event.stopPropagation();
        if (isMyNMavbarOpen()) {
            closeRightMenu();
        } else {
            openRightMenu();
        }
    });
    var wasMobile = true;
    $(window).resize(function () {
        if ($(window).width() > 767) {
            // Window is bigger than 767 pixels wide - show search again, if autohide by mobile.
            if (wasMobile) {
                wasMobile = false;
            }
        }
        if ($(window).width() < 767) {
            // Window is smaller 767 pixels wide - show search again, if autohide by mobile.
            if (wasMobile == false) {
                wasMobile = true;
            }
        }
    });

    $(window).resize(function () {
        if (!isScreeWidthCollapseSize()) {
            $("#myNavbar").css({ display: '' });
            $("#myNavbar").removeClass('animate__bounceOutRight');
            var selector = '#buttonMyNavbar svg';
            $(selector).removeClass('active');
            $(selector).attr('aria-expanded', 'false');

            $("#mysearch").css({ display: '' });
        }
    });
});

function isScreeWidthCollapseSize() {
    return $('body').width() <= 767;
}

async function closeLeftMenu() {
    console.log('closeLeftMenu');
    var selector = '#buttonMenu svg';
    $(selector).removeClass('active');
    YPTSidebarClose();
}
async function openLeftMenu() {
    console.log('openLeftMenu');
    if (isScreeWidthCollapseSize()) {
        closeRightMenu();
        closeSearchMenu();
    }
    YPTSidebarOpen();
}

async function closeRightMenu() {
    var selector = '#buttonMyNavbar svg';
    $(selector).removeClass('active');
    $("#myNavbar").removeClass('animate__bounceInRight');
    $("#myNavbar").addClass('animate__bounceOutRight');
    setTimeout(function () {
        $("#myNavbar").hide();
    }, 500);
}
async function openRightMenu() {
    if (isScreeWidthCollapseSize()) {
        closeLeftMenu();
        closeSearchMenu();
    }
    var selector = '#buttonMyNavbar svg';
    $(selector).addClass('active');
    $("#myNavbar").show();
}

async function closeSearchMenu() {
    $("#mysearch").hide();
}
async function openSearchMenu() {
    if (isScreeWidthCollapseSize()) {
        closeLeftMenu();
        closeRightMenu();
    }
    $("#mysearch").show();
}

async function seachFormPlayURL(url) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'view/url2Embed.json.php',
        method: 'POST',
        data: {
            'url': url
        },
        success: function (response) {
            seachFormIsRunning = 0;
            if (response.error) {
                modal.hidePleaseWait();
                avideoToast(response.msg);
            } else {
                if (typeof linksToEmbed === 'function') {
                    document.location = response.playEmbedLink;
                } else
                    if (typeof flixFullScreen == 'function') {
                        flixFullScreen(response.playEmbedLink, response.playLink);
                        modal.hidePleaseWait();
                    } else {
                        document.location = response.playLink;
                    }
            }
        }
    });
}

function isSearchOpen() {
    return $("#mysearch").is(":visible");
}
function isMyNMavbarOpen() {
    return $('#myNavbar').hasClass('animate__bounceInRight');
}
async function YPTSidebarToggle() {
    if (YPTSidebarIsOpen()) {
        closeLeftMenu()
    } else {
        openLeftMenu();
    }
}
function YPTSidebarIsOpen() {
    return $('body').hasClass('youtube');
}
async function YPTSidebarOpen() {
    console.log('YPTSidebarOpen');
    var selector = '#buttonMenu svg';
    $(selector).addClass('active');
    $('body').addClass('youtube');
    $("#sidebar").removeClass('animate__bounceOutLeft');
    $("#sidebar").show();
    $("#sidebar").addClass('animate__animated animate__bounceInLeft');
    Cookies.set("menuOpen", true, { expires: 365, path: '/' });
    setTimeout(function () {
        setTimeout(function () {
            flickityReload();
        }, 500);
    }, 500);
    youTubeMenuIsOpened = true;
}

async function flickityReload() {
    var flickityEnabledElements = $('.flickity-enabled');
    if (flickityEnabledElements.data('flickity')) {
        // Execute the 'reposition' method only if Flickity is enabled
        flickityEnabledElements.flickity('resize');
    }
}

async function YPTSidebarClose() {
    console.log('YPTSidebarClose');
    $("#sidebar").removeClass('animate__bounceInLeft');
    $("#sidebar").addClass('animate__bounceOutLeft');
    Cookies.set("menuOpen", false, { expires: 365, path: '/' });
    setTimeout(function () {
        YPTSidebarUncompress();
        $('body').removeClass('youtube');
        $("#sidebar").hide();
        setTimeout(function () {
            flickityReload();
        }, 500);
    }, 500);
    youTubeMenuIsOpened = false;
}


async function YPTSidebarCompress() {
    console.log('YPTSidebarCompress');
    Cookies.set("menuCompressed", true, { expires: 365, path: '/' });
    $('body').addClass('compressedMenu');
    setTimeout(function () {
        flickityReload();
    }, 500);
    youTubeMenuIsCompressed = true;
}
async function YPTSidebarUncompress() {
    console.log('YPTSidebarUncompress');
    Cookies.set("menuCompressed", false, { expires: 365, path: '/' });
    $('body').removeClass('compressedMenu');
    setTimeout(function () {
        flickityReload();
    }, 500);
    youTubeMenuIsCompressed = false;
}

function YPTSidebarIsCompressed() {
    return $('body').hasClass('compressedMenu');
}

async function YPTSidebarCompressToggle() {
    if (YPTSidebarIsCompressed()) {
        YPTSidebarUncompress();
    } else {
        YPTSidebarCompress();
    }
}

async function YPTHidenavbar() {
    if (typeof inIframe == 'undefined') {
        setTimeout(function () {
            YPTHidenavbar()
        }, 500);
    } else {
        if (inIframe()) {
            $("#mainNavBar").hide();
            $("body").css("padding-top", "0");
        }
    }
}

$(document).ready(function () {
    var menuCompressed = Cookies.get("menuCompressed");
    if (menuCompressed === "true" && !inIframe()) {
        YPTSidebarCompress();
    } else {
        YPTSidebarUncompress();
    }

    var menuOpen = Cookies.get("menuOpen");
    if (menuOpen === "true" && !inIframe()) {
        YPTSidebarOpen();
    } else {
        YPTSidebarClose();
    }

    setTimeout(function () {
        flickityReload();
    }, 5000);
});
