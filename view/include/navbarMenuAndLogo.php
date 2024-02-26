<li>
    <ul class="left-side">
        <li style="max-width: 40px;">
            <?php
            echo getHamburgerButton('buttonMenu', 'x', 'class="btn btn-default pull-left hamburger"  data-toggle="tooltip"  title="' . __("Main Menu") . '" data-placement="right"');
            ?>
            <?php
            if ($advancedCustom->disableNavBarInsideIframe) {
            ?>
                <script>
                    $(document).ready(function() {
                        YPTHidenavbar();
                    });
                </script>
            <?php
            }
            ?>
        </li>
        <li style="width: 100%; text-align: center;">
            <a class="navbar-brand" id="mainNavbarLogo" href="<?php echo empty($advancedCustom->logoMenuBarURL) ? getHomePageURL() : $advancedCustom->logoMenuBarURL; ?>">
                <img src="<?php echo getURL($config->getLogo()); ?>" alt="<?php echo str_replace('"', '', $config->getWebSiteTitle()); ?>" class="img-responsive " width="250" height="70">
                <?php
                if (isFirstPage()) {
                ?>
                    <h1 class="hidden"><?php echo __('Site:'), ' ', $config->getWebSiteTitle(); ?></h1>
                <?php
                }
                ?>
            </a>
        </li>
        <?php
        if (!empty($advancedCustomUser->keepViewerOnChannel) && !empty($_SESSION['channelName'])) {
            $user = User::getChannelOwner($_SESSION['channelName']);
        ?>
            <li>
                <a class="navbar-brand" href="#" onclick="avideoModalIframeFull('<?php echo User::getChannelLinkFromChannelName($_SESSION['channelName']); ?>');
                            return false;">
                    <img src="<?php echo User::getPhoto($user['id']); ?>" alt="<?php echo str_replace('"', '', User::getNameIdentificationById($user['id'])); ?>" class="img img-circle " style="height: 33px; width: 33px; margin-right: 15px;">
                </a>
            </li>
        <?php
        }
        ?>
    </ul>
</li>