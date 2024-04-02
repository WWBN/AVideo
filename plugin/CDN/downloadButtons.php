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
$downloadOptions = getMP3ANDMP4DownloadLinksFromHLS($videos_id, $video['type']);

if (empty($downloadOptions)) {
    forbiddenPage('All download options on VideoHLS plugin are disabled');
}
$height = 'calc(50vh - 50px)';
if (count($downloadOptions) == 1) {
    $height = 'calc(100vh - 50px)';
}
$_page = new Page(array('Download Video'));
$_page->setExtraScripts(array('view/js/script.download.js'));
?>

<style>
    #downloadButtons .btn {
        height: <?php echo $height; ?>;
        font-size: 30px;
    }

    #downloadButtons a.btn span {
        display: block !important;
        white-space: break-spaces;
        padding-top: 15vh;
    }
</style>
<div class="container-fluid">
    <div id="downloadButtons">
        <?php
        $count = 0;
        $lastURL = '';
        $lastFormat = '';
        foreach ($downloadOptions as $theLink) {
            if (!empty($theLink)) {
                $count++;
                $lastURL = $theLink['url'];
                $progress = $theLink['progress'];
                $lastFormat = strtolower($theLink['name']);
        ?>
                <button type="button" onclick="_goToURLOrAlertError('<?php echo $lastURL; ?>', '<?php echo $progress; ?>', '<?php echo $lastFormat; ?>');" class="btn btn-default btn-light btn-lg btn-block" target="_blank">
                    <i class="fas fa-download"></i> <?php echo __('Download'); ?> <?php echo $theLink['name']; ?>
                </button>
            <?php
            }
        }

        $convertedFile = "{$global['systemRootPath']}videos/{$video['filename']}/index.mp4";
        if (User::isAdmin() && file_exists($convertedFile)) {
            $deleteURL = addQueryStringParameter($lastURL, 'delete', 1);
            ?>
            <button type="button" onclick="avideoAlertAJAX('<?php echo $deleteURL; ?>');" class="btn btn-danger btn-block">
                <i class="fa-solid fa-trash"></i> <?php echo __('Delete'); ?>
            </button>
        <?php
        } else {
            echo "<!-- converted file does not exists {$convertedFile} -->";
        }
        ?>
    </div>
</div>
<script>
    function _goToURLOrAlertError(url, progress, format) {
        avideoToastSuccess(<?php echo json_encode(__('Downloading') . '... ' . $video['title']); ?>);
        downloadURLOrAlertError(url, {}, '<?php echo $video['clean_title']; ?>.' + format, progress);
    }
</script>
<?php
if ($count == 1) {
?>
    <script>
        $(function() {
            _goToURLOrAlertError('<?php echo $lastURL; ?>', '<?php echo $progress; ?>', '<?php echo $lastFormat; ?>');
        });
    </script>
<?php
}
?>
<?php
$_page->print();
?>