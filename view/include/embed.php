<?php
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
$isVideoTypeEmbed = 1;
?>
<!-- Embed <?php echo basename(__FILE__); ?> -->
<?php
echo PlayerSkins::getMediaTag($video['filename']);
?>
<script>
    $(document).ready(function() {
        addView(<?php echo $video['id']; ?>, 0);
    });
</script>
<!-- Embed finish -->
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
