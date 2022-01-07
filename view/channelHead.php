<!-- users_id = <?php echo $user_id; ?> -->
<?php
include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixHead.php';
?>
<link href="<?php echo getCDN(); ?>plugin/YouPHPFlix2/view/css/style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo getCDN(); ?>plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
<style>
    .galleryVideo {
        padding-bottom: 10px;
    }
    #bigVideoCarousel{
        height: auto;
    }
    .posterDetails {
        padding: 10px !important;
    }
    .modeFlixContainer{
        padding: 0;
    }
    .topicRow{
        margin: 0;
    }
    #loading{
        display: none;
    }
    .topicRow h2{
        display: none;
    }
    #channelHome #bigVideo{
        margin-bottom: 0 !important;
    }
</style>