<?php
$uri = getSelfURI();
if (!empty($_REQUEST['Chat']) || preg_match('/plugin.Chat2/', $uri)) {
  return;
}
_session_start();
if (!function_exists('getColorValue')) {
  function getColorValue($requestKey)
  {
    if (!empty($_REQUEST[$requestKey])) {
      $_SESSION[$requestKey] = $_REQUEST[$requestKey];
      return substr($_REQUEST[$requestKey], 10, 6);
    } else if (!empty($_SESSION[$requestKey])) {
      return substr($_SESSION[$requestKey], 10, 6);
    }
    return null;
  }
}

if (isAVideoMobileApp()) {
  $_GET['noNavbar'] = 1;
  $_GET['iframe'] = 1;
  $_GET['noNavbarClose'] = 1;
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
  <?php if (isAVideoMobileApp()) : ?>
  #CloseButtonInVideo,
  .offline-button,
  .loop-button,
  .autoplay-button {
    display: none !important;
  }
  <?php endif; ?>

  /* Overwrite Bootstrap styles <?php echo $uri; ?> */
  <?php if ($bgColor) : ?>body,
  .table>thead>tr>td,
  .table>thead>tr>th,
  .btn-default,
  .alert,
  .row,
  .bootgrid-table td.loading,
  .bootgrid-table td.no-results,
  .modal-content,
  .list-group-item,
  .dropdown-menu,
  .well,
  .panel-default {
    background-color: #<?php echo $bgColor; ?> !important;
  }

  <?php endif; ?><?php if ($bgColor1) : ?>.panel,
  .panel-default>.panel-heading,
  .table,
  .btn-primary,
  .alert-info,
  .table-striped>tbody>tr:hover {
    background-color: #<?php echo $bgColor1; ?> !important;
  }

  <?php endif; ?><?php if ($bgColor2) : ?>.panel-footer,
  .table>tfoot>tr>th,
  .btn-success,
  .alert-success,
  .table-striped>tbody>tr:nth-of-type(odd) {
    background-color: #<?php echo $bgColor2; ?> !important;
  }

  <?php endif; ?><?php if ($bgColorAppBar) : ?>.panel-danger,
  .btn-danger,
  .alert-danger,
  .nav-tabs>li.active>a,
  .nav-tabs>li.active>a:focus,
  .nav-tabs>li.active>a:hover {
    background-color: #<?php echo $bgColorAppBar; ?> !important;
  }

  <?php endif; ?><?php if ($bgColorAppBarDarker) : ?>.panel-warning,
  .btn-warning,
  .alert-warning,
  .img-thumbnail,
  .navbar-default,
  .dropdown-menu>li>a:focus,
  .dropdown-menu>li>a:hover,
  .nav-tabs>li>a:hover,
  .panel-heading,
  .swal-modal,
  .popover-title {
    background-color: #<?php echo $bgColorAppBarDarker; ?> !important;
  }

  <?php endif; ?><?php if ($textColor) : ?>.panel-default>.panel-heading,
  .table>thead>tr>th,
  .btn-default,
  .alert,
  .list-group-item,
  .dropdown-menu,
  .dropdown-menu>li>a {
    color: #<?php echo $textColor; ?> !important;
  }

  <?php endif; ?><?php if ($textColorDark) : ?>.panel,
  .table,
  .btn-primary,
  .alert-info,
  .nav-tabs>li.active>a,
  .nav-tabs>li.active>a:focus,
  .nav-tabs>li.active>a:hover,
  .nav-tabs>li>a:hover,
  .swal-title,
  .swal-text,
  .swal-footer {
    color: #<?php echo $textColorDark; ?> !important;
  }

  <?php endif; ?><?php if ($textColorHigh) : ?>.panel-footer,
  .table>tfoot>tr>th,
  .btn-success,
  .alert-success,
  .dropdown-menu>li>a:focus,
  .dropdown-menu>li>a:hover {
    color: #<?php echo $textColorHigh; ?> !important;
  }

  <?php endif; ?>
</style>