/** Shared presentation for both native DataTables and avideoDataTable() callers. */
(function ($) {
    'use strict';

    var loadingText = (document.currentScript && document.currentScript.getAttribute('data-loading-text')) || 'Loading...';

    function positionLoading($wrapper) {
        var settings = $wrapper.data('avideoLoadingSettings');
        if (!settings || !$wrapper.hasClass('avideo-datatable-busy') || !$wrapper.is(':visible')) {
            return;
        }
        var table = settings.nTable;
        var body = table.tBodies[0];
        if (!body) {
            return;
        }
        var wrapperRect = $wrapper[0].getBoundingClientRect();
        var tableRect = table.getBoundingClientRect();
        var bodyRect = body.getBoundingClientRect();
        var viewport = $(table).closest('.dataTables_scrollBody, .table-responsive')[0] || $wrapper[0];
        var viewportRect = viewport.getBoundingClientRect();
        var left = Math.max(tableRect.left, wrapperRect.left, viewportRect.left);
        var right = Math.min(tableRect.right, wrapperRect.right, viewportRect.left + viewport.clientWidth);
        var top = Math.max(bodyRect.top, viewportRect.top);
        var height = Math.max(0, Math.min(240, bodyRect.bottom - top, viewportRect.top + viewport.clientHeight - top));
        var widths = settings.aoColumns.map(function (column) {
            return column.nTh.getBoundingClientRect().width;
        }).filter(function (width) { return width > 0; });
        var $loading = $wrapper.children('.avideo-datatable-loading');
        var $rows = $loading.find('.avideo-inline-loading-row');
        if ($loading.data('columns') !== widths.length) {
            $rows.empty();
            widths.forEach(function () {
                $rows.append('<div class="avideo-datatable-placeholder"><span class="avideo-inline-loading-line"></span></div>');
            });
            $loading.data('columns', widths.length);
        }
        $rows.css('grid-template-columns', widths.map(function (width) { return width + 'px'; }).join(' '));
        $loading.find('.avideo-datatable-skeleton').css('transform', 'translateX(' + (tableRect.left - left) + 'px)');
        $loading.css({
            left: left - wrapperRect.left + $wrapper[0].scrollLeft,
            top: top - wrapperRect.top + $wrapper[0].scrollTop,
            width: Math.max(0, right - left),
            height: height,
            visibility: height > 0 && right > left ? 'visible' : 'hidden'
        });
    }

    // New integration - no global DataTables presentation handler exists in view/js/.
    // Listen before the library loads, including tables initialized by plugin head scripts.
    function prepareTable(settings) {
        var $wrapper = $(settings.nTableWrapper);
        if (!$wrapper.length || $wrapper.hasClass('avideo-datatable')) {
            return $wrapper;
        }
        $wrapper.addClass('avideo-datatable').data('avideoLoadingSettings', settings);
        $('<div class="avideo-datatable-loading panel panel-default" role="status" aria-live="polite"/>')
            .append('<div class="avideo-datatable-skeleton" aria-hidden="true"><div class="avideo-inline-loading-row"></div><div class="avideo-inline-loading-row"></div><div class="avideo-inline-loading-row"></div></div>')
            .append($('<div class="avideo-datatable-loading-status panel panel-default"/>')
                .append('<span class="loader text-primary" aria-hidden="true"></span>')
                .append($('<span class="avideo-datatable-loading-label"/>').text(loadingText)))
            .hide()
            .appendTo($wrapper);
        if (typeof ResizeObserver !== 'undefined') {
            var observer = new ResizeObserver(function () { positionLoading($wrapper); });
            observer.observe(settings.nTable);
            observer.observe($wrapper[0]);
            $wrapper.data('avideoLoadingObserver', observer);
        }
        $(settings.nTable).closest('.dataTables_scrollBody, .table-responsive')
            .on('scroll.avideoLoading' + settings.sInstance, function () { positionLoading($wrapper); });
        return $wrapper;
    }

    $(document).on('preInit.dt.avideoTheme init.dt.avideoTheme draw.dt.avideoTheme column-sizing.dt.avideoTheme', function (event, settings) {
        if (!event.namespace || !settings || event.target !== settings.nTable) {
            return;
        }
        positionLoading(prepareTable(settings));
    }).on('processing.dt.avideoTheme', function (event, settings, processing) {
        if (!event.namespace || !settings || event.target !== settings.nTable) {
            return;
        }
        var $wrapper = prepareTable(settings);
        $wrapper.toggleClass('avideo-datatable-busy', processing);
        $wrapper.children('.avideo-datatable-loading').toggle(processing);
        $(settings.nTable).attr('aria-busy', processing ? 'true' : 'false');
        if (processing) {
            positionLoading($wrapper);
            requestAnimationFrame(function () { positionLoading($wrapper); });
        }
    }).on('destroy.dt.avideoTheme', function (event, settings) {
        if (!settings || event.target !== settings.nTable) {
            return;
        }
        $(settings.nTable).removeAttr('aria-busy');
        var observer = $(settings.nTableWrapper).data('avideoLoadingObserver');
        if (observer) {
            observer.disconnect();
        }
        $(settings.nTable).closest('.dataTables_scrollBody, .table-responsive').off('.avideoLoading' + settings.sInstance);
        $(settings.nTableWrapper).removeClass('avideo-datatable avideo-datatable-busy')
            .removeData('avideoLoadingSettings avideoLoadingObserver')
            .children('.avideo-datatable-loading').remove();
    });

    $(window).on('resize.avideoDataTableTheme', function () {
        $('.avideo-datatable-busy').each(function () {
            positionLoading($(this));
        });
    });
    $(document).on('shown.bs.tab.avideoTheme shown.bs.modal.avideoTheme', function () {
        $('.avideo-datatable-busy').each(function () {
            positionLoading($(this));
        });
    });
})(jQuery);
