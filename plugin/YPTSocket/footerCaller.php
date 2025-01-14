<?php
if (isBot()) {
    return false;
}
if (!User::isLogged()) {
    return false;
}

$obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');

if (empty($obj->enableCalls)) {
    return false;
}

$response = pluginsRequired(array('Meet', 'YPTSocket'), "Caller");

if($response->error){
    echo '<script>avideoAlertOnce("Notice", "'.$response->msg.'", "info", "meetSocketAlert");</script>';
    return false;
}
?>
<style>
    .incomeCallImage{
        max-width: 50px;
        max-height: 50px;
    }
    .incomeCallBtn{
        margin: 10px;
        width: 32px;
        height: 32px;
        font-size: 14px;
        padding: 0;
    }
</style>
<script src="<?php echo getURL('plugin/WebRTC/call/caller.js'); ?>" type="text/javascript"></script>
<!--
<script src="<?php echo getURL('plugin/YPTSocket/caller.js'); ?>" type="text/javascript"></script>
-->