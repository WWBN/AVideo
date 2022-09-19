<?php
global $global;
if (class_exists("Plugin") && empty($global['avideoEndIncluded'])) {
    AVideoPlugin::getEnd();
}else{
    _error_log('avideoEndIncluded');
}
$global['avideoEndIncluded'] = 1;
