<?php
$backURL = getBackURL();
if (empty($backURL)) {
    $backURL = $global['webSiteRootURL'];
}else{
    // if back URL is another video send it to the main page
    $videos_id = getVideoIDFromURL($backURL);
    if(!empty($videos_id)){
        $backURL = $global['webSiteRootURL'];
    }
}

?>
<div id="divTopBar" style="position: fixed; top: 0; left: 0; height: 50px; width: 100vw; z-index: 99999; padding:10px; ">
    <a href="<?php echo $backURL; ?>" id="closeBtnFull" class="pull-right" >
        <i class="fas fa-times"></i>
    </a>
</div>