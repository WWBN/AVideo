<?php
echo '<!-- moment.js start -->';
echo getTagIfExists('node_modules/moment/min/moment.min.js');
//echo getTagIfExists('node_modules/moment-timezone/builds/moment-timezone.min.js');
echo getTagIfExists('node_modules/moment-timezone/builds/moment-timezone-with-data.min.js');
echo getTagIfExists('node_modules/moment/locale/' . getLanguage() . '.js');
echo '<!-- moment.js end -->';
?>