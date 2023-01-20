<?php
$obj = AVideoPlugin::getObjectDataIfEnabled('MobileYPT');
$url = 'http://192.168.0.2/youphptube.com/mobile/qrcode/';
$url = 'https://youphp.tube/mobile/qrcode/';
$url = addQueryStringParameter($url, 'site', $global['webSiteRootURL']);
$url = addQueryStringParameter($url, 'user', User::getUserName());
$url = addQueryStringParameter($url, 'pass', User::getUserPass());
$url = addQueryStringParameter($url, 'users_id', User::getId());

?>
<li>
    <hr>
    <strong class="text-danger">
        <i class="fas fa-mobile"></i>
        <?php echo __('Mobile'); ?>
    </strong>
</li>
<li>
    <a href="#" onclick="avideoModalIframeXSmall('<?php echo $url; ?>');return false;" >
        <i class="fas fa-qrcode"></i> <?php echo __('Connect Mobile App'); ?>
    </a>
</li>      



