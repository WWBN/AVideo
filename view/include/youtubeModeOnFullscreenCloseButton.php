<?php
if (!isIframe()) {
    echo "<!-- divTopBar !isIframe -->";
    return false;
}
if (!isEmbed()) {
    echo "<!-- divTopBar !isEmbed -->";
    return false;
}
$backURL = getBackURL();
if (empty($backURL)) {
    return false;
}

?>
<div id="divTopBar" style="position: fixed; top: 0; left: 0; height: 50px; width: 100vw; z-index: 99999; padding:10px; ">
    <a href="<?php echo $backURL; ?>" id="closeBtnFull" class="pull-right" >
        <i class="fas fa-times"></i>
    </a>
</div>