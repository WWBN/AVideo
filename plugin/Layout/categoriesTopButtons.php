<?php
if (empty($obj) || !isset($obj->categoriesTopButtonsFluid)) {
    $obj = AVideoPlugin::loadPlugin('Layout');
}
global $advancedCustom;
$_rowCount = getRowCount();
$current = getCurrentPage();
$_REQUEST['rowCount'] = 1000;
$_REQUEST['current'] = 1;
$parsed_cats = array();
$categories = Category::getAllCategories();
$_REQUEST['rowCount'] = $_rowCount;
$_REQUEST['current'] = $current;
$items = array();
foreach ($categories as $value) {
    if ($value['parentId']) {
        continue;
    }
    if ($advancedCustom->ShowAllVideosOnCategory) {
        $total = $value['fullTotal'];
    } else {
        $total = $value['total'];
    }
    if (empty($total)) {
        continue;
    }
    if (in_array($value['id'], $parsed_cats)) {
        continue;
    }
    $label = '<i class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></i>  '
        . '<span class="">' . __($value['name']) . '</span>';
    if (!empty($obj->categoriesTopButtonsShowVideosCount)) {
        $label .= ' <span class="badge">' . $total . '</span>';
    }
    $items[] = array(
        'href' => Category::getCategoryLinkFromName($value['clean_name']), 
        'tooltip' => __($value['name']), 
        'onclick' => '', 
        'label' => $label,
        'isActive' => $value['clean_name'] == @$_REQUEST['catName'],
        'clean_name' => $value['clean_name']
    );
}
//var_dump($_REQUEST['catName'], $items);exit;
generateHorizontalFlickity($items);
