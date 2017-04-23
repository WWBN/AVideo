<hr>
<footer>
    <p>Powered by <a href="http://www.youphptube.com" class="external btn btn-outline btn-primary" target="_blank">YouPHPTube</a> &copy; 2017, All rights reserved</p>
</footer>
<script>
$(function() {
    <?php 
    if(!empty($_GET['error'])){
    ?>
    swal("Sorry!", "<?php echo $_GET['error']; ?>", "error");
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