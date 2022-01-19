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
$downloadOptions = array();
if (!empty($videoHLSObj->saveMP4CopyOnCDNStorageToAllowDownload)) {
    $downloadOptions[] = VideoHLS::getCDNDownloadLink($videos_id, 'mp4');
}
if (!empty($videoHLSObj->saveMP3CopyOnCDNStorageToAllowDownload)) {
    $downloadOptions[] = VideoHLS::getCDNDownloadLink($videos_id, 'mp3');
}
if (empty($downloadOptions)) {
    forbiddenPage('All download options on VideoHLS plugin are disabled');
}
$video = Video::getVideoLight($videos_id);
$height = 'calc(50vh - 50px)';
if (count($downloadOptions) == 1) {
    $height = 'calc(100vh - 50px)';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Download Video</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #downloadButtons .btn{
                height: <?php echo $height; ?>;
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
                $count = 0;
                $lastURL = '';
                foreach ($downloadOptions as $theLink) {
                    if (!empty($theLink)) {
                        $count++;
                        $lastURL = $theLink['url'];
                        ?>
                        <button type="button" onclick="goToURLOrAlertError('<?php echo $theLink['url']; ?>', {});" 
                                class="btn btn-default btn-light btn-lg btn-block" target="_blank">
                            <i class="fas fa-download"></i> Download <?php echo $theLink['name']; ?>
                        </button>    
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';

        if ($count == 1) {
            ?>
            <script>
                $(function () {
                    goToURLOrAlertError('<?php echo $lastURL; ?>', {});
                });
            </script>
            <?php
        }
        ?>
    </body>
</html>
