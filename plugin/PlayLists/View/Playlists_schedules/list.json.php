<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
header('Content-Type: application/json');

$rows = Playlists_schedules::getAll();
foreach ($rows as $key => $value) {
    $rows[$key]['loopText'] = empty($rows[$key]['loop'])?__('No'):__('Yes');
    $rows[$key]['statusText'] = empty($rows[$key]['status'])?__('Inactive'):Playlists_schedules::STATUS_TEXT[$rows[$key]['status']];
    $rows[$key]['repeatText'] = __(Playlists_schedules::$REPEAT_TEXT[$rows[$key]['repeat']]);
    $rows[$key]['start_datetime'] = date('Y-m-d H:i', $rows[$key]['start_datetime']);
    $rows[$key]['finish_datetime'] = date('Y-m-d H:i', $rows[$key]['finish_datetime']);
}
?>
{"data": <?php echo json_encode($rows); ?>}