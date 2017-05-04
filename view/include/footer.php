<?php
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<hr>
<footer>
    <p>Powered by <a href="http://www.youphptube.com" class="external btn btn-outline btn-primary" target="_blank">YouPHPTube v<?php echo $config->getVersion(); ?></a> &copy; 2017, All rights reserved</p>
</footer>
<script>
$(function() {
    <?php 
    if(!empty($_GET['error'])){
    ?>
    swal({title:"Sorry!", text: "<?php echo $_GET['error']; ?>", type:"error",  html:true});
    <?php
    }
    ?>
});
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/seetalert/sweetalert.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootpag/jquery.bootpag.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootgrid/jquery.bootgrid.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/script.js" type="text/javascript"></script>