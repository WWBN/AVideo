<?php
$imageEditor = [
    'type' => 'art',
    'title' => __('Channel Art'),
    'help' => __('Recommended size: 2560 x 1440 pixels. Keep text and logos in the marked center area so they remain visible on mobile.'),
    'saveLabel' => __('Save Channel Art'),
    'width' => 2560,
    'height' => 1440,
    'screenWidth' => 720,
    'url' => getURL(User::getBackgroundURLFromUserID(User::getId()), true),
    'endpoint' => 'objects/uploadChannelArt.json.php',
];
include $global['systemRootPath'] . 'view/userImageUploadInclude.php';
