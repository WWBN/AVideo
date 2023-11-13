<?php
if (empty($obj) || !isset($obj->categoriesTopButtonsFluid)) {
    $obj = AVideoPlugin::loadPlugin('Layout');
}
$timeLogName = TimeLogStart("CategoryTopButtons");
global $advancedCustom;
$_rowCount = getRowCount();
$current = getCurrentPage();
$_REQUEST['rowCount'] = 1000;
unsetCurrentPage();
$parsed_cats = array();
TimeLogEnd($timeLogName, __LINE__);
$categories = Category::getAllCategories();
TimeLogEnd($timeLogName, __LINE__);
$_REQUEST['rowCount'] = $_rowCount;
$_REQUEST['current'] = $current;
$items = array();
foreach ($categories as $value) {
    if ($value['parentId']) {
        echo "<!-- generateHorizontalFlickity continue parentId -->";
        continue;
    }
    if ($advancedCustom->ShowAllVideosOnCategory) {
        $total = $value['fullTotal'];
    } else {
        $total = $value['total'];
    }
    
    if (empty($value['fullTotal']) && empty($value['total'])) {
        echo "<!-- generateHorizontalFlickity continue total -->";
        continue;
    }
    if (in_array($value['id'], $parsed_cats)) {
        echo "<!-- generateHorizontalFlickity continue parsed_cats -->";
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
TimeLogEnd($timeLogName, __LINE__);
echo "<!-- generateHorizontalFlickity categories=".count($categories).' items='.count($items)."-->";
//var_dump($_REQUEST['catName'], $items);exit;
generateHorizontalFlickity($items);
TimeLogEnd($timeLogName, __LINE__);
