<?php
global $config, $advancedCustom;
if (empty($advancedCustom->openEncoderInIFrame) || !isSameDomainAsMyAVideo($config->getEncoderURL())) {
    if (!empty($advancedCustom->encoderNetwork) && empty($advancedCustom->doNotShowEncoderNetwork)) {
        $params = new stdClass();
        $params->webSiteRootURL = $global['webSiteRootURL'];
        $params->user = User::getUserName();
        $params->pass = User::getUserPass();
        ?>
        <li>
            <a href="#" onclick='postFormToTarget("<?php echo $advancedCustom->encoderNetwork; ?>", "encoderN", <?php echo json_encode($params); ?>);return false;'  data-toggle="tooltip" title="<?php echo __("Choose one of our encoders to upload a file or download it from the Internet"); ?>" data-placement="left"
               class="faa-parent animated-hover" >
                <span class="fa fa-cogs"></span> <?php echo empty($advancedCustom->encoderNetworkLabel) ? __("Encoder Network") : __($advancedCustom->encoderNetworkLabel); ?>
            </a>
        </li>
        <?php
    }
    if (empty($advancedCustom->doNotShowEncoderButton)) {
        if (!empty($config->getEncoderURL())) {
            $params = new stdClass();
            $params->webSiteRootURL = $global['webSiteRootURL'];
            $params->user = User::getUserName();
            $params->pass = User::getUserPass();
            ?>
            <li>
                <a href="#" onclick='postFormToTarget("<?php echo $config->getEncoderURL(); ?>", "encoder", <?php echo json_encode($params); ?>);
                                    return false;'  data-toggle="tooltip" title="<?php echo __("Upload a file or download it from the Internet"); ?>" data-placement="left"
                   class="faa-parent animated-hover" >
                    <span class="fas fa-cog faa-spin"></span> <?php echo empty($advancedCustom->encoderButtonLabel) ? __("Encode video and audio") : __($advancedCustom->encoderButtonLabel); ?>
                </a>
            </li>
            <?php
        } else {
            ?>
            <li>
                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'siteConfigurations');return false;" ><span class="fa fa-cogs"></span> <?php echo __("Configure an Encoder URL"); ?></a>
            </li>
            <?php
        }
    }
} else {
    if (!empty($advancedCustom->encoderNetwork) && empty($advancedCustom->doNotShowEncoderNetwork)) {
        ?>
        <li>
            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'i/network');return false;" data-toggle="tooltip" title="<?php echo __("Choose one of our encoders to upload a file or download it from the Internet"); ?>" data-placement="left" >
                <span class="fa fa-cogs"></span> <?php echo empty($advancedCustom->encoderNetworkLabel) ? __("Encoder Network") : __($advancedCustom->encoderNetworkLabel); ?>
            </a>
        </li>
        <?php
    }
    if (empty($advancedCustom->doNotShowEncoderButton)) {
        if (!empty($config->getEncoderURL())) {
            ?>
            <li>
                <a class="faa-parent animated-hover" href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'i/upload');return false;" data-toggle="tooltip" title="<?php echo __("Upload a file or download it from the Internet"); ?>" data-placement="left" >
                    <span class="fa fa-cog faa-spin"></span> <?php echo empty($advancedCustom->encoderButtonLabel) ? __("Encode video and audio") : __($advancedCustom->encoderButtonLabel); ?>
                </a>
            </li>
            <?php
        } else {
            ?>
            <li>
                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'siteConfigurations');return false;"><span class="fa fa-cogs"></span> <?php echo __("Configure an Encoder URL"); ?></a>
            </li>
            <?php
        }
    }
}
?>