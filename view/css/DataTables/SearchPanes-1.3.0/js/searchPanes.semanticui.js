/*! semantic ui integration for DataTables' SearchPanes
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-se', 'datatables.net-searchpanes'], function ($) {
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
                $ = require('datatables.net-se')(root, $).$;
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
        buttonGroup: 'right floated ui buttons column',
        disabledButton: 'disabled',
        narrowSearch: 'dtsp-narrowSearch',
        narrowSub: 'dtsp-narrow',
        paneButton: 'ui button',
        paneInputButton: 'circular search link icon',
        topRow: 'row dtsp-topRow'
    });
    $.extend(true, dataTable.SearchPanes.classes, {
        clearAll: 'dtsp-clearAll ui button',
        disabledButton: 'disabled'
    });
    // This override is required for the integrated search Icon in sematic ui
    dataTable.SearchPane.prototype._searchContSetup = function () {
        $('<i class="' + this.classes.paneInputButton + '"></i>').appendTo(this.dom.searchCont);
    };
    return dataTable.searchPanes;
}));
