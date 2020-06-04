<!-- users_id = <?php echo $user_id; ?> -->
<?php
include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixHead.php';
?>
<link href="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/css/style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
<style>
    .galleryVideo {
        padding-bottom: 10px;
    }
    #bigVideo{
        margin: 0 0 -350px 0!important;
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
    #carouselRows, .modeFlixContainer .carousel {
        height: 750px;
    }
    #carouselRows, .modeFlixContainer .poster {
        margin-top: 25px;
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
</style>