<?php
if (!User::canUpload()) {
    return false;
}
?>
<script src="<?php echo getURL('plugin/SocialMediaPublisher/script.js'); ?>"></script>
<link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css" />
<div class="row">
    <div class="col-sm-12">
        <div class="pull-left">
            <i class="fa-solid fa-circle-info"></i>
            Please click on one of the social network buttons below to authorize our site to publish videos on your account. This allows us to share your content directly to your chosen platform.
        </div>

        <div class="pull-right">
            <a href="https://restream.ypt.me/TermsOfUse" target="_blank" class="btn btn-link">Terms of Use</a>
            <a href="https://restream.ypt.me/PrivacyPolicies" target="_blank" class="btn btn-link">Privacy Policies</a>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="social-network btn-group btn-group-justified">
            <?php
            foreach (SocialMediaPublisher::SOCIAL_TYPES as $value) {
            ?>
                <button type="button" class="btn btn-default <?php echo $value['iconClass']; ?> " onclick="openYPT('<?php echo $value['name']; ?>')">
                    <div class="largeSocialIcon"><?php echo $value['ico']; ?></div>
                    <?php echo $value['label']; ?>
                </button>
            <?php
            }
            ?>
        </div>

    </div>

</div>
<script>

</script>