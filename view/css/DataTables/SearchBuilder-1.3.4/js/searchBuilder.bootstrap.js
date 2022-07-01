/*! Bootstrap ui integration for DataTables' SearchBuilder
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-bs', 'datatables.net-searchbuilder'], function ($) {
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
                $ = require('datatables.net-bs')(root, $).$;
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
        clearAll: 'btn btn-default dtsb-clearAll'
    });
    $.extend(true, dataTable.Group.classes, {
        add: 'btn btn-default dtsb-add',
        clearGroup: 'btn btn-default dtsb-clearGroup',
        logic: 'btn btn-default dtsb-logic'
    });
    $.extend(true, dataTable.Criteria.classes, {
        condition: 'form-control dtsb-condition',
        data: 'form-control dtsb-data',
        "delete": 'btn btn-default dtsb-delete',
        left: 'btn btn-default dtsb-left',
        right: 'btn btn-default dtsb-right',
        value: 'form-control dtsb-value'
    });
    return dataTable.searchPanes;
}));
