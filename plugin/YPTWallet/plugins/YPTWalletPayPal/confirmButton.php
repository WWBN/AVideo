<button type="submit" class="btn btn-primary" id="YPTWalletPayPalButton"><i class="fab fa-paypal"></i> PayPal</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletPayPalButton').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();

            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletPayPal/requestPayment.json.php',
                data: {
                    "value": $('#value').val()
                },
                type: 'post',
                success: function (response) {
                    if (!response.error) {
                        document.location = response.approvalLink;
                    } else {
                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Error!"); ?>", "error");
                        modal.hidePleaseWait();
                    }
                }
            });
            return false;
        });
    });

</script>
<!--
<button type="submit" class="btn btn-primary" id="YPTWalletPayPalSubscriptionButton"><?php echo __("Confirm Subscription"); ?> PayPal</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletPayPalSubscriptionButton').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();

            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletPayPal/requestSubscription.json.php',
                data: {
                    "value": $('#value').val()
                },
                type: 'post',
                success: function (response) {
                    if (!response.error) {
                        document.location = response.approvalLink;
                    } else {
                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Error!"); ?>", "error");
                        modal.hidePleaseWait();
                    }
                }
            });
            return false;
        });
    });

</script>
-->