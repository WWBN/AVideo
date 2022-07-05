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
        ?>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <div class="container-fluid">
            <h1><?php echo $video['title']; ?></h1>
            <h2><?php echo humanFileSize($video['filesize']); ?></h2>
            <div class="row">
                <?php
                foreach ($sources as $key => $value) {
                    if (preg_match('/^mp4_([0-9]+)/', $key, $matches)) {
                        ?>
                        <div class="col-sm-3">
                            <div class="panel panel-default" id="videos_offline_<?php echo $videos_id; ?>_<?php echo $matches[1]; ?>">
                                <div class="panel-heading">
                                    <button class="btn btn-danger"  onclick="_deleteOfflineVideo(<?php echo json_encode($matches[1]); ?>);">
                                        <?php echo __('Delete'); ?>
                                    </button>
                                    <button class="btn btn-success" onclick="_downloadOfflineVideo(<?php echo json_encode($value['url']); ?>, <?php echo json_encode($matches[1]); ?>);">
                                        <?php echo __('Download'); ?>
                                    </button>
                                </div>
                                <div class="panel-body">
                                    Resolution: <?php echo $matches[1]; ?>p<br>
                                    Size: <?php echo humanFileSize(filesize($value['path'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        //var_dump($matches, $value);
                    }
                }
                //var_dump($video, $sources);
                ?>

            </div>
        </div>

        <script>
            const offlineVideoDbName = 'videos_offlineDb_<?php echo User::getId(); ?>';
            var mediaId = <?php echo $videos_id; ?>;
        </script>
        <script src="<?php echo getURL('plugin/PlayerSkins/offlineVideo.js'); ?>"></script>
        <script>
            $(document).ready(function () {
                listAllOfflineVideo();
            });
            function listAllOfflineVideo() {
                videos_id = <?php echo $videos_id; ?>;
                // Open transaction, get object store; make it a readwrite so we can write to the IofflineDb
                const objectStore = offlineDb.transaction(['videos_os'], 'readwrite').objectStore('videos_os');
                // Add the record to the IofflineDb using add()
                var myIndex = objectStore.index('videos_id');
                var getAllRequest = myIndex.getAll(IDBKeyRange.only(videos_id));
                getAllRequest.onsuccess = function () {
                    for (var item in getAllRequest.result) {
                        var video = getAllRequest.result[item];
                        var elemSelector = '#videos_offline_'+video.videos_id_resolution;
                        $(elemSelector).removeClass('panel-default');
                        $(elemSelector).addClass('panel-success');
                    }
                    console.log(getAllRequest.result);
                }
            }
            function _downloadOfflineVideo(resolution){
                deleteOfflineVideo(<?php echo $videos_id; ?>, resolution);
                listAllOfflineVideo();
            }
            function _deleteOfflineVideo(resolution){
                fetchVideoFromNetwork(src, 'video/mp4', resolution);
            }
        </script>
        <?php
        //echo AVideoPlugin::getFooterCode();
        ?>

    </body>
</html>