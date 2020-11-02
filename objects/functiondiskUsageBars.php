<?php
$getDiskUsage = getDiskUsage();
?>
<div class="clearfix" style="margin: 10px 12px;">
    <div class="progress" style="margin: 2px;">
        <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo $getDiskUsage->videos_dir_used_percentage; ?>%">
            <?php echo $getDiskUsage->videos_dir_used_percentage; ?>%
        </div>
        <div class="progress-bar progress-bar-warning" role="progressbar" style="width:<?php echo $getDiskUsage->disk_used_percentage - $getDiskUsage->videos_dir_used_percentage; ?>%">
            <?php echo $getDiskUsage->disk_used_percentage - $getDiskUsage->videos_dir_used_percentage; ?>%
        </div>
        <div class="progress-bar progress-bar-default" role="progressbar" style="width:<?php echo $getDiskUsage->disk_free_space_percentage; ?>%">
            <?php echo $getDiskUsage->disk_free_space_percentage; ?>%
        </div>
    </div>
    <div class="label label-success">
        <?php echo __("Videos Directory"); ?>: <?php echo $getDiskUsage->videos_dir_human; ?> (<?php echo $getDiskUsage->videos_dir_used_percentage; ?>%)
    </div>
    <div class="label label-warning">
        <?php echo __("Other Files"); ?>: <?php echo $getDiskUsage->disk_used_human; ?> (<?php echo $getDiskUsage->disk_used_percentage - $getDiskUsage->videos_dir_used_percentage; ?>%)
    </div>
    <div class="label label-primary">
        <?php echo __("Free Space"); ?>: <?php echo $getDiskUsage->disk_free_space_human; ?> (<?php echo $getDiskUsage->disk_free_space_percentage; ?>%)
    </div>
</div>
<?php
if ($getDiskUsage->disk_free_space_percentage < 15) {
    echo "<script>$(document).ready(function () {avideoAlertHTMLText('Danger','Your Disk is almost Full, you have only <strong>{$getDiskUsage->disk_free_space_percentage}%</strong> free <br> ({$getDiskUsage->disk_free_space_human})', 'error');});</script>";
}
