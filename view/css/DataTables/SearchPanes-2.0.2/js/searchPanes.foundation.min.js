/*!
 Bootstrap integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-zf","datatables.net-searchpanes"],function(a){return b(a)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-zf")(a,c).$);c.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(a,c);return b(c)}:b(jQuery)})(function(b){var a=b.fn.dataTable;b.extend(!0,a.SearchPane.classes,{buttonGroup:"secondary button-group",disabledButton:"disabled",narrow:"dtsp-narrow",
narrowButton:"dtsp-narrowButton",narrowSearch:"dtsp-narrowSearch",paneButton:"secondary button",pill:"badge secondary",search:"search",searchLabelCont:"searchCont",show:"col",table:"unstriped"});b.extend(!0,a.SearchPanes.classes,{clearAll:"dtsp-clearAll button secondary",collapseAll:"dtsp-collapseAll button secondary",disabledButton:"disabled",panes:"panes dtsp-panesContainer",showAll:"dtsp-showAll button secondary",title:"dtsp-title"});return a.searchPanes});
