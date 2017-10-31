<hr>
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
<script type="application/ld+json">
    {
    "@context": "http://schema.org/",
    "@type": "Product",
    "name": "YouPHPTube",
    "version": "<?php echo $config->getVersion(); ?>",
    "image": "http://youphptube.com/img/logo.png",
    "description": "The Best YouTube Clone Script, a free web solution to build your own video sahring site."
    }
</script>
<script>
    $(function () {
<?php
if (!empty($_GET['error'])) {
    ?>
            swal({title: "Sorry!", text: "<?php echo $_GET['error']; ?>", type: "error", html: true});
    <?php
}
?>
    });
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/seetalert/sweetalert.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootpag/jquery.bootpag.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootgrid/jquery.bootgrid.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/script.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-toggle/bootstrap-toggle.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/js-cookie/js.cookie.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>css/flagstrap/js/jquery.flagstrap.min.js" type="text/javascript"></script>
<?php
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
echo YouPHPTubePlugin::getFooterCode();
?>