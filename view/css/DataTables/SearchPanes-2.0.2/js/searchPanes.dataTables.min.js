/*!
 Bootstrap integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(c){"function"===typeof define&&define.amd?define(["jquery","datatables.net-dt","datatables.net-searchpanes"],function(b){return c(b)}):"object"===typeof exports?module.exports=function(b,a){b||(b=window);a&&a.fn.dataTable||(a=require("datatables.net-dt")(b,a).$);a.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(b,a);return c(a)}:c(jQuery)})(function(c){return c.fn.dataTable.searchPanes});
