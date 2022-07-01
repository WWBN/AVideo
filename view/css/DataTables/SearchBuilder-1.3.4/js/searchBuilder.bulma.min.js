/*!
 Bulma ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-bs5","datatables.net-searchbuilder"],function(a){return b(a)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-bs5")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c)}:b(jQuery)})(function(b){var a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"button dtsb-clearAll"});b.extend(!0,a.Group.classes,
{add:"button dtsb-add",clearGroup:"button dtsb-clearGroup is-light",logic:"button dtsb-logic is-light"});b.extend(!0,a.Criteria.classes,{container:"dtsb-criteria","delete":"button dtsb-delete",left:"button dtsb-left",right:"button dtsb-right"});return a.searchPanes});
