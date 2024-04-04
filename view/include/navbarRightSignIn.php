<?php
if (!empty($advancedCustomUser->signInOnRight)) {
    if (User::isLogged()) {
        if (!$advancedCustomUser->disableSignOutButton) {
            ?>
            <li>
                <a class="btn navbar-btn btn-default" href="#" onclick="avideoLogoff(true);" >
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
                    <i class="fas fa-sign-out-alt"></i> <span class="hidden-md hidden-sm"><?php echo __("Sign Out"); ?></span>
                </a>
            </li>
            <?php
        }
    } else {
        ?>
        <li>
            <a class="btn navbar-btn btn-default line_<?php echo __LINE__; ?>" href="<?php echo $global['webSiteRootURL']; ?>user" >
                <i class="fas fa-sign-in-alt"></i> <?php echo __("Login"); ?>
            </a>
        </li>
        <?php
    }
}
?>