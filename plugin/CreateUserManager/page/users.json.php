<?php
require_once '../../../videos/configuration.php';
if (!CreateUserManager::isManager()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager"));
    exit;
}
header('Content-Type: application/json');
require_once $global['systemRootPath'] . 'plugin/CreateUserManager/Objects/UserManager.php';

$rows = UserManager::getAllUsers();
$rowsTotal = UserManager::getTotalUsers();
?>
{
"draw": <?php echo $_GET['draw']; ?>,
"recordsTotal": <?php echo $rowsTotal; ?>,
"recordsFiltered": <?php echo $rowsTotal; ?>,
"data": <?php echo json_encode($rows); ?>
}