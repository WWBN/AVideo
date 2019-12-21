<?php
$obj = AVideoPlugin::getObjectDataIfEnabled('AD_Overlay');

$ad = new AD_Overlay_Code(0);
$ad->loadFromUser(User::getId());
?>
<li>
    <a data-toggle="tab" href="#adOverlay" id="aPersonalInfo">
        <?php echo __("Ad Overlay") ?>
        <?php
        if (!empty($ad->getStatus()) && $ad->getStatus() == 'a') {
            ?>
            <span class="label label-success adsStatus">Ads Active</span>
            <?php
        } else {
            ?>
            <span class="label label-danger adsStatus">Ads Inacitive</span>
            <?php
        }
        ?>
    </a>
</li>