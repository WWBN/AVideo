<?php
global $global;
if (class_exists("Plugin") && empty($global['avideoEndIncluded'])) {
    AVideoPlugin::getEnd();
    _error_log('avideoEndIncluded');
}else{
    _error_log('avideoEndIncluded ERROR');
}
$global['avideoEndIncluded'] = 1;
