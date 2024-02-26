<?php
require_once '../../../videos/configuration.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin Bookmark"));
    exit;
}
header('Content-Type: application/json');
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';

$rows = BookmarkTable::getAll();
$rowsTotal = BookmarkTable::getTotal();
?>
{
"draw": <?php echo $_GET['draw']; ?>,
"recordsTotal": <?php echo $rowsTotal; ?>,
"recordsFiltered": <?php echo $rowsTotal; ?>,
"data": <?php echo json_encode($rows); ?>
}