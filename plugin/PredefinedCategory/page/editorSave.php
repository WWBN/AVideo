<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin logo overlay"));
    exit;
}
header('Content-Type: application/json');
require_once $global['systemRootPath'] . 'plugin/PredefinedCategory/PredefinedCategory.php';

$plugin = new PredefinedCategory();

$obj = new stdClass();

$o = $plugin->getDataObject();
//var_dump($o);exit;
if(!empty($_POST['groupRadioValue'])){
    $o->{"AddVideoOnGroup_[{$_POST['groupRadioValue']}]_"}=$_POST['groupRadio']==='true'?true:false;
}else if(empty($_POST['user_id'])){
    $o->defaultCategory = $_POST['category_id'];
}else{
    $v = $_POST['user_id'];
    if(empty($_POST['category_id'])){
        unset($o->userCategory->$v);
    }else{
        $o->userCategory->$v = $_POST['category_id'];
    }
}

$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(json_encode($o));
$obj->saved = $p->save();

echo json_encode($o);
?>