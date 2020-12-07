<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule.php';
if(!User::isAdmin()){
    $total = Meet_schedule::getTotalFromUsersId(User::getId(), @$_REQUEST['meet_scheduled'], empty($_GET['manageMeetings']));
    $rows = Meet_schedule::getAllFromUsersId(User::getId(), @$_REQUEST['meet_scheduled'], empty($_GET['manageMeetings']));
}else{
    $total = Meet_schedule::getTotal(@$_REQUEST['meet_scheduled']);
    $rows = Meet_schedule::getAll(@$_REQUEST['meet_scheduled']);
}

if(empty($_GET['draw'])){
    $_GET['draw'] = 0;
}
header('Content-Type: application/json');
?>
{
"draw": <?php echo $_GET['draw']; ?>,
  "recordsTotal": <?php echo $total; ?>,
  "recordsFiltered": <?php echo $total; ?>,"data": <?php echo json_encode($rows); ?>}