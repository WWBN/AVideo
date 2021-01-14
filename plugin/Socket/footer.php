<script>
    var webSocketURL = 'ws://localhost:8888';
    var webSocketToken = '<?php echo getEncryptedInfo(); ?>';
</script>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Socket/script.js" type="text/javascript"></script>
