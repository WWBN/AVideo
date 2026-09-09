
var seachFormIsRunning = 0;
var youTubeMenuIsOpened = false;
var youTubeMenuIsCompressed = false;
var sidebarAnimationTimeout;

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
        YPTSidebarBeginInteraction();
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
            console.log("buttonMyNavbar logged 1");
            closeRightMenu();
        } else {
            console.log("buttonMyNavbar logged 2");
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
    var selector = '#buttonMenu svg';
    $(selector).removeClass('active');
    YPTSidebarClose();
}
async function openLeftMenu() {
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
    return $('#myNavbar').is(':visible');
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

function YPTSidebarBeginInteraction() {
    clearTimeout(sidebarAnimationTimeout);
    $('body').addClass('sidebarAnimating');
    sidebarAnimationTimeout = setTimeout(function () {
        $('body').removeClass('sidebarAnimating');
        flickityReload();
    }, 250);
}

async function YPTSidebarOpen() {
    var selector = '#buttonMenu svg';
    $(selector).addClass('active');
    $('#buttonMenu').attr('aria-expanded', 'true');
    $('body').addClass('youtube');
    $("#sidebar").removeClass('animate__animated animate__bounceInLeft animate__bounceOutLeft');
    $("#sidebar").show();
    Cookies.set("menuOpen", true, avideoCookieOptions(365));
    flickityReload();
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
    $('#buttonMenu').attr('aria-expanded', 'false');
    $('#buttonMenu svg').removeClass('active');
    $("#sidebar").removeClass('animate__animated animate__bounceInLeft animate__bounceOutLeft');
    Cookies.set("menuOpen", false, avideoCookieOptions(365));
    $('body').removeClass('youtube');
    $("#sidebar").hide();
    YPTSidebarUncompress();
    youTubeMenuIsOpened = false;
}


async function YPTSidebarCompress() {
    Cookies.set("menuCompressed", true, avideoCookieOptions(365));
    $('body').addClass('compressedMenu');
    flickityReload();
    youTubeMenuIsCompressed = true;
}
async function YPTSidebarUncompress() {
    Cookies.set("menuCompressed", false, avideoCookieOptions(365));
    $('body').removeClass('compressedMenu');
    flickityReload();
    youTubeMenuIsCompressed = false;
}

function YPTSidebarIsCompressed() {
    return $('body').hasClass('compressedMenu');
}

async function YPTSidebarCompressToggle() {
    YPTSidebarBeginInteraction();
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
    // Initialization must not animate, schedule a close, or overwrite preferences.
    var restored = $('body').hasClass('sidebarLayout');
    youTubeMenuIsOpened = restored ? YPTSidebarIsOpen() : Cookies.get("menuOpen") === "true" && !inIframe();
    youTubeMenuIsCompressed = youTubeMenuIsOpened && (restored ? YPTSidebarIsCompressed() : Cookies.get("menuCompressed") === "true");
    $('body').addClass('sidebarLayout').removeClass('sidebarAnimating');
    $('#sidebar').removeClass('animate__animated animate__bounceInLeft animate__bounceOutLeft');
    $('body').toggleClass('youtube', youTubeMenuIsOpened);
    $('body').toggleClass('compressedMenu', youTubeMenuIsCompressed);
    $('#sidebar').toggle(youTubeMenuIsOpened);
    $('#buttonMenu svg').toggleClass('active', youTubeMenuIsOpened);
    $('#buttonMenu').attr('aria-expanded', String(youTubeMenuIsOpened));

    setTimeout(function () {
        flickityReload();
    }, 5000);
});

