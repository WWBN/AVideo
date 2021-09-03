/*!
 Bootstrap integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(c){"function"===typeof define&&define.amd?define(["jquery","datatables.net-ju","datatables.net-searchpanes"],function(a){return c(a,window,document)}):"object"===typeof exports?module.exports=function(a,b){a||(a=window);b&&b.fn.dataTable||(b=require("datatables.net-ju")(a,b).$);b.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(a,b);return c(b,a,a.document)}:c(jQuery,window,document)})(function(c,a,b){a=c.fn.dataTable;c.extend(!0,a.SearchPane.classes,{disabledButton:"dtsp-paneInputButton dtsp-disabledButton",
paneButton:"dtsp-paneButton ui-button",topRow:"dtsp-topRow ui-state-default"});c.extend(!0,a.SearchPanes.classes,{clearAll:"dtsp-clearAll ui-button",container:"dtsp-searchPanes",panes:"dtsp-panesContainer fg-toolbar ui-toolbar ui-widget-header"});return a.searchPanes});
