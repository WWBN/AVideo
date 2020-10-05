<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fa fa-cog"></i> <?php echo __('Customize options'); ?> <div class="pull-right"><?php echo getPluginSwitch('CustomizeAdvanced'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                $filter = array(
                    'disableNativeSignUp' => __('The form to signup will not exists'),
                    'disableNativeSignIn' => __('The regular form to signin will not exist, if you check this will only have social login or LDAP option'),
                    'userMustBeLoggedIn' => __('The site will display only a login form to un authenticated users'));
                createTable("CustomizeUser", $filter);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-facebook-square"></i> Facebook <div class="pull-right"><?php echo getPluginSwitch('LoginFacebook'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("LoginFacebook");
                ?>
                <small class="form-text text-muted">
                    <a href="https://developers.facebook.com/apps"><?php echo __('Get Facebook ID and Key'); ?></a><br>
                    <?php echo __('Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Facebook</strong><br>
                    <?php echo __('For mobile a Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>plugin/MobileManager/oauth2.php?type=Facebook</strong>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-google-plus-g"></i> Google <div class="pull-right"><?php echo getPluginSwitch('LoginGoogle'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("LoginGoogle");
                ?>
                <small class="form-text text-muted">
                    <a href="https://console.developers.google.com/apis/credentials"><?php echo __('Get Google ID and Key'); ?></a><br>
                    <?php echo __('Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Google</strong><br>
                    <?php echo __('For mobile a Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>plugin/MobileManager/oauth2.php?type=Google</strong>
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-linkedin"></i> Linkedin <div class="pull-right"><?php echo getPluginSwitch('LoginLinkedin'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("LoginLinkedin");
                ?>
                <small class="form-text text-muted">
                    <a href="https://www.linkedin.com/secure/developer"><?php echo __('Get Linkedin ID and Key'); ?></a><br>
                    <?php echo __('Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Linkedin</strong><br>
                    <?php echo __('For mobile a Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>plugin/MobileManager/oauth2.php?type=Linkedin</strong>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-twitter-square"></i> Twitter <div class="pull-right"><?php echo getPluginSwitch('LoginTwitter'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("LoginTwitter");
                ?>
                <small class="form-text text-muted">
                    <a href="https://apps.twitter.com/"><?php echo __('Get Twitter ID and Key'); ?></a><br>
                    <?php echo __('Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Twitter</strong><br>
                    <?php echo __('For mobile a Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>plugin/MobileManager/oauth2.php?type=Twitter</strong>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-yahoo"></i> Yahoo <div class="pull-right"><?php echo getPluginSwitch('LoginYahoo'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("LoginYahoo");
                ?>
                <small class="form-text text-muted">
                    <a href="https://developer.yahoo.com/oauth2/guide/flows_authcode/"><?php echo __('Get Yahoo ID and Key'); ?></a><br>
                    <?php echo __('Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Yahoo</strong><br>
                    <?php echo __('For mobile a Valid OAuth redirect URIs'); ?>: <strong><?php echo $global['webSiteRootURL']; ?>plugin/MobileManager/oauth2.php?type=Yahoo</strong>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-sign-in-alt"></i> LDAP <div class="pull-right"><?php echo getPluginSwitch('LoginLDAP'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("LoginLDAP");
                ?>
                <small class="form-text text-muted">
                    <a href="https://github.com/WWBN/AVideo/wiki/Configure-LDAP-Plugin"><?php echo __('Help Page'); ?></a>
                </small>

            </div>
        </div>
    </div>
</div>
