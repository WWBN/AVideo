
<?php
if (empty($advancedCustomUser->doNotShowRightProfile) && (User::isLogged() || empty($advancedCustomUser->signInOnRight))) {
    $tooltip = '';
    if (User::isLogged()) {
        $tooltip = 'data-toggle="tooltip" data-html="true" title="' . User::getNameIdentification() . ":: " . User::getMail() . '" data-placement="left"';
    } else {
        $tooltip = 'data-toggle="tooltip" data-html="true" title="' . __("Login") . '" data-placement="left"';
    }
    ?>
    <div class="navbar-header pull-right" id="navbarRightProfile" <?php echo $tooltip; ?> >
        <ul >
            <li class="rightProfile" >
                <div class="btn-group" id="rightProfileBtnGroup" >
                    <?php
                    if (User::isLogged()) {
                        ?>
                        <button type="button" class="btn btn-default dropdown-toggle navbar-btn pull-left btn-circle"  id="rightProfileButton" style="padding:0;" onclick="toogleRightProfile();">
                            <img src="<?php echo User::getPhoto(); ?>"
                                 style="width: 32px; height: 32px; max-width: 32px;"
                                 class="img img-responsive img-circle" alt="User Photo"
                                 />
                        </button>
                        <script>
                            function toogleRightProfile() {
                                if ($('#rightProfileBtnGroup').hasClass('open')) {
                                    $('#rightProfileButton').removeClass('glowBox');

                                    $('#rightProfileBtnGroup .dropdown-menu').removeClass('animate__bounceInRight');
                                    $('#rightProfileBtnGroup .dropdown-menu').addClass('animate__bounceOutRight');
                                    setTimeout(function () {
                                        $('#rightProfileBtnGroup').removeClass('open');
                                        $('#rightProfileButton').attr('aria-expanded', false);
                                    }, 500);
                                } else {
                                    $('#rightProfileButton').addClass('glowBox');
                                    $('#rightProfileBtnGroup .dropdown-menu').removeClass('animate__bounceOutRight');
                                    $('#rightProfileBtnGroup .dropdown-menu').addClass('animate__bounceInRight');
                                    $('#rightProfileBtnGroup').addClass('open');
                                    $('#rightProfileButton').attr('aria-expanded', true).focus();
                                }
                            }
                        </script>
                        <?php echo '<!-- navbar line ' . __LINE__ . '-->'; ?>
                        <ul class="dropdown-menu dropdown-menu-right <?php echo getCSSAnimationClassAndStyle('animate__bounceInRight', 'rightProfileButton', 0); ?> overflow:visible;" >
                            <li style="padding: 10px 10px 0 10px; min-height: 60px;" class="clearfix">
                                <img src="<?php echo User::getPhoto(); ?>" style="max-width: 50px; max-height: 50px; margin: 0 5px 0 0;"  class="img img-responsive img-circle pull-left" alt="User Photo"/>
                                <div  class="pull-left"  >
                                    <strong class="text-danger"><?php echo User::getNameIdentification(); ?></strong>
                                    <div style="white-space: nowrap;
                                         overflow: hidden;
                                         text-overflow: ellipsis; margin: 0 5px;"><small><?php echo User::getMail(); ?></small></div>

                                </div>
                            </li>
                            <?php
                            if (!$advancedCustomUser->disableSignOutButton) {
                                ?>
                                <li>
                                    <a href="#" onclick="avideoLogoff(true);" >
                                        <?php
                                        $userCookie = User::getUserCookieCredentials();
                                        if ((!empty($userCookie))) {
                                            ?>
                                            <i class="fas fa-lock text-muted" style="opacity: 0.2;"></i>
                                            <?php
                                        } else {
                                            ?>
                                            <i class="fas fa-lock-open text-muted" style="opacity: 0.2;"></i>
                                        <?php }
                                        ?>
                                        <i class="fas fa-sign-out-alt"></i> <?php echo __("Sign out"); ?>
                                    </a>
                                </li>
                            <?php }
                            ?>

                            <li>
                                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'user');return false;" style="border-radius: 4px 4px 0 0;">
                                    <span class="fa fa-user-circle"></span>
                                    <?php echo __("My Account"); ?>
                                </a>
                            </li>

                            <?php
                            if (User::canUpload(true)) {
                                ?>
                                <li>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'mvideos');
                                                        return false;">
                                        <i class="fa-solid fa-film"></i>
                                        <i class="fa-solid fa-headphones"></i>
                                        <?php echo __("My videos"); ?>
                                    </a>
                                </li>
                            <?php }
                            ?>
                            <li>
                                <a href="#" onclick="avideoModalIframeFull('<?php echo User::getChannelLink(); ?>');
                                                return false;" >
                                    <span class="fas fa-play-circle"></span>
                                    <?php echo __($advancedCustomUser->MyChannelLabel); ?>
                                </a>
                            </li>
                            <?php
                            print AVideoPlugin::navBarProfileButtons();

                            if ((($config->getAuthCanViewChart() == 0) && (User::canUpload())) || (($config->getAuthCanViewChart() == 1) && (User::canViewChart()))) {
                                ?>
                                <li>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'charts');
                                                        return false;">
                                        <span class="fas fa-tachometer-alt"></span>
                                        <?php echo __("Dashboard"); ?>
                                    </a>
                                </li>
                                <?php
                            }
                            if (User::canUpload()) {
                                ?>
                                <li>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'subscribes');
                                                        return false;">
                                        <span class="fa fa-check"></span>
                                        <?php echo __("My Subscribers"); ?>
                                    </a>
                                </li>
                                <?php
                                if (Category::canCreateCategory()) {
                                    ?>

                                    <li>
                                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'categories');
                                                                return false;">
                                            <i class="fa-solid fa-list"></i>
                                            <?php echo __($advancedCustom->CategoryLabel); ?>
                                        </a>

                                    </li>
                                <?php }
                                ?>
                                <li>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'comments');return false;">
                                        <span class="fa fa-comment"></span>
                                        <?php echo __("Comments"); ?>
                                    </a>
                                </li>
                            <?php }
                            ?>

                        </ul>
                        <?php
                    } else if (empty($advancedCustomUser->signInOnRight)) {
                        ?>
                        <a class="btn btn-default navbar-btn line_<?php echo __LINE__; ?>" href="<?php echo $global['webSiteRootURL']; ?>user"
                           id="rightLoginButton" style="min-height:34px; padding: 6px 12px; border-width: 1px;"
                           data-html="true" title="<?php echo __("Login"); ?>" data-placement="left">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                    <?php }
                    ?>
                </div>

            </li>
        </ul>
    </div>
<?php }
?>
