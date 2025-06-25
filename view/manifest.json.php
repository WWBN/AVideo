<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('Content-Type: application/json');

$obj = AVideoPlugin::getDataObject('MobileManager');
if (empty($obj->pwa_display->value)) {
    $obj->pwa_display->value = 'fullscreen';
}
_session_write_close();

$pwa = new stdClass();

$pwa->short_name = $config->getWebSiteTitle();
$pwa->name = $config->getWebSiteTitle();
$pwa->description = $config->getWebSiteTitle();


$favicon = Configuration::_getFavicon(true);

$pwa->icons = pwaIconsArray($favicon);

//$pwa->start_url = $global['webSiteRootURL'];
$pwa->start_url = '/';

$pwa->background_color = $obj->pwa_background_color;
$pwa->theme_color = $obj->pwa_background_color;
$pwa->orientation = "any";
$pwa->display_override = [$obj->pwa_display->value, 'fullscreen', 'standalone', 'minimal-ui', "window-control-overlay"];
$pwa->display = $obj->pwa_display->value;
$pwa->scope = $obj->pwa_scope;

$pwa->related_applications = [];
$pwa->related_applications[] = pwaRelated_applications('play', $obj->playStoreApp);
$pwa->related_applications[] = pwaRelated_applications('itunes', $obj->appleStoreApp);


$shortcut = new stdClass();
$shortcut->name = $config->getWebSiteTitle();
$shortcut->short_name = $config->getWebSiteTitle();
$shortcut->description = $config->getWebSiteTitle();
//$shortcut->url = $global['webSiteRootURL'];
$shortcut->url = '/';
$shortcut->icons = pwaIconsArray($favicon);

$pwa->shortcuts = [$shortcut];

echo _json_encode($pwa);

function pwaRelated_applications($platform, $url)
{
    $obj = new stdClass();
    $obj->platform = $platform;
    $obj->url = $url;
    return $obj;
}
