<?php
if (empty($advancedCustom->openEncoderInIFrame) || !isSameDomainAsMyAVideo($config->getEncoderURL())) {
    if (!empty($advancedCustom->encoderNetwork) && empty($advancedCustom->doNotShowEncoderNetwork)) {
        ?>
        <li>
            <form id="formEncoderN" method="post" action="<?php echo $advancedCustom->encoderNetwork; ?>" target="encoder"  autocomplete="off">
                <input type="hidden" name="webSiteRootURL" value="<?php echo $global['webSiteRootURL']; ?>"  autocomplete="off" />
                <input type="hidden" name="user" value="<?php echo User::getUserName(); ?>"  autocomplete="off" />
                <input type="hidden" name="pass" value="<?php echo User::getUserPass(); ?>"  autocomplete="off" />
            </form>
            <a href="#" onclick="$('#formEncoderN').submit();
                    return false;"  data-toggle="tooltip" title="<?php echo __("Choose one of our encoders to upload a file or download it from the Internet"); ?>" data-placement="left" >
                <span class="fa fa-cogs"></span> <?php echo empty($advancedCustom->encoderNetworkLabel) ? __("Encoder Network") : $advancedCustom->encoderNetworkLabel; ?>
            </a>
        </li>
        <?php
    }
    if (empty($advancedCustom->doNotShowEncoderButton)) {
        if (!empty($config->getEncoderURL())) {
            ?>
            <li>
                <form id="formEncoder" method="post" action="<?php echo $config->getEncoderURL(); ?>" target="encoder"  autocomplete="off" >
                    <input type="hidden" name="webSiteRootURL" value="<?php echo $global['webSiteRootURL']; ?>"  autocomplete="off"  />
                    <input type="hidden" name="user" value="<?php echo User::getUserName(); ?>"  autocomplete="off"  />
                    <input type="hidden" name="pass" value="<?php echo User::getUserPass(); ?>"  autocomplete="off"  />
                </form>
                <a href="#" onclick="$('#formEncoder').submit();
                        return false;"  data-toggle="tooltip" title="<?php echo __("Upload a file or download it from the Internet"); ?>" data-placement="left" >
                    <span class="fa fa-cog"></span> <?php echo empty($advancedCustom->encoderButtonLabel) ? __("Encode video and audio") : $advancedCustom->encoderButtonLabel; ?>
                </a>
            </li>
            <?php
        } else {
            ?>
            <li>
                <a href="<?php echo $global['webSiteRootURL']; ?>siteConfigurations" ><span class="fa fa-cogs"></span> <?php echo __("Configure an Encoder URL"); ?></a>
            </li>
            <?php
        }
    }
}else{
    
    if (!empty($advancedCustom->encoderNetwork) && empty($advancedCustom->doNotShowEncoderNetwork)) {
        ?>
        <li>
            <a href="<?php echo $global['webSiteRootURL']; ?>i/network"  data-toggle="tooltip" title="<?php echo __("Choose one of our encoders to upload a file or download it from the Internet"); ?>" data-placement="left" >
                <span class="fa fa-cogs"></span> <?php echo empty($advancedCustom->encoderNetworkLabel) ? __("Encoder Network") : $advancedCustom->encoderNetworkLabel; ?>
            </a>
        </li>
        <?php
    }
    if (empty($advancedCustom->doNotShowEncoderButton)) {
        if (!empty($config->getEncoderURL())) {
            ?>
            <li>
                <a href="<?php echo $global['webSiteRootURL']; ?>i/upload"  data-toggle="tooltip" title="<?php echo __("Upload a file or download it from the Internet"); ?>" data-placement="left" >
                    <span class="fa fa-cog"></span> <?php echo empty($advancedCustom->encoderButtonLabel) ? __("Encode video and audio") : $advancedCustom->encoderButtonLabel; ?>
                </a>
            </li>
            <?php
        } else {
            ?>
            <li>
                <a href="<?php echo $global['webSiteRootURL']; ?>siteConfigurations"><span class="fa fa-cogs"></span> <?php echo __("Configure an Encoder URL"); ?></a>
            </li>
            <?php
        }
    }
}
?>