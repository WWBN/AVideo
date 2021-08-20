/*!
 Foundation ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-zf","datatables.net-searchbuilder"],function(a){return b(a,window,document)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-zf")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c,a,a.document)}:b(jQuery,window,document)})(function(b,a,c){a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"button secondary dtsb-clearAll"});
b.extend(!0,a.Group.classes,{add:"button secondary dtsb-add",clearGroup:"button secondary dtsb-clearGroup",logic:"button secondary dtsb-logic"});b.extend(!0,a.Criteria.classes,{condition:"form-control dtsb-condition",data:"form-control dtsb-data","delete":"button secondary dtsb-delete",left:"button secondary dtsb-left",right:"button secondary dtsb-right",value:"form-control dtsb-value"});return a.searchPanes});
