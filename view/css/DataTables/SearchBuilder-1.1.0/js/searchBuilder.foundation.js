/*! SearchBuilder 1.1.0
 * ©SpryMedia Ltd - datatables.net/license/mit
 */
(function () {
    'use strict';

    /*! Foundation ui integration for DataTables' SearchBuilder
     * ©2016 SpryMedia Ltd - datatables.net/license
     */
    (function (factory) {
        if (typeof define === 'function' && define.amd) {
            // AMD
            define(['jquery', 'datatables.net-zf', 'datatables.net-searchbuilder'], function ($) {
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
                    $ = require('datatables.net-zf')(root, $).$;
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
        var dataTable = $.fn.dataTable;
        $.extend(true, dataTable.SearchBuilder.classes, {
            clearAll: 'button secondary dtsb-clearAll'
        });
        $.extend(true, dataTable.Group.classes, {
            add: 'button secondary dtsb-add',
            clearGroup: 'button secondary dtsb-clearGroup',
            logic: 'button secondary dtsb-logic'
        });
        $.extend(true, dataTable.Criteria.classes, {
            condition: 'form-control dtsb-condition',
            data: 'form-control dtsb-data',
            "delete": 'button secondary dtsb-delete',
            left: 'button secondary dtsb-left',
            right: 'button secondary dtsb-right',
            value: 'form-control dtsb-value'
        });
        return dataTable.searchPanes;
    }));

}());
