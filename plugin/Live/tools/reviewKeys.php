<?php
require_once __DIR__ . '/../../../videos/configuration.php';
$global['printLogs'] = 1;

$sql = "select * from live_transmitions_history ORDER BY id ASC LIMIT 99999 ";
$formats = '';
$values = array();

$res = sqlDAL::readSql($sql, $formats, $values);

$fullData = sqlDAL::fetchAllAssoc($res);

$results = array();
$resultsC = array();
foreach ($fullData as $key => $value) {
    $parts = explode('-', $value['key']);
    $_key = $parts[0];
    if(!isset($results[$_key])){
        $results[$_key] = array();
    }
    if(!in_array($value['users_id'], $results[$_key])){
        $results[$_key][] = $value['users_id'];
        $resultsC[$_key][] = $value;
    }

}

foreach ($resultsC as $key => $value) {
    if(count($value)>1){
        echo PHP_EOL;
        echo 'key='.$key.PHP_EOL;
        echo 'total='.count($value).PHP_EOL;
        foreach ($value as $row) {
            $parts = explode('-', $row['key']);
            $_key = $parts[0];

            $sql = "SELECT * FROM live_transmitions WHERE  users_id = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "i", [$row['users_id']]);
            $data = sqlDAL::fetchAssoc($res);
            if($_key != $data['key']){
                //$sql = "UPDATE live_transmitions_history SET `key` = '{$data['key']}' WHERE users_id = {$row['users_id']} and `key` LIKE '{$row['key']}%' ";
                //$insert_row = sqlDAL::writeSql($sql);

                echo '------------'.PHP_EOL;
                echo 'id='.$row['id'].PHP_EOL;
                echo 'user='.User::getNameIdentificationById($row['users_id']) .PHP_EOL;
                echo 'key='.$row['key'].PHP_EOL;
                echo 'live_transmitions key='.$data['key'].PHP_EOL;
                echo '------------'.PHP_EOL;
            }
        }
    }
}
