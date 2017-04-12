<?php

header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'objects/user.php';
$obj = new stdClass();
$obj->status = "1";
$obj->error = "";
if (!User::isAdmin()) {
    $obj->status = 0;
    $obj->error = __("Permission denied");
    die(json_encode($obj));
}

$dir = "{$global['systemRootPath']}locale/";
if (!is_writable($dir)) {
    $obj->status = 0;
    $obj->error = sprintf(__("Your %slocale dir is not writable"), $global['systemRootPath']);
    die(json_encode($obj));
} 
$file = $dir.$_POST['flag'].".php";
$myfile = fopen($file, "w") or die("Unable to open file!");
if(!$myfile){
    $obj->status = 0;
    $obj->error = __("Unable to open file!");
    die(json_encode($obj));
}

$txt = "<?php\nglobal \$t;\n";
fwrite($myfile, $txt);
fwrite($myfile, $_POST['code']);
fclose($myfile);
echo json_encode($obj);
