<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("Configurations"); ?></div>
    <div class="panel-body">
        <form id="form">
            <div class="form-group">
                <label for="CryptoWallet"><?php echo $walletDataObject->CryptoWalletName; ?>:</label>
                <input type="text" class="form-control" name="CryptoWallet" value="<?php echo $wallet->getCrypto_wallet_address(); ?>">
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#form").submit(function (event) {
            event.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/saveConfiguration.php',
                data: $("#form").serialize(),
                type: 'post',
                success: function (response) {
                    if (!response.error) {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Configuration Saved"); ?>", "success");
                    } else {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    }
                    modal.hidePleaseWait();
                    console.log(response);
                }
            });
        });
    });
</script>