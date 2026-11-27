<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_affiliations.php';
header('Content-Type: application/json');


if (!User::isLogged()) {
    forbiddenPage();
}

if (User::isAdmin()) {
    $rows = Users_affiliations::getAll();
    $total = Users_affiliations::getTotal();
} else {
    $rows = Users_affiliations::getAllForUser(User::getId());
    $total = count($rows);
}

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}