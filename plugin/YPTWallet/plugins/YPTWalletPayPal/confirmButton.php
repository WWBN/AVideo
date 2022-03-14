<?php
$obj = AVideoPlugin::getObjectData("PayPalYPT");
?>
<button type="submit" class="btn btn-primary" id="YPTWalletPayPalButton<?php echo @$_GET['plans_id']; ?>"><i class="fab fa-paypal"></i> <?php echo __($obj->paymentButtonLabel); ?></button>
<script>
    $(document).ready(function () {
        $('#YPTWalletPayPalButton<?php echo @$_GET['plans_id']; ?>').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();

            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletPayPal/requestPayment.json.php',
                data: {
                    "value": $('#value<?php echo @$_GET['plans_id']; ?>').val(),
                    "videos_id": $('#videos_id_<?php echo @$_GET['plans_id']; ?>').val()
                },
                type: 'post',
                success: function (response) {
                    if (!response.error) {
                        document.location = response.approvalLink;
                    } else {
                        avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Error!"); ?>", "error");
                        modal.hidePleaseWait();
                    }
                }
            });
            return false;
        });
    });

</script>