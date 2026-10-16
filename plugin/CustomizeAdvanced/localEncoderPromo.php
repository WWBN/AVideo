<?php
$localEncoderPageURL = CustomizeAdvanced::getLocalEncoderURL();
if (User::canUpload() && !empty($localEncoderPageURL)) {
?>
    <div class="alert alert-info" role="note">
        <p><strong><i class="fas fa-desktop" aria-hidden="true"></i> <?php echo __('Prepare videos on your computer with Local Encoder'); ?></strong></p>
        <p><?php echo __('Use your computer to encode videos and import supported video links, then upload the results to this AVideo site. Your Web Encoder remains required.'); ?></p>
        <a class="btn btn-default" href="<?php echo htmlspecialchars($localEncoderPageURL, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-download" aria-hidden="true"></i> <?php echo __('Learn more and download'); ?>
        </a>
    </div>
<?php
}
?>
