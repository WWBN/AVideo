<?php
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<hr>
<footer>
    <ul class="list-inline">
        <li>
            Powered by <a href="http://www.youphptube.com" class="external btn btn-outline btn-primary" target="_blank">YouPHPTube v<?php echo $config->getVersion(); ?></a>
        </li>
        <li>
            <a href="https://www.facebook.com/mediasharingtube/" class="external btn btn-outline btn-primary" target="_blank"><span class="sr-only">Facebook</span><i class="fa fa-fw fa-facebook"></i></a>
        </li>
        <li>
            <a href="https://plus.google.com/u/0/113820501552689289262" class="external btn btn-outline btn-primary" target="_blank"><span class="sr-only">Google Plus</span><i class="fa fa-fw fa-google-plus"></i></a>
        </li>
    </ul>
</footer>
<script type="application/ld+json">
    {
    "@context": "http://schema.org/",
    "@type": "Product",
    "name": "YouPHPTube",
    "image": "http://youphptube.com/img/logo.png",
    "description": "Free web solution to build your own video sahring site."
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