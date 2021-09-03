/*!
 Bulma integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(c){"function"===typeof define&&define.amd?define(["jquery","datatables.net-bulma","datatables.net-searchpanes"],function(a){return c(a,window,document)}):"object"===typeof exports?module.exports=function(a,b){a||(a=window);b&&b.fn.dataTable||(b=require("datatables.net-bulma")(a,b).$);b.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(a,b);return c(b,a,a.document)}:c(jQuery,window,document)})(function(c,a,b){a=c.fn.dataTable;c.extend(!0,a.SearchPane.classes,{disabledButton:"is-disabled",
paneButton:"button dtsp-paneButton is-white",search:"input search"});c.extend(!0,a.SearchPanes.classes,{clearAll:"dtsp-clearAll button",disabledButton:"is-disabled"});return a.searchPanes});