// Native horizontal scrolling keeps plugin buttons intact, including text-only actions.
$(function () {
    var strip = document.getElementById('myNavbar');
    if (!strip) return;
    var $strip = $(strip);
    var $previous = $('#lastItemOnMenu > .navbarScrollPrevious');
    var $next = $('#lastItemOnMenu > .navbarScrollNext');
    var frame;
    function updateArrows() {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(function () {
            // Include arrow widths when deciding whether overflow has actually disappeared.
            var arrowWidth = ($previous.is(':visible') ? $previous.outerWidth(true) : 0) +
                ($next.is(':visible') ? $next.outerWidth(true) : 0);
            var overflowing = strip.scrollWidth > strip.clientWidth + arrowWidth + 1;
            $previous.add($next).prop('hidden', !overflowing);
            $previous.prop('disabled', strip.scrollLeft <= 1);
            $next.prop('disabled', strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 1);
        });
    }
    $previous.add($next).on('click', function () {
        closePopups();
        strip.scrollBy({left: (this === $previous[0] ? -1 : 1) * Math.max(120, strip.clientWidth * .7),
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
    });
    // Fixed positioning escapes overflow clipping without moving plugin menu elements.
    function positionMenu($menu, anchor) {
        $menu.data('navbarScrollAnchor', anchor);
        var rect = anchor.getBoundingClientRect();
        var bounds = strip.getBoundingClientRect();
        var width = Math.min($menu.outerWidth() || $menu.find('.select2-dropdown').outerWidth() || rect.width, window.innerWidth - 16);
        var left = Math.max(8, Math.min(rect.left, bounds.right - width, window.innerWidth - width - 8));
        $menu.css({position: 'fixed', left: left, right: 'auto', top: rect.bottom + 4, bottom: 'auto',
            maxWidth: window.innerWidth - 16, maxHeight: Math.max(80, window.innerHeight - rect.bottom - 12)});
    }
    function syncDropdowns() {
        $strip.find('.dropdown-menu').each(function () {
            var $menu = $(this);
            var $parent = $menu.parent();
            if ($parent.hasClass('open')) {
                if (!$menu.hasClass('navbarScrollDropdown')) {
                    $menu.data('navbarOriginalStyle', $menu.attr('style') || '').addClass('navbarScrollDropdown');
                }
                positionMenu($menu, $parent.children('[data-toggle="dropdown"]')[0] || $parent[0]);
            } else if ($menu.hasClass('navbarScrollDropdown')) {
                $menu.removeClass('navbarScrollDropdown').attr('style', $menu.data('navbarOriginalStyle'));
            }
        });
    }
    $strip.on('shown.bs.dropdown hidden.bs.dropdown', syncDropdowns);
    // Plugins can toggle .open directly or consume Bootstrap events on their wrappers.
    var dropdownObserver = new MutationObserver(function (records) {
        if (records.some(function (record) {
            return record.type === 'childList' || (record.target.matches('.btn-group, .dropdown, .dropup') &&
                !record.target.matches('.dropdown-menu'));
        })) syncDropdowns();
    });
    dropdownObserver.observe(strip, {subtree: true, childList: true, attributes: true, attributeFilter: ['class']});
    syncDropdowns();
    function closePopups() {
        $strip.find('.open > [data-toggle="dropdown"]').dropdown('toggle');
        $strip.find('select.select2-hidden-accessible').each(function () { $(this).select2('close'); });
    }
    $strip.on('scroll', function () {
        $strip.find('.navbarScrollDropdown:visible').each(function () {
            var anchor = $(this).data('navbarScrollAnchor');
            if (anchor) positionMenu($(this), anchor);
        });
        updateArrows();
    });
    // Select2's default dropdown parent may be inside the scroller, too.
    $strip.on('select2:open', 'select', function () {
        var select = this;
        requestAnimationFrame(function () {
            var $popup = $(select).parent().children('.select2-container--open').filter(function () {
                return $(this).find('.select2-dropdown').length;
            });
            $popup.addClass('navbarScrollDropdown');
            positionMenu($popup, $(select).next('.select2-container')[0] || select);
        });
    });
    $(window).on('resize.navbarScroll', function () { closePopups(); updateArrows(); });
    if (typeof ResizeObserver !== 'undefined') {
        var observer = new ResizeObserver(updateArrows);
        observer.observe(strip);
        if (strip.firstElementChild) observer.observe(strip.firstElementChild);
        observer.observe(document.getElementById('lastItemOnMenu'));
    }
    updateArrows();
});

// Dropdown tooltips open to the side so their menus remain unobstructed.
$(function () {
    var navbar = document.getElementById('mainNavBar');
    if (!navbar) return;
    function prepareTooltip(event) {
        var trigger = event.target.closest('[data-toggle="tooltip"]');
        if (!trigger || !navbar.contains(trigger) || trigger.closest('#sidebar')) return;
        var $trigger = $(trigger);
        var toggle = trigger.closest('[data-toggle="dropdown"]') || trigger.querySelector('[data-toggle="dropdown"]');
        var placement = 'bottom';
        if (toggle && !trigger.closest('.dropdown-menu')) {
            var rect = toggle.getBoundingClientRect();
            var bounds = navbar.getBoundingClientRect();
            placement = bounds.right - rect.right >= rect.left - bounds.left ? 'right' : 'left';
        }
        $trigger.attr('data-placement', placement).attr('data-container', 'body');
        $trigger.data('placement', placement).data('container', 'body');
        var tooltip = $trigger.data('bs.tooltip');
        if (tooltip) {
            tooltip.options.placement = placement;
            tooltip.options.container = 'body';
            tooltip.options.viewport = {selector: 'body', padding: 8};
            tooltip.tip().addClass('navbarTooltip');
        }
    }
    navbar.addEventListener('mouseover', prepareTooltip, true);
    navbar.addEventListener('focusin', prepareTooltip, true);
    $(navbar).on('show.bs.tooltip', '[data-toggle="tooltip"]', prepareTooltip);
});
