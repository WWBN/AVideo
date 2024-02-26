<?php
if (empty($global['userBootstrapLatest'])) {
    ?>
    <script src="<?php echo getURL('view/bootstrap/js/bootstrap.min.js'); ?>" type="text/javascript"></script>
    <?php
} else {
        ?>
    <script src="<?php echo getURL('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo getURL('view/bootstrap/compatibility.js'); ?>" type="text/javascript"></script
    <?php
    }
?>