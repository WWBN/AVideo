<?php
global $global;
if(!empty($global['mainAreaLiveRow'])){
    return;
}
if (empty($_REQUEST['catName'])) {
    $objLive = AVideoPlugin::getDataObject('Live');
    if (empty($objLive->doNotShowLiveOnVideosList)) {
        include __DIR__.'/liveHTMLRows.php';
    }
}
$global['mainAreaLiveRow'] = 1;
