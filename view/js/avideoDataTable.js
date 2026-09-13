/**
 * Shared helper for pages migrated from the legacy Bootgrid grid to DataTables.
 *
 * Provides:
 *   1) window.avideoDataTable(selector, options) - explicit per-element DataTable init. Marks
 *      every element it touches as "registered" so the bridge below knows it opted in. Pass
 *      options.avideoControls (true, or {refresh:bool, columns:bool, lengthMenu:bool}) to opt in
 *      to the shared Refresh/"Columns"/"All" controls Bootgrid provided by default
 *      (navigation:3 + columnSelection:true + rowCount:[10,25,50,-1]) - see
 *      avideoApplyStandardControls() below. Pages that already build their own equivalent
 *      controls (managerVideos_body.php, managerPlugins_body.php) do not opt in.
 *   2) window.avideoDataTableAjax(config) - a shared ajax function builder for the common
 *      {current,rowCount,total,rows} legacy response shape, with error handling: a failed/invalid
 *      response always finalizes the DataTables draw (clears the "Processing" state instead of
 *      hanging) and surfaces a toast, instead of silently leaving stale rows with no explanation.
 *   3) A single $.fn.bootgrid legacy bridge, installed once, so old call sites
 *      ($('#grid').bootgrid('reload'|'getCurrentRows'|'getCurrentPage'|'search'|'destroy')) keep
 *      working when the target element was registered via avideoDataTable(). Any element that
 *      was NOT migrated still falls through to the real Bootgrid plugin untouched. A DataTable
 *      that exists but was never registered via avideoDataTable() (e.g. some other script called
 *      $(...).DataTable() directly) is deliberately left alone: not translated, not handed to
 *      Bootgrid (which would corrupt it), see avideoBootgridReactToUnregisteredDataTable() below.
 *      Mixed collections (e.g. $('.bootgrid-table')) are split per-element and each part is
 *      routed correctly.
 *
 * See docs/table-usage-map.md for the migration history and objects/bootGrid.php for the
 * server-side sort/search normalization these translated calls end up relying on.
 */
