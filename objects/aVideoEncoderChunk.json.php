<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->file = tempnam(sys_get_temp_dir(), 'YTPChunk_');

$putdata = fopen("php://input", "r");
$fp = fopen($obj->file, "w");

error_log("aVideoEncoderChunk.json.php: start {$obj->file} ");

while ($data = fread($putdata, 1024 * 1024))
    fwrite($fp, $data);

fclose($fp);
fclose($putdata);
sleep(1);
$obj->filesize = filesize($obj->file);

$json = json_encode($obj);

error_log("aVideoEncoderChunk.json.php: {$json} ");

die($json);
