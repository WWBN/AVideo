<?php
PlayerSkins::createMarker($rows);
PlayerSkins::getStartPlayerJS('setTimeout(function(){adjustMarkerWidths();},1000);');
?>
<script src="<?php echo getURL('plugin/Bookmark/script.js'); ?>" type="text/javascript"></script>
