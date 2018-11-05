<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
VideosVR360::toogleVR360($_POST['videos_id']);
