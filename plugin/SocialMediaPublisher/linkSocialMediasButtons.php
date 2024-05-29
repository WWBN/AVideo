<?php
if (!User::canUpload()) {
    return false;
}
?>
<script src="<?php echo getURL('plugin/SocialMediaPublisher/script.js'); ?>"></script>
<link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css" />
<div class="row">
    <div class="col-sm-12">
        <p>
            <i class="fa-solid fa-circle-info"></i>
            Please click on one of the social network buttons below to authorize our site to publish videos on your account. This allows us to share your content directly to your chosen platform.
        </p>
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