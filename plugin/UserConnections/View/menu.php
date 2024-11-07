<?php
if(!User::isLogged()){
    return false;
}
if (isIframe() || isConfirmationPage() || isEmbed() || !empty($global['connectionAdded'])) {
    return false;
}
$global['connectionAdded'] = 1;
?>
<link href="<?php echo getURL('plugin/UserConnections/View/menu.css'); ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo getURL('plugin/UserConnections/View/menu.js'); ?>"></script>
<nav id="connectionMenu-toolbar" role="navigation">
    <div
        id="connectionMenu-toolbar-toggle"
        class="connectionMenu-toolbar-toggle list-group-item"
        data-toggle="tooltip"
        title="<?php echo __('Friends'); ?>"
        data-placement="right" onclick="toggleConnectionMenu();">
        <i class="fa-solid fa-user-group animate__animated animate__bounceIn inactiveIcon"></i>
        <div class="button animate__animated animate__bounceIn">
            <span class="sr-only"><?php echo __('Open'); ?></span>
            <i class="fa-solid fa-xmark fa-2x showWhenActive animate__animated animate__rotateIn"></i>
            <i class="fa-solid fa-user-group fa-2x hideWhenActive"></i>
        </div>
    </div>
    <div id="connectionMenu-toolbar-overlay" class="" style="display: none;">
        <ul class="list-group blur-background" id="connectionList">
            <!-- Dynamic list of connections will be populated here -->
        </ul>
    </div>
</nav>