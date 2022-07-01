/*! Bootstrap 5 ui integration for DataTables' SearchBuilder
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-bs5', 'datatables.net-searchbuilder'], function ($) {
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
                $ = require('datatables.net-bs5')(root, $).$;
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
        clearAll: 'btn btn-light dtsb-clearAll'
    });
    $.extend(true, dataTable.Group.classes, {
        add: 'btn btn-light dtsb-add',
        clearGroup: 'btn btn-light dtsb-clearGroup',
        logic: 'btn btn-light dtsb-logic'
    });
    $.extend(true, dataTable.Criteria.classes, {
        condition: 'form-select dtsb-condition',
        data: 'dtsb-data form-select',
        "delete": 'btn btn-light dtsb-delete',
        input: 'form-control dtsb-input',
        left: 'btn btn-light dtsb-left',
        right: 'btn btn-light dtsb-right',
        select: 'form-select',
        value: 'dtsb-value'
    });
    return dataTable.searchPanes;
}));
