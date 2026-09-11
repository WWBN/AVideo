var video;
var cat;
var videos_id;
var $carousel = [];

function isFlickityEnabled(selector) {
    return $(selector).hasClass('flickity-enabled');
}

function resizeFlixCarousels() {
    for (var i = 0; i < $carousel.length; i++) {
        $carousel[i].flickity('resize');
    }
}

function resizeFlixHero() {
    $('.flix-hero #bg_container').each(function () {
        // Cover the hero with a centered trailer viewport, including tall portrait layouts.
        var width = Math.max(this.clientWidth, this.clientHeight * 16 / 9);
        this.style.setProperty('--flix-video-width', width + 'px');
        this.style.setProperty('--flix-video-height', (width * 9 / 16) + 'px');
    });
}

$(function () {
    // Bootswatch themes predate CSS variables; read the palette they actually render.
    var bodyStyle = window.getComputedStyle(document.body);
    $('.flickity-area').each(function () {
        if (bodyStyle.backgroundColor !== 'rgba(0, 0, 0, 0)' && bodyStyle.backgroundColor !== 'transparent') {
            this.style.setProperty('--flix-surface', bodyStyle.backgroundColor);
        }
        this.style.setProperty('--flix-text', bodyStyle.color);
    });
    $('#footerDiv, #mainContainer.flickity-area').show();
    startModeFlix('');
    $('#loading').hide();
    resizeFlixCarousels();
    resizeFlixHero();
    $(window).on('resize.flixHero', resizeFlixHero);
    $('a[data-toggle="tab"][href="#channelHome"]').on('shown.bs.tab', function () {
        resizeFlixCarousels();
        resizeFlixHero();
    });
    if (typeof ResizeObserver !== 'undefined') {
        var heroObserver = new ResizeObserver(resizeFlixHero);
        $('.flix-hero').each(function () { heroObserver.observe(this); });
    }

    // The sidebar changes the available width without a window resize.
    var mainContainer = document.querySelector('.flix-catalog') || document.getElementById('mainContainer');
    if (mainContainer && typeof ResizeObserver !== 'undefined') {
        var previousWidth = 0;
        var observer = new ResizeObserver(function (entries) {
            var width = entries[0].contentRect.width;
            if (width !== previousWidth) {
                previousWidth = width;
                resizeFlixCarousels();
            }
        });
        observer.observe(mainContainer);
    }

    $(document).on('click.flix', '.flix-close-details', function () {
        closeFlixDetails($(this).closest('.poster'), true);
    }).on('keydown.flix', function (event) {
        if (event.key === 'Escape' && !event.isDefaultPrevented()
            && !$(event.target).closest('.swal-overlay, .modal, #divIframeFull, .dropdown-menu').length
            && !$('#divIframeFull:visible, .modal:visible, .swal-overlay--show-modal').length) {
            var $poster = $('.flickity-area .poster:visible').last();
            closeFlixDetails($poster, true);
        }
    });
});

function closeFlixDetails($poster, restoreFocus) {
    if (!$poster.length) {
        return;
    }
    var $card = $('.thumbsImage[aria-controls="' + $poster.attr('id') + '"]');
    $poster.stop(true, true).slideUp(window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180);
    $card.removeClass('active').attr('aria-expanded', 'false');
    if (restoreFocus && $card.length) {
        $card[0].focus({preventScroll: true});
    }
}

