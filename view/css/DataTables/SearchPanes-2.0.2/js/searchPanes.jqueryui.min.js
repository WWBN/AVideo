/*!
 Bootstrap integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-ju","datatables.net-searchpanes"],function(a){return b(a)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-ju")(a,c).$);c.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(a,c);return b(c)}:b(jQuery)})(function(b){var a=b.fn.dataTable;b.extend(!0,a.SearchPane.classes,{disabledButton:"dtsp-paneInputButton dtsp-disabledButton",paneButton:"dtsp-paneButton ui-button",
topRow:"dtsp-topRow ui-state-default"});b.extend(!0,a.SearchPanes.classes,{clearAll:"dtsp-clearAll ui-button",collapseAll:"dtsp-collapseAll ui-button",container:"dtsp-searchPanes",panes:"dtsp-panesContainer fg-toolbar ui-toolbar ui-widget-header",showAll:"dtsp-showAll ui-button"});return a.searchPanes});
