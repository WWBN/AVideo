/*!
 JQuery ui ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-ju","datatables.net-searchbuilder"],function(a){return b(a,window,document)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-ju")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c,a,a.document)}:b(jQuery,window,document)})(function(b,a,c){a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"ui-button ui-corner-all ui-widget dtsb-clearAll"});
b.extend(!0,a.Group.classes,{add:"ui-button ui-corner-all ui-widget dtsb-add",clearGroup:"ui-button ui-corner-all ui-widget dtsb-clearGroup",logic:"ui-button ui-corner-all ui-widget dtsb-logic"});b.extend(!0,a.Criteria.classes,{condition:"ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all dtsb-condition",data:"ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all dtsb-data","delete":"ui-button ui-corner-all ui-widget dtsb-delete",left:"ui-button ui-corner-all ui-widget dtsb-left",
right:"ui-button ui-corner-all ui-widget dtsb-right",value:"ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all dtsb-value"});return a.searchPanes});
