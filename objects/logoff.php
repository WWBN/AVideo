<?php
global $global, $config;
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
User::logoff();
header("location: {$global['webSiteRootURL']}");