function startModeFlix(container) {
    // Keep the existing placeholder if a thumbnail is missing; never retry it in a loop.
    $(container + '.carousel-cell-image').off('error.flix').on('error.flix', function () {
        var $image = $(this);
        if ($image.hasClass('thumbsGIF')) {
            $image.stop(true, true).addClass('hidden');
            return;
        }
        var fallback = $image.attr('data-flix-fallback');
        if (fallback) {
            $image.removeAttr('data-flix-fallback').addClass('flix-image-fallback flickity-lazyloaded').attr('src', fallback);
        }
    }).filter('.flickity-lazyerror').each(function () {
        $(this).triggerHandler('error.flix');
    });
    // Namespace bindings so repeated initialization (including AJAX rows) stays idempotent.
    var $thumbs = $(container + '.thumbsImage');
    $thumbs.off('.flix').on('mouseenter.flix', function () {
        if (window.matchMedia('(hover: hover) and (prefers-reduced-motion: no-preference)').matches) {
            $(this).find('.thumbsGIF').each(function () {
                var $preview = $(this);
                var source = $preview.attr('data-flix-preview');
                if (source) {
                    // Download animated previews only when the visitor actually hovers a card.
                    $preview.one('load.flixPreview', function () {
                        $preview.addClass('flickity-lazyloaded');
                        if ($preview.closest('.thumbsImage').is(':hover')) {
                            $preview.stop(true, true).fadeIn(150);
                        }
                    }).one('error.flixPreview', function () {
                        $preview.addClass('hidden');
                    }).removeAttr('data-flix-preview').attr('src', source);
                } else if (this.complete && this.naturalWidth > 0) {
                    $preview.stop(true, true).fadeIn(150);
                }
            });
        }
    }).on('mouseleave.flix', function () {
        $(this).find('.thumbsGIF').stop(true, true).fadeOut(150);
    });
    $thumbs.filter('[crc]').on('keydown.flix', function (event) {
        if (event.target === this && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            $(this).trigger('click');
        }
    }).on('click.flix', function (event) {
        if ($(event.target).closest('a, button').length) {
            return;
        }
        var $card = $(this);
        var crc = $card.attr('crc');
        var $poster = $('#poster' + crc);
        if (!$poster.length) {
            return;
        }
        var wasOpen = $card.attr('aria-expanded') === 'true';
        var $area = $card.closest('.flickity-area');
        $area.find('.thumbsImage.active').each(function () {
            closeFlixDetails($('#' + $(this).attr('aria-controls')), false);
        });
        if (wasOpen) {
            return;
        }
        $card.addClass('active').attr('aria-expanded', 'true');
        $poster.css('background-image', 'url("' + $poster.attr('poster') + '")');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        $poster.stop(true, true).slideDown(reduceMotion ? 0 : 180, function () {
            resizeFlixCarousels();
            var navbarHeight = $('#mainNavBar').outerHeight() || 0;
            var top = $card.closest('.carousel').offset().top - navbarHeight - 16;
            $('html, body').stop(true).animate({scrollTop: Math.max(0, top)}, reduceMotion ? 0 : 180);
        });
        $card.closest('.carousel').flickity('stopPlayer');

        var ajaxLoad = $card.attr('ajaxLoad');
        var $ajaxTarget = $('#ajaxLoad-' + crc);
        if (ajaxLoad && !$ajaxTarget.attr('ajaxLoaded') && !$ajaxTarget.data('flixLoading')) {
            $ajaxTarget.data('flixLoading', true);
            modal.showPleaseWait();
            $ajaxTarget.load(ajaxLoad, function (response, status) {
                $ajaxTarget.removeData('flixLoading');
                modal.hidePleaseWait();
                if (status === 'error') {
                    $ajaxTarget.empty().append($('<p>').text(__('An error occurred')));
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-default',
                        text: __('Please try again')
                    }).on('click.flix', function () {
                        closeFlixDetails($poster, true);
                        $card.trigger('click');
                    }).appendTo($ajaxTarget);
                    avideoToastError(__('An error occurred'));
                } else {
                    $ajaxTarget.attr('ajaxLoaded', 1);
                    resizeFlixCarousels();
                }
            });
        }
    });

    $(container + '.carousel').each(function () {
        var $row = $(this);
        if ($row.data('flixInitialized') || !$row.attr('data-flickity')) {
            return;
        }
        var options = JSON.parse($row.attr('data-flickity'));
        options.setGallerySize = true;
        options.lazyLoad = 2;
        options.percentPosition = false;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            options.autoPlay = false;
        }
        $row.data('flixInitialized', true);
        // Flickity skips resize while moving; finish sidebar/viewport changes on settle.
        $row.on('settle.flix', function () {
            $row.flickity('resize');
        });
        $carousel.push($row.flickity(options));
        if (options.autoPlay === false) {
            $row.flickity('stopPlayer');
        }
        $row.on('focusin.flix', '.thumbsImage[crc]', function () {
            var index = $(this).closest('.carousel-cell').index();
            $row.flickity('selectCell', index, false, true);
            $row.flickity('stopPlayer');
        });
    });

    if (typeof transformLinksToEmbed === 'function') {
        transformLinksToEmbed(container + ' a.canWatchPlayButton');
    }
}