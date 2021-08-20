/*! Bulma ui integration for DataTables' SearchBuilder
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-bs5', 'datatables.net-searchbuilder'], function ($) {
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
                $ = require('datatables.net-bs5')(root, $).$;
            }
            if (!$.fn.dataTable.searchBuilder) {
                // eslint-disable-next-line @typescript-eslint/no-var-requires
                require('datatables.net-searchbuilder')(root, $);
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
    $.extend(true, dataTable.SearchBuilder.classes, {
        clearAll: 'button dtsb-clearAll'
    });
    $.extend(true, dataTable.Group.classes, {
        add: 'button dtsb-add',
        clearGroup: 'button dtsb-clearGroup is-light',
        logic: 'button dtsb-logic is-light'
    });
    $.extend(true, dataTable.Criteria.classes, {
        container: 'dtsb-criteria',
        "delete": 'button dtsb-delete',
        left: 'button dtsb-left',
        right: 'button dtsb-right'
    });
    return dataTable.searchPanes;
}));
