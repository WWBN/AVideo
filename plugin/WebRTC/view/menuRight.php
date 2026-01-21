<?php
$obj = AVideoPlugin::getDataObject('WebRTC');

// Check if quick go live is enabled
if (empty($obj->quickGoLiveEnabled)) {
    return;
}

// Check if user is logged in and has stream permission
if (!User::isLogged() || !User::canStream()) {
    return;
}

$buttonTitle = !empty($obj->quickGoLiveButtonTitle) ? $obj->quickGoLiveButtonTitle : 'Go Live';
?>
<li>
    <button id="quickGoLiveButton"
            class="btn btn-danger navbar-btn"
            data-toggle="tooltip"
            title="<?php echo __('Start streaming instantly with one click'); ?>"
            data-placement="bottom">
        <i class="fas fa-bolt"></i>
        <span class="hidden-md hidden-sm hidden-mdx"><?php echo __($buttonTitle); ?></span>
    </button>
</li>
<script src="<?php echo getURL('plugin/WebRTC/view/quickGoLive.js'); ?>"></script>
