<?php
require_once '../../../videos/configuration.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin Audit"));
    exit;
}
header('Content-Type: application/json');
require_once $global['systemRootPath'] . 'plugin/Audit/Objects/AuditTable.php';

$rows = AuditTable::getAll();
$rowsTotal = AuditTable::getTotal();
?>
{
"draw": <?php echo $_GET['draw']; ?>,
"recordsTotal": <?php echo $rowsTotal; ?>,
"recordsFiltered": <?php echo $rowsTotal; ?>,
"data": <?php echo json_encode($rows); ?>
}