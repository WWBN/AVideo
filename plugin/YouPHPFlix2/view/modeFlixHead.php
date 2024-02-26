<link href="<?php echo getURL('view/js/webui-popover/jquery.webui-popover.min.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo getURL('node_modules/flickity/dist/flickity.min.css'); ?>" rel="stylesheet" type="text/css" />
<style>
<?php
if (isMobile()) {
    ?>
        #carouselRows .posterDetails {
            height: 100% !important;
        }
    <?php
}
?>
</style>