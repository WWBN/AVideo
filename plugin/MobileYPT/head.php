<?php
function getColorValue($requestKey) {
  if (!empty($_REQUEST[$requestKey])) {
    return substr($_REQUEST[$requestKey], 10, 6);
  }
  return null;
}

$bgColor = getColorValue('bgColor');
$bgColor1 = getColorValue('bgColor1');
$bgColor2 = getColorValue('bgColor2');
$bgColorAppBar = getColorValue('bgColorAppBar');
$bgColorAppBarDarker = getColorValue('bgColorAppBarDarker');

$textColor = getColorValue('textColor');
$textColorDark = getColorValue('textColorDark');
$textColorHigh = getColorValue('textColorHigh');
?>
<style>
/* Overwrite Bootstrap styles */
<?php if ($bgColor): ?>
body, .panel-default>.panel-heading,
.table>thead>tr>td,
.table>thead>tr>th,
.btn-default,
.alert, .row,
.bootgrid-table td.loading, 
.bootgrid-table td.no-results, 
.modal-content, .list-group-item, .dropdown-menu {
  background-color: #<?php echo $bgColor; ?> !important;
}
<?php endif; ?>

<?php if ($bgColor1): ?>
.panel,
.table,
.btn-primary,
.alert-info,
.table-striped>tbody>tr:hover {
  background-color: #<?php echo $bgColor1; ?> !important;
}
<?php endif; ?>

<?php if ($bgColor2): ?>
.panel-footer,
.table>tfoot>tr>th,
.btn-success,
.alert-success,
.table-striped>tbody>tr:nth-of-type(odd) {
  background-color: #<?php echo $bgColor2; ?> !important;
}
<?php endif; ?>

<?php if ($bgColorAppBar): ?>
.panel-danger,
.btn-danger,
.alert-danger {
  background-color: #<?php echo $bgColorAppBar; ?> !important;
}
<?php endif; ?>

<?php if ($bgColorAppBarDarker): ?>
.panel-warning,
.btn-warning,
.alert-warning,.img-thumbnail, .navbar-default, .dropdown-menu>li>a:focus, .dropdown-menu>li>a:hover {
  background-color: #<?php echo $bgColorAppBarDarker; ?> !important;
}
<?php endif; ?>

<?php if ($textColor): ?>
.panel-default>.panel-heading,
.table>thead>tr>th,
.btn-default,
.alert, .list-group-item, .dropdown-menu, .dropdown-menu>li>a  {
  color: #<?php echo $textColor; ?> !important;
}
<?php endif; ?>

<?php if ($textColorDark): ?>
.panel,
.table,
.btn-primary,
.alert-info {
  color: #<?php echo $textColorDark; ?> !important;
}
<?php endif; ?>

<?php if ($textColorHigh): ?>
.panel-footer,
.table>tfoot>tr>th,
.btn-success,
.alert-success, .dropdown-menu>li>a:focus, .dropdown-menu>li>a:hover {
  color: #<?php echo $textColorHigh; ?> !important;
}
<?php endif; ?>
</style>