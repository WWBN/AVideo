<?php
require_once 'functions.php';
header('Content-Type: application/json');
$obj = new stdClass();
$obj->max_file_size = get_max_file_size();
$obj->file_upload_max_size = file_upload_max_size();

echo json_encode($obj);