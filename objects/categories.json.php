<?php
error_reporting(0);
global $global, $config;

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/category.php';

allowOrigin();
header('Content-Type: application/json');

$_REQUEST['rowCount'] = getRowCount(1000);
$_REQUEST['current'] = getCurrentPage();

$onlyWithVideos = false;
if(!empty($_GET['user'])){
    $onlyWithVideos = true;
}
// Always apply user-group filtering using the logged-in user's real ID.
// Guests get -1 so getUserGroups returns [], leaving only unrestricted categories visible.
$currentUserId = User::getId();
$sameUserGroupAsMe = !empty($currentUserId) ? intval($currentUserId) : -1;

$categories = Category::getAllCategories(true, $onlyWithVideos, false, $sameUserGroupAsMe);
$total = Category::getTotalCategories(true, $onlyWithVideos, false, $sameUserGroupAsMe);
//$breaks = array('<br />', '<br>', '<br/>');
foreach ($categories as $key => $value) {
    $categories[$key]['iconHtml'] = "<span class='$value[iconClass]'></span>";
    $categories[$key]['users_groups_ids_array'] = Categories_has_users_groups::getUserGroupsIdsFromCategory($value['id']);

    if(empty($categories[$key]['users_groups_ids_array'])){
        $categories[$key]['total_users_groups'] = 0;
    }else{
        $categories[$key]['total_users_groups'] = count($categories[$key]['users_groups_ids_array']);
    }
    //$categories[$key]['description'] = str_ireplace($breaks, "\r\n", $value['description']);
    /*
    $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?";
    $res = sqlDAL::readSql($sql,"i",array($value['id']));
    $catTypeCache = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if($catTypeCache!=false){
        if($catTypeCache['manualSet']=="0"){
            $categories[$key]['type'] = "3";
        } else {
            $categories[$key]['type'] = $catTypeCache['type'];
        }
    } else {
        $categories[$key]['type'] = "3";
    }
     *
     */
}
if (empty($_POST['sort']) && empty($_GET['sort'])) {
    $array_column = array_column($categories, 'hierarchyAndName');
    array_multisort($array_column, SORT_ASC, $categories);
}

$json = [
    'current'=>getCurrentPage(),
    'rowCount'=>getRowCount(),
    'total'=>$total,
    'rows'=>$categories,
    'onlyWithVideos'=>$onlyWithVideos,
    'sameUserGroupAsMe'=>$sameUserGroupAsMe
];

echo _json_encode($json);
