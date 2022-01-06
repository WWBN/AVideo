<?php
header('Content-Type: application/json');
$cmd = "git log -1";
exec($cmd . "  2>&1", $output, $return_val);

$obj = new stdClass();

$obj->output = $output;

foreach ($output as $value) {
    preg_match("/Date:(.*)/i", $value, $match);
    if (!empty($match[1])) {
        $obj->date = strtotime($match[1]);
        $obj->dateString = trim($match[1]);
        $obj->dateMySQL = date("Y-m-d H:i:s", $obj->date);
    }
}

echo json_encode($obj);
