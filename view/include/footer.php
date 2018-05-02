<footer>
    <?php
    $custom = "";
    if (YouPHPTubePlugin::isEnabled("c4fe1b83-8f5a-4d1b-b912-172c608bf9e3")) {
        require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
        $ec = new ExtraConfig();
        $custom = $ec->getFooter();
    }
    if (empty($custom)) {
        ?>
        <ul class="list-inline">
            <li>
                Powered by <a href="http://www.youphptube.com" class="external btn btn-outline btn-primary btn-xs" target="_blank">YouPHPTube v<?php echo $config->getVersion(); ?></a>
            </li>
            <li>
                <a href="https://www.facebook.com/mediasharingtube/" class="external btn btn-outline btn-primary btn-xs" target="_blank"><span class="sr-only">Facebook</span><i class="fa fa-fw fa-facebook"></i></a>
            </li>
            <li>
                <a href="https://plus.google.com/u/0/113820501552689289262" class="external btn btn-outline btn-primary btn-xs" target="_blank"><span class="sr-only">Google Plus</span><i class="fa fa-fw fa-google-plus"></i></a>
            </li>
        </ul>
        <?php
    } else {
        echo $custom;
    }
    ?>
</footer>
<script>
    <?php
    if (User::isAdmin()) { ?>
    window.onerror = function myErrorHandler(errorMsg, url, lineNumber) {
    console.log("<?php echo 'A Javascript-error happend. Please tell your admin to clear the folder videos/cache. \r\n If this doesn\'t help, attach these infos to a github-pull-request:'; ?> \r\n Msg:" + errorMsg+" \r\n Url: "+url+ ", line: "+lineNumber);//or any message
    return false;
    }
    <?php } ?>
    
    // Just for testing
    //throw "A Bug";
    $(function () {
<?php
if (!empty($_GET['error'])) {
    ?>
            swal({title: "Sorry!", text: "<?php echo $_GET['error']; ?>", type: "error", html: true});
    <?php
}
?>
<?php
if (!empty($_GET['msg'])) {
    ?>
            swal({title: "Ops!", text: "<?php echo $_GET['msg']; ?>", type: "info", html: true});
    <?php
}
?>
    });
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<?php
    $jsFiles = array();
    //$jsFiles[] = "{$global['webSiteRootURL']}bootstrap/js/bootstrap.min.js";
    $jsFiles[] = "view/js/seetalert/sweetalert.min.js";
    $jsFiles[] = "view/js/bootpag/jquery.bootpag.min.js";
    $jsFiles[] = "view/js/bootgrid/jquery.bootgrid.js";
    $jsFiles[] = "view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js";
    $jsFiles[] = "view/js/script.js";
    $jsFiles[] = "view/js/bootstrap-toggle/bootstrap-toggle.min.js";
    $jsFiles[] = "view/js/js-cookie/js.cookie.js";
    $jsFiles[] = "view/css/flagstrap/js/jquery.flagstrap.min.js";
    $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.min.js";
    $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.plugins.min.js";
    //$jsFiles[] = "{$global['webSiteRootURL']}view/js/videojs-wavesurfer/wavesurfer.min.js";
    //$jsFiles[] = "{$global['webSiteRootURL']}view/js/videojs-wavesurfer/dist/videojs.wavesurfer.min.js";
    $jsURL =  combineFiles($jsFiles, "js");

?>
<script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>css/font-awesome-5.0.10/svg-with-js/js/fontawesome-all.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>css/font-awesome-5.0.10/svg-with-js/js/fa-v4-shims.min.js" type="text/javascript"></script>
<?php
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
echo YouPHPTubePlugin::getFooterCode();
?>