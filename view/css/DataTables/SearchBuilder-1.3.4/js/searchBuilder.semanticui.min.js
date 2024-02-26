/*!
 semantic ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-se","datatables.net-searchbuilder"],function(a){return b(a)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-se")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c)}:b(jQuery)})(function(b){var a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"basic ui button dtsb-clearAll"});b.extend(!0,a.Group.classes,
{add:"basic ui button dtsb-add",clearGroup:"basic ui button dtsb-clearGroup",logic:"basic ui button dtsb-logic"});b.extend(!0,a.Criteria.classes,{condition:"ui selection dropdown dtsb-condition",data:"ui selection dropdown dtsb-data","delete":"basic ui button dtsb-delete",left:"basic ui button dtsb-left",right:"basic ui button dtsb-right",value:"basic ui selection dropdown dtsb-value"});a.ext.buttons.searchBuilder.action=function(c,e,f,d){c.stopPropagation();this.popover(d._searchBuilder.getNode(),
{align:"container",span:"container"});void 0!==d._searchBuilder.s.topGroup&&d._searchBuilder.s.topGroup.dom.container.trigger("dtsb-redrawContents");b("div.dtsb-searchBuilder").removeClass("ui basic vertical buttons")};return a.searchPanes});
