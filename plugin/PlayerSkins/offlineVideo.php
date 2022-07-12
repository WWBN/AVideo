<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$videos_id = intval(@$_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!User::canWatchVideo($videos_id)) {
    forbiddenPage('you cannot watch this video');
}

$video = Video::getVideoLight($videos_id);
$sources = getVideosURL_V2($video['filename']);
$sourcesResolutions = array();
$mainResolution = false;

foreach ($sources as $key => $value) {
    if (preg_match('/^mp4_([0-9]+)/', $key, $matches)) {
        $option = array('url' => $value['url'], 'path' => $value['path'], 'resolution' => $matches[1]);
        $sourcesResolutions[] = $option;
        if ($option['resolution'] == 480) {
            $mainResolution = $option;
        }
    }
}
if (empty($mainResolution) && !empty($sourcesResolutions)) {
    $mainResolution = $sourcesResolutions[0];
}

function createOfflineDownloadPanel($option, $class = 'col-xs-6') {
    global $videos_id;
    ?>
    <div class="<?php echo $class; ?>">
        <div class="panel panel-default videos_offline_<?php echo $videos_id; ?>_<?php echo $option['resolution']; ?>">
            <div class="panel-heading">
                <button class="btn btn-danger"  onclick='_deleteOfflineVideo(<?php echo json_encode($option['resolution']); ?>);'>
                    <i class="fas fa-trash"></i> <?php echo __('Delete'); ?>
                </button>
                <button class="btn btn-warning" onclick='_downloadOfflineVideo(<?php echo json_encode($option['url']); ?>, <?php echo json_encode($option['resolution']); ?>, ".videos_offline_<?php echo $videos_id; ?>_<?php echo $option['resolution']; ?> .progress");'>
                    <i class="fas fa-download"></i> <?php echo __('Download'); ?>
                </button>
                <button class="btn btn-success" onclick='_updateVideo(<?php echo json_encode($videos_id); ?>);'>
                    <i class="fas fa-sync"></i> <?php echo __('Renew'); ?>
                </button>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item"><?php echo __('Resolution') ?> <span class="badge"><?php echo $option['resolution']; ?></span></li>
                    <li class="list-group-item"><?php echo __('Size') ?> <span class="badge"><?php echo humanFileSize(filesize($option['path'])); ?></span></li>
                </ul>
            </div>
            <div class="panel-footer">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0;">
                        0%
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title>Download Video</title>
        <?php
        //echo AVideoPlugin::getHeadCode();
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            .offlinevideos div.panel-success > div.panel-heading > button.btn.btn-warning{
                display: none;
            }
            .offlinevideos div.panel-default > div.panel-heading > button.btn.btn-danger,
            .offlinevideos div.panel-default > div.panel-heading > button.btn.btn-success{
                display: none;
            }
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">

            <div class="panel panel-default">
                <div class="panel-heading">
                </div>
                <div class="panel-body offlinevideos tabbable-line">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#offlineVideo"><?php echo __('Offline Video'); ?></a></li>
                        <li><a data-toggle="tab" href="#offlineVideoAdvanced"><?php echo __('Advanced'); ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="offlineVideo" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading clearfix">
                                            <?php
                                            echo Video::getVideosListItem($video['id']);
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <?php echo __('Total Size') ?> <span class="badge"><?php echo humanFileSize($video['filesize']); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    createOfflineDownloadPanel($mainResolution, '');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="offlineVideoAdvanced" class="tab-pane fade">
                            <div class="row">
                                <?php
                                foreach ($sourcesResolutions as $key => $option) {
                                    createOfflineDownloadPanel($option);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
        <script>
            const offlineVideoDbName = 'videos_offlineDb_<?php echo User::getId(); ?>';
            var mediaId = <?php echo $videos_id; ?>;
        </script>
        <script src="<?php echo getURL('node_modules/dexie/dist/dexie.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('plugin/PlayerSkins/offlineVideo.js'); ?>" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                listAllOfflineVideo();
            });
            function listAllOfflineVideo() {
                videos_id = <?php echo $videos_id; ?>;
                var collection = offlineDbRequest.offline_videos.where('videos_id').equals(videos_id);

                $('.panel').removeClass('panel-success');
                $('.panel').addClass('panel-default');
                changeProgressBarOfflineVideo('.progress', 0);
                collection.each(function (video) {
                    var elemSelector = '.videos_offline_' + video.videos_id_resolution;
                    $(elemSelector).removeClass('panel-default');
                    $(elemSelector).addClass('panel-success');

                    changeProgressBarOfflineVideo(elemSelector+' .progress', 100);
                });

            }
            async function _downloadOfflineVideo(src, resolution, progressBarSelector) {
                return await fetchVideoFromNetwork(src, 'video/mp4', resolution, progressBarSelector).then(function (video) {
                    console.log("_downloadOfflineVideo: ", video);
                    listAllOfflineVideo();
                    socketUpdateOfflineVideoSource(<?php echo json_encode($_REQUEST['socketResourceId']); ?>);
                }).catch(function (e) {
                    console.log("_downloadOfflineVideo Error: ", e);
                });
            }
            function _deleteOfflineVideo(resolution) {
                return deleteOfflineVideo(<?php echo $videos_id; ?>, resolution).then((video) => {
                    console.log('_deleteOfflineVideo', video);
                    listAllOfflineVideo();
                    socketUpdateOfflineVideoSource(<?php echo json_encode($_REQUEST['socketResourceId']); ?>);
                });
            }
        </script>
        <?php
        //echo AVideoPlugin::getFooterCode();
        ?>

    </body>
</html>