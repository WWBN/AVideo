<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->file = tempnam(sys_get_temp_dir(), 'prefix');

$putdata = fopen("php://input", "r");
$fp = fopen($obj->file, "w");

while ($data = fread($putdata, 1024 * 1024))
    fwrite($fp, $data);

fclose($fp);
fclose($putdata);
die(json_encode($obj));
