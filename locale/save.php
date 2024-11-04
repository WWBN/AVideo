<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'objects/user.php';
$obj = new stdClass();
$obj->status = "1";
$obj->error = "";
if (!User::isAdmin() || !empty($global['disableAdvancedConfigurations'])) {
    $obj->status = 0;
    $obj->error = __("Permission denied");
    die(json_encode($obj));
}
if(empty($_REQUEST['custom'])){
    $dir = "{$global['systemRootPath']}locale/";
}else{
    $dir = "{$global['systemRootPath']}videos/locale/";
    make_path($dir);
}
if (!is_writable($dir) && !isWindowsServer()) {
    $obj->status = 0;
    $obj->error = sprintf(__("Your %s locale dir is not writable"), $global['systemRootPath']);
    die(json_encode($obj));
}

if (empty($_POST['flag'])) {
    forbiddenPage('Flag is empty');
}
$file = $dir.($_POST['flag']).".php";
$myfile = fopen($file, "w") or die("Unable to open file!");
if (!$myfile) {
    $obj->status = 0;
    $obj->error = __("Unable to open file!");
    die(json_encode($obj));
}

$txt = "<?php\nglobal \$t;\n";
fwrite($myfile, $txt);
fwrite($myfile, $_POST['code']);
fclose($myfile);
echo json_encode($obj);
