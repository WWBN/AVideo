/*!
 Foundation ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-zf","datatables.net-searchbuilder"],function(a){return b(a)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-zf")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c)}:b(jQuery)})(function(b){var a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"button alert dtsb-clearAll"});b.extend(!0,a.Group.classes,
{add:"button dtsb-add",clearGroup:"button dtsb-clearGroup",logic:"button dtsb-logic"});b.extend(!0,a.Criteria.classes,{condition:"form-control dtsb-condition",data:"form-control dtsb-data","delete":"button alert dtsb-delete",left:"button dtsb-left",right:"button dtsb-right",value:"form-control dtsb-value"});return a.searchPanes});
