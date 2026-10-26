<?php
$imageEditor = [
    'type' => 'photo',
    'title' => __('Profile Photo'),
    'help' => __('Use a square image for your channel profile photo.'),
    'saveLabel' => __('Save Profile Photo'),
    'width' => 150,
    'height' => 150,
    'screenWidth' => 180,
    'url' => User::getPhoto(0, true),
    'endpoint' => 'objects/uploadUserPhoto.json.php',
];
include $global['systemRootPath'] . 'view/userImageUploadInclude.php';
if ($vloObj = AVideoPlugin::getDataObjectIfEnabled('VideoLogoOverlay')) {
    if ($vloObj->useUserChannelImageAsLogo) {
        ?><div class="alert alert-info"><?php echo __('This image will appear in your livestream'); ?></div><?php
    }
}
