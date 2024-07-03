<?php
PlayerSkins::createMarker($rows);
PlayerSkins::getStartPlayerJS('setTimeout(function(){adjustMarkerWidths();},1000);');
?>
<link  href="<?php echo getURL('plugin/Bookmark/style.css'); ?>" rel="stylesheet" type="text/css"/>
<script src="<?php echo getURL('plugin/Bookmark/script.js'); ?>" type="text/javascript"></script>
