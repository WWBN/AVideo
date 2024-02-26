/*! Bootstrap integration for DataTables' SearchPanes
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-zf', 'datatables.net-searchpanes'], function ($) {
            return factory($);
        });
    }
    else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }
            if (!$ || !$.fn.dataTable) {
                // eslint-disable-next-line @typescript-eslint/no-var-requires
                $ = require('datatables.net-zf')(root, $).$;
            }
            if (!$.fn.dataTable.SearchPanes) {
                // eslint-disable-next-line @typescript-eslint/no-var-requires
                require('datatables.net-searchpanes')(root, $);
            }
            return factory($);
        };
    }
    else {
        // Browser
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    var dataTable = $.fn.dataTable;
    $.extend(true, dataTable.SearchPane.classes, {
        buttonGroup: 'secondary button-group',
        disabledButton: 'disabled',
        narrow: 'dtsp-narrow',
        narrowButton: 'dtsp-narrowButton',
        narrowSearch: 'dtsp-narrowSearch',
        paneButton: 'secondary button',
        pill: 'badge secondary',
        search: 'search',
        searchLabelCont: 'searchCont',
        show: 'col',
        table: 'unstriped'
    });
    $.extend(true, dataTable.SearchPanes.classes, {
        clearAll: 'dtsp-clearAll button secondary',
        collapseAll: 'dtsp-collapseAll button secondary',
        disabledButton: 'disabled',
        panes: 'panes dtsp-panesContainer',
        showAll: 'dtsp-showAll button secondary',
        title: 'dtsp-title'
    });
    return dataTable.searchPanes;
}));
