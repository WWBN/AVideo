<?php
global $global, $config;
//$doNotIncludeConfig = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$_GET['redirectUri'] = getRedirectUri();
forbiddenPage("We've detected that you're using an ad blocker. This content is accessible when no ad blocker is detected.");
?>