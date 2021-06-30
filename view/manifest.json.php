<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('Content-Type: application/json');

$obj = AVideoPlugin::getDataObject('MobileManager');

$pwa = new stdClass();

$pwa->short_name = $config->getWebSiteTitle();
$pwa->name = $config->getWebSiteTitle();
$pwa->description = $config->getWebSiteTitle();

$pwa->icons = pwaIconsArray();

//$pwa->start_url = $global['webSiteRootURL'];
$pwa->start_url = '/';

$pwa->background_color = $obj->pwa_background_color;
$pwa->theme_color = $obj->pwa_background_color;
$pwa->orientation = "portrait";
$pwa->display_override = array("window-control-overlay", "minimal-ui");
$pwa->display = $obj->pwa_display->value;
$pwa->scope = $obj->pwa_scope;

$pwa->related_applications = array();
$pwa->related_applications[] = pwaRelated_applications('play', $obj->playStoreApp);
$pwa->related_applications[] = pwaRelated_applications('itunes', $obj->appleStoreApp);


$shortcut = new stdClass();
$shortcut->name = $config->getWebSiteTitle();
$shortcut->short_name = $config->getWebSiteTitle();
$shortcut->description = $config->getWebSiteTitle();
//$shortcut->url = $global['webSiteRootURL'];
$shortcut->url = '/';
$shortcut->icons = pwaIconsArray();

$pwa->shortcuts = array($shortcut);

echo _json_encode($pwa);

function pwaRelated_applications($platform, $url) {
    $obj = new stdClass();
    $obj->platform = $platform;
    $obj->url = $url;
    return $obj;
}

function pwaIcon($src, $type, $sizes) {
    $icon = new stdClass();
    $icon->src = $src;
    $icon->type = $type;
    $icon->sizes = $sizes;
    return $icon;
}

function pwaIconsArray() {

    $icon = array();

    $favicon = Configuration::_getFavicon(true);
    $faviconICO = Configuration::_getFavicon(false);

    $sizes = array(72, 96, 120, 128, 144, 152, 180, 192, 384, 512);

    foreach ($sizes as $value) {
        $pwaIcon = "faviconPWA{$value}.png";
        if (!file_exists(getVideosDir() . $pwaIcon)) {
            im_resizePNG($favicon['file'], getVideosDir() . $pwaIcon, $value, $value);
        }
        $icon[] = pwaIcon(getCDN() . 'videos/' . $pwaIcon, 'image/png', "{$value}x{$value}");
    }
    //$icon[] = pwaIcon($favicon['url'], 'image/png', '180x180');
    //$icon[] = pwaIcon($faviconICO['url'], 'image/x-icon', '16x16,24x24,32x32,48x48,144x144');

    return $icon;
}
?>