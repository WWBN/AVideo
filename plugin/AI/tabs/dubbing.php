<?php
$hlsIsEnabled = AVideoPlugin::loadPluginIfEnabled('VideoHLS');
$type = Video::getVideoTypeFromId($videos_id, true);
if (empty($type->m3u8)) {
?>
    <div class="alert alert-warning">
        <strong>Note:</strong> This feature is only available for a video in HLS format. If you're not familiar with these requirements, please contact support for assistance.
        <?php
        foreach ($type as $key => $value) {
            echo "<div><strong>{$key}</strong>: ".(empty($value)?'YES':'NO')."</div>";
        }
        ?>
    </div>
<?php
    return;
}
?>
<div class="row">
    <div class="col-sm-4">
        <?php
        include __DIR__ . '/dubbing.panel.php';
        ?>
    </div>
    <div class="col-sm-8">
        <?php
        $global['doNotPrintPage'] = true; // do not print the header and footer
        include_once __DIR__ . '/../../../plugin/VideoHLS/languages/index.php';
        $global['doNotPrintPage'] = false; // reset the flag
        ?>
    </div>
</div>
