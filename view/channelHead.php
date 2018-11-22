<link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script>
    /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
    $.widget.bridge('uibutton', $.ui.button);
    $.widget.bridge('uitooltip', $.ui.tooltip);
    var channelName = '<?php echo $_GET['channelName']; ?>';
</script>
<!-- users_id = <?php echo $user_id; ?> -->
<link href="<?php echo $global['webSiteRootURL']; ?>/plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
<style>
    .galleryVideo {
        padding-bottom: 10px;
    }
</style>