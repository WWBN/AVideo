<?php
header('Content-Type: image/png');
$logo1 = '../videos/userPhoto/logo.png';
$logo2 = '../view/img/logo.png';
//var_dump(file_exists($logo1), file_exists($logo2));exit;
if(file_exists($logo1)){
    readfile($logo1);
    exit;
}   
readfile($logo2);