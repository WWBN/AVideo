<?php
if (!User::canUpload()) {
    return false;
}
?>
<script src="<?php echo getURL('plugin/SocialMediaPublisher/script.js'); ?>"></script>
<link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css" />
<div class="row">
    <div class="col-sm-12 socialPublisherConnectHelp">
        <i class="fa-solid fa-circle-info"></i>
        <?php echo __('Connect an account to publish your videos on it. A window opens where you sign in and allow this site to publish for you. To connect again an account that stopped working, just connect it again.'); ?>
        <br>
        <small class="text-muted">
            <?php echo __('Disconnect removes the account from this site. To remove the permission completely, also remove it in the security settings of that account (for YouTube, in your Google Account).'); ?>
            <a href="https://restream.ypt.me/TermsOfUse" target="_blank" rel="noopener noreferrer" class="btn btn-link btn-xs"><?php echo __('Terms of Use'); ?></a>
            <a href="https://restream.ypt.me/PrivacyPolicies" target="_blank" rel="noopener noreferrer" class="btn btn-link btn-xs"><?php echo __('Privacy Policies'); ?></a>
        </small>
    </div>
    <div class="col-sm-12">
        <div class="social-network btn-group btn-group-justified">
            <?php
            foreach (SocialMediaPublisher::SOCIAL_TYPES as $value) {
                // Bootstrap 3 justified groups need each <button> inside its own .btn-group
            ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default <?php echo $value['iconClass']; ?>" onclick="openYPT('<?php echo $value['name']; ?>')" title="<?php echo __('Connect'), ' ', $value['label']; ?>">
                        <div class="largeSocialIcon"><?php echo $value['ico']; ?></div>
                        <?php echo $value['label']; ?>
                    </button>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
