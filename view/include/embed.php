<?php
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
$isEmbed = 1;
?>
<!-- Embed -->
<?php
echo PlayerSkins::getMediaTag($video['filename']);
?>
<!-- Embed finish -->
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
