/*!
 semantic ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-se","datatables.net-searchbuilder"],function(a){return b(a,window,document)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-se")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c,a,a.document)}:b(jQuery,window,document)})(function(b,a,c){a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"ui button dtsb-clearAll"});
b.extend(!0,a.Group.classes,{add:"ui button dtsb-add",clearGroup:"ui button dtsb-clearGroup",logic:"ui button dtsb-logic"});b.extend(!0,a.Criteria.classes,{condition:"ui selection dropdown dtsb-condition",data:"ui selection dropdown dtsb-data","delete":"ui button dtsb-delete",left:"ui button dtsb-left",right:"ui button dtsb-right",value:"ui selection dropdown dtsb-value"});return a.searchPanes});
