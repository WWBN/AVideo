<?php
global $global;
if (!class_exists("Plugin")) {
    _error_log('avideoEndIncluded class Plugin not exists');
}elseif (!empty($global['avideoEndIncluded'])) {
    //_error_log('avideoEndIncluded already processed');
}else{
    AVideoPlugin::getEnd();
}
$global['avideoEndIncluded'] = 1;