/*! Bootstrap integration for DataTables' SearchPanes
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-ju', 'datatables.net-searchpanes'], function ($) {
            return factory($, window, document);
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
                $ = require('datatables.net-ju')(root, $).$;
            }
            if (!$.fn.dataTable.SearchPanes) {
                // eslint-disable-next-line @typescript-eslint/no-var-requires
                require('datatables.net-searchpanes')(root, $);
            }
            return factory($, root, root.document);
        };
    }
    else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document) {
    'use strict';
    var dataTable = $.fn.dataTable;
    $.extend(true, dataTable.SearchPane.classes, {
        disabledButton: 'dtsp-paneInputButton dtsp-disabledButton',
        paneButton: 'dtsp-paneButton ui-button',
        topRow: 'dtsp-topRow ui-state-default'
    });
    $.extend(true, dataTable.SearchPanes.classes, {
        clearAll: 'dtsp-clearAll ui-button',
        container: 'dtsp-searchPanes',
        panes: 'dtsp-panesContainer fg-toolbar ui-toolbar ui-widget-header'
    });
    return dataTable.searchPanes;
}));
