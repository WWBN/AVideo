<?php
require_once 'user.php';
User::logoff();
header("location: {$global['webSiteRootURL']}");