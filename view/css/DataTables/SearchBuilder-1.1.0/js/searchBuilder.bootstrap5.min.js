/*!
 Bootstrap 5 ui integration for DataTables' SearchBuilder
 ©2016 SpryMedia Ltd - datatables.net/license
*/
(function(b){"function"===typeof define&&define.amd?define(["jquery","datatables.net-bs5","datatables.net-searchbuilder"],function(a){return b(a,window,document)}):"object"===typeof exports?module.exports=function(a,c){a||(a=window);c&&c.fn.dataTable||(c=require("datatables.net-bs5")(a,c).$);c.fn.dataTable.searchBuilder||require("datatables.net-searchbuilder")(a,c);return b(c,a,a.document)}:b(jQuery,window,document)})(function(b,a,c){a=b.fn.dataTable;b.extend(!0,a.SearchBuilder.classes,{clearAll:"btn btn-light dtsb-clearAll"});
b.extend(!0,a.Group.classes,{add:"btn btn-light dtsb-add",clearGroup:"btn btn-light dtsb-clearGroup",logic:"btn btn-light dtsb-logic"});b.extend(!0,a.Criteria.classes,{condition:"form-select dtsb-condition",data:"dtsb-data form-select","delete":"btn btn-light dtsb-delete",input:"form-control dtsb-input",left:"btn btn-light dtsb-left",right:"btn btn-light dtsb-right",select:"form-select",value:"dtsb-value"});return a.searchPanes});
