<?php
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
?>
<!-- video -->
<?php
echo PlayerSkins::getMediaTag($video['filename']);
?>
<!-- video finish -->
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
