<div id="loading" class="loader"
     style="border-width: 0; width: 20vh; height: 20vh; position: absolute; left: 50%; top: 50%; margin-left: -10vh; margin-top: -10vh;">
    <img src="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/img/loading.png" class="img img-responsive" alt="Loading"/>
</div>
<div style="display:none;" id="footerDiv">
    <?php
    include $global['systemRootPath'] . 'view/include/footer.php';

    if (!empty($tmpSessionType)) {
        $_SESSION['type'] = $tmpSessionType;
    } else {
        unset($_SESSION['type']);
    }
    $jsFiles = array("view/js/bootstrap-list-filter/bootstrap-list-filter.min.js", "plugin/YouPHPFlix2/view/js/flickity/flickity.pkgd.min.js", "view/js/webui-popover/jquery.webui-popover.min.js", "plugin/YouPHPFlix2/view/js/script.js");
    $jsURL = combineFiles($jsFiles, "js");
    ?>
</div>
<script src="<?php echo getCDN(); ?>view/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
<script src="<?php echo getCDN(); ?>plugin/Gallery/script.js" type="text/javascript"></script>
<script src="<?php echo $jsURL; ?>" type="text/javascript"></script>