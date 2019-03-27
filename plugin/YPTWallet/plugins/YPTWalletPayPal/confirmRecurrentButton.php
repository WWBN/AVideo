<?php
$uniqid = uniqid();
?>
<button type="submit" class="btn btn-primary" id="YPTWalletPayPalRecurrentButton<?php echo $uniqid; ?>"><i class="fab fa-paypal"></i> <?php echo __("Subscribe"); ?> PayPal</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletPayPalRecurrentButton<?php echo $uniqid; ?>').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();

            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletPayPal/requestSubscription.json.php',
                data: {
                    "plans_id": "<?php echo @$_GET['plans_id']; ?>"
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