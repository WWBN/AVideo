/*!
 Bootstrap integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(c){"function"===typeof define&&define.amd?define(["jquery","datatables.net-dt","datatables.net-searchpanes"],function(a){return c(a,window,document)}):"object"===typeof exports?module.exports=function(a,b){a||(a=window);b&&b.fn.dataTable||(b=require("datatables.net-dt")(a,b).$);b.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(a,b);return c(b,a,a.document)}:c(jQuery,window,document)})(function(c,a,b){return c.fn.dataTable.searchPanes});
