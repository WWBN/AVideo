<?php
global $isSerie;
$isSerie = 1;
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
?>
<!-- Serie -->
<?php
echo PlayerSkins::getMediaTag($video['filename']);
?>
<!-- Serie finish -->
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