(function ($) {
    'use strict';

    if (typeof $ === 'undefined' || !$.fn) {
        return;
    }

    if ($.fn.bootgrid && $.fn.bootgrid.__avideoBridge) {
        return; // already installed (e.g. script included twice)
    }

    var REGISTRY_KEY = 'avideoDataTableRegistered';

    function isRegistered(el) {
        return !!($.fn.DataTable && $.fn.DataTable.isDataTable(el) && $(el).data(REGISTRY_KEY) === true);
    }

    function translate(str) {
        return (typeof __ === 'function') ? __(str) : str;
    }

    // Restores the controls Bootgrid provided by default (navigation:3 + columnSelection:true +
    // rowCount:[10,25,50,-1]) using the DataTables Buttons extension already bundled
    // (view/css/DataTables/datatables.js includes b-colvis) instead of hand-rolled UI.
    function avideoApplyStandardControls(options, config) {
        config = config || {};
        var opts = $.extend({}, options);
        var buttons = [];
        if (config.refresh !== false) {
            buttons.push({
                text: '<i class="fas fa-sync"></i> ' + translate('Refresh'),
                action: function (e, dt) {
                    dt.ajax.reload(null, false);
                }
            });
        }
        if (config.columns !== false) {
            buttons.push({ extend: 'colvis', text: translate('Columns') });
        }
        if (buttons.length) {
            opts.dom = opts.dom || 'Blfrtip';
            if (opts.dom.indexOf('B') === -1) {
                opts.dom = 'B' + opts.dom;
            }
            opts.buttons = (opts.buttons || []).concat(buttons);
        }
        if (config.lengthMenu !== false && !opts.lengthMenu) {
            opts.lengthMenu = [[10, 25, 50, -1], [10, 25, 50, translate('All')]];
        }
        return opts;
    }

    window.avideoDataTable = function (selector, options) {
        options = options || {};
        var controlsConfig = options.avideoControls;
        if (controlsConfig) {
            options = avideoApplyStandardControls(options, controlsConfig === true ? {} : controlsConfig);
        }
        delete options.avideoControls;
        var $el = $(selector);
        $el.each(function () {
            // DataTables 1.x needs an explicit table width to restore relative sizing and
            // register its resize handler; CSS width alone leaves a frozen pixel width.
            if (!$.fn.DataTable.isDataTable(this) && !this.getAttribute('width') && !this.style.width) {
                $(this).attr('width', '100%');
            }
        });
        var dt = ($.fn.DataTable && $.fn.DataTable.isDataTable($el)) ? $el.DataTable() : $el.DataTable(options);
        $el.each(function () {
            $(this).data(REGISTRY_KEY, true);
        });
        return dt;
    };

    // Shared ajax function builder for the legacy {current,rowCount,total,rows} response shape.
    // config.url: string or function returning the URL. config.extraParams: object or function
    // merged into the request data. config.legacyResponse (default true): translate the legacy
    // shape into DataTables' {draw,recordsTotal,recordsFiltered,data}; pass false if the endpoint
    // already speaks the native DataTables shape. On error (network failure or a non-JSON/invalid
    // response), always finalizes the draw with an empty result instead of leaving the table stuck
    // on "Processing..." with stale rows, and shows a toast if avideoToastError() is available.
    window.avideoDataTableAjax = function (config) {
        config = config || {};
        return function (data, callback) {
            if (typeof config.extraParams === 'function') {
                $.extend(data, config.extraParams());
            } else if (config.extraParams) {
                $.extend(data, config.extraParams);
            }
            $.ajax({
                url: typeof config.url === 'function' ? config.url() : config.url,
                data: data,
                dataType: 'json',
                success: function (json) {
                    if (config.legacyResponse === false) {
                        callback(json);
                        return;
                    }
                    callback({ draw: data.draw, recordsTotal: json.total, recordsFiltered: json.total, data: json.rows });
                },
                error: function () {
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                    if (typeof avideoToastError === 'function') {
                        avideoToastError(translate('Failed to load data. Please try again.'));
                    }
                }
            });
        };
    };

    // Cleans up the registry when a table is destroyed, whether that happened through the bridge
    // below or through a direct call to the native DataTables API - so a later avideoDataTable()
    // re-initializing the same element starts with no residual registration state.
    $(document).on('destroy.dt', function (e) {
        $(e.target).removeData(REGISTRY_KEY);
    });

    var _avideoOriginalBootgrid = $.fn.bootgrid;

    function avideoBootgridReactToUnregisteredDataTable(action) {
        console.warn('avideoDataTable bootgrid bridge: ignoring .bootgrid("' + action + '") on a DataTable ' +
            'that was not initialized via avideoDataTable() - it is left untouched.');
    }

    // Matches Grid.prototype.getCurrentPage()/getCurrentRows() in view/js/bootgrid/jquery.bootgrid.js:
    // both only ever report on the current page's rows, in display order, respecting whatever
    // filter is currently applied. DataTables' rows({page:'current'}) selector already implies
    // order:'current'+search:'applied' by itself (see DataTables' own _selector_row_indexes),
    // and for a table with paging disabled (or "All" selected, both report length===-1) the
    // "current page" spans every row that passed the filter - exactly the semantics we want,
    // including collapsing to [] when the filter matches nothing.
    function avideoBootgridGetter(dt, action) {
        switch (action) {
            case 'getCurrentPage':
                var info = dt.page.info();
                if (info.recordsDisplay === 0 || info.length === -1) {
                    return 1;
                }
                return Math.floor(info.start / info.length) + 1;
            case 'getCurrentRows':
                return dt.rows({ page: 'current', order: 'current', search: 'applied' }).data().toArray();
            default:
                throw new Error('avideoDataTable bootgrid bridge: unsupported getter "' + action + '"');
        }
    }

    // Matches Grid.prototype.reload()/search(): reload() preserves the current page, search()
    // always returns to page 1 (DataTables' own .draw() already does this by default).
    function avideoBootgridCommand(dt, action, args) {
        switch (action) {
            case 'reload':
                dt.ajax.reload(null, false);
                return;
            case 'search':
                var phrase = args[0] || '';
                dt.search(phrase);
                $(dt.table().container()).find('.dataTables_filter input').val(phrase);
                dt.draw();
                return;
            case 'destroy':
                dt.destroy();
                return;
            default:
                throw new Error('avideoDataTable bootgrid bridge: unsupported command "' + action + '"');
        }
    }

    $.fn.bootgrid = function (option) {
        var args = Array.prototype.slice.call(arguments, 1);
        var isGetter = typeof option === 'string' && option.indexOf('get') === 0;
        var isCommand = typeof option === 'string' && !isGetter;
        var firstEl = this.get(0);
        var firstIsDataTable = !!(firstEl && $.fn.DataTable && $.fn.DataTable.isDataTable(firstEl));
        var returnValue;
        var legacyElements = $();

        this.each(function () {
            var registered = isRegistered(this);
            var isDataTableEl = $.fn.DataTable && $.fn.DataTable.isDataTable(this);
            if (registered) {
                var dt = $(this).DataTable();
                if (isCommand) {
                    avideoBootgridCommand(dt, option, args);
                } else if (isGetter && this === firstEl) {
                    returnValue = avideoBootgridGetter(dt, option);
                }
                // non-string (init) option on an already-registered DataTable: intentionally
                // ignored, never re-initializes/converts it into a Bootgrid instance.
            } else if (isDataTableEl) {
                // A DataTable exists here but was never handed to avideoDataTable(): don't
                // translate (we don't know it opted into this contract) and don't fall through
                // to Bootgrid either (auto-initializing Bootgrid on top of a DataTable-managed
                // <table> would corrupt it).
                if (isCommand || (isGetter && this === firstEl)) {
                    avideoBootgridReactToUnregisteredDataTable(option);
                }
            } else {
                legacyElements = legacyElements.add(this);
            }
        });

        if (legacyElements.length && typeof _avideoOriginalBootgrid === 'function') {
            var legacyReturn = _avideoOriginalBootgrid.apply(legacyElements, arguments);
            if (isGetter && !firstIsDataTable) {
                returnValue = legacyReturn;
            }
        }

        return isGetter ? returnValue : this;
    };

    $.fn.bootgrid.__avideoBridge = true;
    if (_avideoOriginalBootgrid) {
        $.fn.bootgrid.Constructor = _avideoOriginalBootgrid.Constructor;
        $.fn.bootgrid.noConflict = function () {
            $.fn.bootgrid = _avideoOriginalBootgrid;
            return this;
        };
    }
})(jQuery);

