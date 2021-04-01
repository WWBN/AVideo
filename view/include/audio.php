<?php
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
$isAudio = 1;
?>
<!-- Audio -->
<?php
echo PlayerSkins::getMediaTag($video['filename']);
?>
<!-- Audio finish -->
<?php
include $global['systemRootPath'] . 'plugin/PlayerSkins/contextMenu.php';
?>
