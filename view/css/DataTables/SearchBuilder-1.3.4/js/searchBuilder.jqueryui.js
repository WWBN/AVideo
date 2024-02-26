/*! JQuery ui ui integration for DataTables' SearchBuilder
 * ©2016 SpryMedia Ltd - datatables.net/license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-ju', 'datatables.net-searchbuilder'], function ($) {
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
                $ = require('datatables.net-ju')(root, $).$;
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
        clearAll: 'ui-button ui-corner-all ui-widget dtsb-clearAll'
    });
    $.extend(true, dataTable.Group.classes, {
        add: 'ui-button ui-corner-all ui-widget dtsb-add',
        clearGroup: 'ui-button ui-corner-all ui-widget dtsb-clearGroup',
        logic: 'ui-button ui-corner-all ui-widget dtsb-logic'
    });
    $.extend(true, dataTable.Criteria.classes, {
        condition: 'ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all dtsb-condition',
        data: 'ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all dtsb-data',
        "delete": 'ui-button ui-corner-all ui-widget dtsb-delete',
        left: 'ui-button ui-corner-all ui-widget dtsb-left',
        right: 'ui-button ui-corner-all ui-widget dtsb-right',
        value: 'ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all dtsb-value'
    });
    return dataTable.searchPanes;
}));
