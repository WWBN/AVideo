<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$t = LiveTransmition::getFromDbByUserName($_GET['u']);
$uuid = $t['key'];

$u = new User(0, $_GET['u'], false);
$user_id = $u->getBdId();
$subscribe = Subscribe::getButton($user_id);
$name = $u->getNameIdentificationBd();
$video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($user_id) . '" alt="" class="img img-responsive img-circle" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br>' . $subscribe . '</div></div>';

$img = "{$global['webSiteRootURL']}plugin/Live/getImage.php?u={$_GET['u']}&format=jpg";
$data = getimgsize("{$global['systemRootPath']}videos/{$video['filename']}.jpg");
$imgw = $data[0];
$imgh = $data[1];
if(empty($imgw) || empty($imgh)){
    $imgw = 640;
    $imgh = 360;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $t['title']; ?> - Live Video - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/videojs-contrib-ads/videojs.ads.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        
        <meta property="fb:app_id"             content="774958212660408" />
        <meta property="og:url"                content="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?u=<?php echo $_GET['u']; ?>" />
        <meta property="og:type"               content="video.other" />
        <meta property="og:title"              content="<?php echo str_replace('"', '', $t['title']); ?> - <?php echo $config->getWebSiteTitle(); ?>" />
        <meta property="og:description"        content="<?php echo str_replace('"', '', $t['title']); ?>" />
        <meta property="og:image"              content="<?php echo $img; ?>" />
        <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
        <meta property="og:image:height"       content="<?php echo $imgh; ?>" />
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        $lt = new LiveTransmition($t['id']);
        if($lt->userCanSeeTransmition()){
        ?>
        
        <div class="container-fluid principalContainer " itemscope itemtype="http://schema.org/VideoObject">
            <div class="col-md-12">
                <?php
                require "{$global['systemRootPath']}plugin/Live/view/liveVideo.php";
                ?>
            </div>  
        </div>
        <div class="container-fluid ">
            <div class="col-md-5 col-md-offset-2 list-group-item">
                <h1 itemprop="name">
                    <i class="fa fa-video-camera"></i> <?php echo $t['title']; ?>
                </h1>
                <p><?php echo nl2br(textToLink($t['description'])); ?></p>
                <div class="col-xs-12 col-sm-12 col-lg-12"><?php echo $video['creator']; ?></div>
            </div> 
            <div class="col-md-3">
                    <?php
                    echo $config->getAdsense();
                    ?>
            </div>
        </div>
        <?php
        }else{
            ?>
        <h1 class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> You are not allowed see this streaming</h1>    
            <?php
        }
        ?>
        
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
                        /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
                        $.widget.bridge('uibutton', $.ui.button);
                        $.widget.bridge('uitooltip', $.ui.tooltip);
        </script>  
        
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-contrib-ads/videojs.ads.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/videojs-contrib-hls.min.js" type="text/javascript"></script>
        <?php        
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

                <?php
                $p->getChat($uuid);
                ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-list-filter/bootstrap-list-filter.min.js" type="text/javascript"></script>
        
    </body>
</html>

<?php
include $global['systemRootPath'].'objects/include_end.php';
?>