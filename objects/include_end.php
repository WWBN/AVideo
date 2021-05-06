<?php
global $global;
if(class_exists("Plugin") && empty($global['avideoEndIncluded'])){AVideoPlugin::getEnd();}
$global['avideoEndIncluded'] = 1;
