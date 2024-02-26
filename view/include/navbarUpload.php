
<li>
    <div data-toggle="tooltip" title="<?php echo __("Submit your videos"); ?>" data-placement="left"  class="btn-group">
        <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left  faa-parent animated-hover"  data-toggle="dropdown">
            <i class="<?php echo $advancedCustom->uploadButtonDropdownIcon ?? "fas fa-video"; ?>"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? __($advancedCustom->uploadButtonDropdownText) : ""; ?> <span class="caret"></span>
        </button>
        <?php
        if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && User::isVerified()) || (isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && !$advancedCustomUser->onlyVerifiedEmailCanUpload) || !isset($advancedCustomUser->onlyVerifiedEmailCanUpload)) {
            echo '<!-- navbar line ' . __LINE__ . '-->';
            ?>
            <ul class="dropdown-menu dropdown-menu-right dropdown-menu-arrow " role="menu" id="uploadMenu">
                <?php
                include $global['systemRootPath'] . 'view/include/navbarEncoder.php';
                //var_dump(CustomizeAdvanced::showDirectUploadButton());exit;
                if (CustomizeAdvanced::showDirectUploadButton()) {
                    ?>
                    <li>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'mvideos?upload=1');return false;" data-toggle="tooltip" 
                           title="<?php echo __("Upload files without encode"), ' ', implode(', ',CustomizeAdvanced::directUploadFiletypes()); ?>" 
                           data-placement="left" class="faa-parent animated-hover" >
                            <span class="fas fa-upload faa-bounce"></span> <?php echo empty($advancedCustom->uploadMP4ButtonLabel) ? __("Direct upload") : __($advancedCustom->uploadMP4ButtonLabel); ?>
                        </a>
                    </li>
                    <?php
                }
                if (empty($advancedCustom->doNotShowImportMP4Button)) {
                    ?>
                    <li>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'view/import.php');return false;" data-toggle="tooltip" title="<?php echo __("Search for videos in your local disk"); ?>" data-placement="left" class="faa-parent animated-hover" >
                            <span class="fas fa-hdd faa-ring"></span> <?php echo empty($advancedCustom->importMP4ButtonLabel) ? __("Direct Import Local Videos") : __($advancedCustom->importMP4ButtonLabel); ?>
                        </a>
                    </li>
                    <?php
                }
                if (empty($advancedCustom->doNotShowEmbedButton)) {
                    ?>
                    <li>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'mvideos?link=1');return false;" data-toggle="tooltip" title="<?php echo __("Embed videos/files in your site"); ?>" data-placement="left" class="faa-parent animated-hover" >
                            <span class="fa fa-link faa-burst"></span> <?php echo empty($advancedCustom->embedButtonLabel) ? __("Embed a video link") : __($advancedCustom->embedButtonLabel); ?>
                        </a>
                    </li>
                    <?php
                }
                if (AVideoPlugin::isEnabledByName("Articles")) {
                    ?>
                    <li>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'mvideos?article=1');return false;" data-toggle="tooltip" title="<?php echo __("Write an article"); ?>" data-placement="left"  class="faa-parent animated-hover">
                            <i class="far fa-newspaper faa-horizontal"></i> <?php echo __("Add Article"); ?>
                        </a>
                    </li>
                    <?php
                }
                echo AVideoPlugin::getUploadMenuButton();
                ?>
            </ul>     
            <?php
        } else {
            echo '<!-- navbar line ' . __LINE__ . '-->';
            ?>
            <ul class="dropdown-menu dropdown-menu-right dropdown-menu-arrow " role="menu" id="uploadMenu">
                <li>
                    <a  href="" >
                        <span class="fa fa-exclamation faa-flash animated"></span> <?php echo __("Only verified users can upload"); ?>
                    </a>
                </li>
                <?php echo AVideoPlugin::getUploadMenuButton(); ?>
            </ul>

        <?php }
        ?>
    </div>

</li>