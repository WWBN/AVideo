<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

$videos_id = intval($_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!User::canWatchVideo($videos_id)) {
    forbiddenPage('You cannot watch this video');
}

$videoHLSObj = AVideoPlugin::getDataObjectIfEnabled('VideoHLS');

if (empty($videoHLSObj)) {
    forbiddenPage('VideoHLS plugin is required for that');
}
$video = Video::getVideoLight($videos_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Download Video</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #downloadButtons a.btn{
                height: calc(50vh - 50px);
                font-size: 30px;
            }
            #downloadButtons a.btn span{
                display: block !important;
                white-space: break-spaces;
                padding-top: 15vh;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div id="downloadButtons">
                <?php
                if (!empty($videoHLSObj->saveMP4CopyOnCDNStorageToAllowDownload)) {
                    $theLink = VideoHLS::getCDNDownloadLink($videos_id, 'mp4');
                    if(!empty($theLink)){
                        ?>
                        <button type="button" onclick="goToURLOrAlertError('<?php echo $theLink['url']; ?>', {});" 
                                class="btn btn-default btn-light btn-lg btn-block" target="_blank">
                            <i class="fas fa-download"></i> <?php echo $theLink['name']; ?>
                        </button>    
                        <?php
                    }
                }
                if (!empty($videoHLSObj->saveMP3CopyOnCDNStorageToAllowDownload)) {
                    $link = VideoHLS::getCDNDownloadLink($videos_id, 'mp3');
                    if(!empty($link)){
                        ?>
                        <button type="button" onclick="goToURLOrAlertError('<?php echo $theLink['url']; ?>', {});" 
                                class="btn btn-default btn-light btn-lg btn-block" target="_blank">
                            <i class="fas fa-download"></i> <?php echo $theLink['name']; ?>
                        </button>    
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
