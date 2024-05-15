<?php



$obj = AVideoPlugin::getObjectDataIfEnabled('MobileYPT');
$url = 'http://192.168.0.2/youphptube.com/mobile/qrcode/';
$url = 'https://youphp.tube/mobile/qrcode/';
$url = addQueryStringParameter($url, 'site', $global['webSiteRootURL']);
$url = addQueryStringParameter($url, 'user', User::getUserName());
$url = addQueryStringParameter($url, 'pass', User::getUserPass());
$url = addQueryStringParameter($url, 'users_id', User::getId());
$url = addQueryStringParameter($url, 'isMobile', isMobile() ? 1 : 0);
$url = addQueryStringParameter($url, 'qrcode', 1);

$onclick = "avideoModalIframeXSmall('{$url}');";

$objAPI = AVideoPlugin::getObjectDataIfEnabled('API');
if(empty($objAPI)){
    $onclick = "avideoAlertError('Enable API plugin first');";
}
?>
<li>
    <hr>
    <strong class="text-danger hideIfCompressed">
        <i class="fas fa-mobile"></i>
        <?php echo __('Mobile'); ?>
    </strong>
</li>
<li>
    <a href="#" onclick="avideoModalIframeXSmall('<?php echo $url; ?>');return false;">
        <i class="fas fa-qrcode"></i>
        <span class="menuLabel">
            <?php echo __('Connect Mobile App'); ?>
        </span>
    </a>
</li>