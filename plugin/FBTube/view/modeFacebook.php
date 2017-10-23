<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/FBTube/view/style.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/FBTube/view/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/social.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-rotatezoom/videojs.zoomrotate.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-list-filter/bootstrap-list-filter.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">
            <div class="col-lg-2 col-md-1 col-sm-1 hidden-xs"></div>
            <div class="col-lg-6 col-md-7 col-sm-8 col-xs-12" id="result">

            </div>
            <div class="col-lg-4 col-md-4 col-sm-3 hidden-xs">
                <div data-spy="affix" style="margin-right: 10vw;" >
                    <div class="list-group-item ">
                        <?php
                        echo $config->getAdsense();
                        ?>
                    </div>                    
                </div>
            </div>


        </div>
        <?php
        include 'include/footer.php';
        ?>

        <script>
            function load(page) {
                $('#result').append($('<div>').load('<?php echo $global['webSiteRootURL']; ?>plugin/FBTube/view/getVideos.php?page=' + page));
            }
            $(document).ready(function () {
                $(window).scroll(function () {
                    $(".fbRow").each(function (index) {
                        var $h1 = $(this);
                        var window_offset = $h1.offset().top - $(window).scrollTop();
                        if (window_offset > 50 && window_offset < 100) {
                            $(".fbRow").each(function (index) {
                                try {
                                    $(this).find('video').get(0).pause();
                                } catch (err) {}
                                try {
                                    id = $(this).find('.embed-responsive-item').attr('id');
                                    console.log(id);
                                    document.getElementById('iframe'+id).contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                                } catch (err) {}
                                $(this).find('.list-group-item').removeClass('playActive');
                            });
                            try {
                                $(this).find('video').get(0).play();
                            } catch (err) {}
                            try {
                                id = $(this).find('.embed-responsive-item').attr('id');
                                console.log(id);
                                document.getElementById('iframe'+id).contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                            } catch (err) {}
                            $(this).find('.list-group-item').addClass('playActive');
                            return true;
                        }
                    });
                });
                load(1);
            });
        </script>
    </body>
</html>
