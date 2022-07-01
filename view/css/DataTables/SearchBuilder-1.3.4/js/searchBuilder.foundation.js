/*! Foundation ui integration for DataTables' SearchBuilder
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-zf', 'datatables.net-searchbuilder'], function ($) {
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
            if (!$.fn.dataTable.searchBuilder) {
                // eslint-disable-next-line @typescript-eslint/no-var-requires
                require('datatables.net-searchbuilder')(root, $);
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
    $.extend(true, dataTable.SearchBuilder.classes, {
        clearAll: 'button alert dtsb-clearAll'
    });
    $.extend(true, dataTable.Group.classes, {
        add: 'button dtsb-add',
        clearGroup: 'button dtsb-clearGroup',
        logic: 'button dtsb-logic'
    });
    $.extend(true, dataTable.Criteria.classes, {
        condition: 'form-control dtsb-condition',
        data: 'form-control dtsb-data',
        "delete": 'button alert dtsb-delete',
        left: 'button dtsb-left',
        right: 'button dtsb-right',
        value: 'form-control dtsb-value'
    });
    return dataTable.searchPanes;
}));
