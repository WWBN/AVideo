<?php
global $global;
?>
<div style="    display: inline-grid;
     height: 100vh;
     width: 100vw;">
    <button class="btn btn-danger btn-lg btn-block h1" onclick="sendAVideoMobileMessage('goLive', '1');" style="font-size: 32px;">
        <i class="fas fa-broadcast-tower"></i> <?php echo __('Go Live'); ?>
    </button>
    <button class="btn btn-primary btn-lg btn-block h1" onclick="document.location = '<?php echo $global['webSiteRootURL']; ?>plugin/MobileManager/?logoff=1'" style="font-size: 32px;">
        <i class="fas fa-sign-out-alt"></i> <?php echo __('Logoff'); ?>
    </button>
</div>
<script>
    $(function () {
        sendAVideoMobileMessage('loginJS', {site:"<?php echo $global['webSiteRootURL']; ?>", user:"<?php echo User::getUserName(); ?>", pass:"<?php echo User::getUserPass(); ?>"});    
        //sendAVideoMobileMessage('saveSessionUser', {site:"<?php echo $global['webSiteRootURL']; ?>", user:"<?php echo User::getUserName(); ?>", pass:"<?php echo User::getUserPass(); ?>"});
    });
</script>