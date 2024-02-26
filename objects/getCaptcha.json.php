<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$captchaImage = getIncludeFileContent($global['systemRootPath'] . 'objects/getCaptcha.php');

//echo $captchaImage;exit;

$obj = new stdClass();
$obj->session_id = session_id();
$obj->base64String = base64_encode($captchaImage);

header('Content-Type: application/json');
die(json_encode($obj));
