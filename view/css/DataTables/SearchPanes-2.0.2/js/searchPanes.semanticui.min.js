/*!
 semantic ui integration for DataTables' SearchPanes
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-se","datatables.net-searchpanes"],function(a){return b(a)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-se")(a,c).$);c.fn.dataTable.SearchPanes||require("datatables.net-searchpanes")(a,c);return b(c)}:b(jQuery)})(function(b){var a=b.fn.dataTable;b.extend(!0,a.SearchPane.classes,{buttonGroup:"right floated ui buttons column",disabledButton:"disabled",
narrowSearch:"dtsp-narrowSearch",narrowSub:"dtsp-narrow",paneButton:"basic ui",paneInputButton:"circular search link icon",topRow:"row dtsp-topRow"});b.extend(!0,a.SearchPanes.classes,{clearAll:"dtsp-clearAll basic ui button",collapseAll:"dtsp-collapseAll basic ui button",disabledButton:"disabled",showAll:"dtsp-showAll basic ui button"});a.SearchPane.prototype._searchContSetup=function(){b('<i class="'+this.classes.paneInputButton+'"></i>').appendTo(this.dom.searchCont)};return a.searchPanes});
