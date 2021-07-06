<?php
$uniqid = uniqid();
$obj = AVideoPlugin::getObjectData("PayPalYPT");

$params = array('total'=>$total, 'currency'=>$currency, 'frequency'=>$frequency, 'interval'=>$interval, 'name'=>$name, 'json'=>$json, 'trialDays'=>$trialDays, 'addFunds_Success'=>$addFunds_Success);

?>
<button type="submit" class="btn btn-primary" id="YPTWalletPayPalRecurrentButton<?php echo $uniqid; ?>"><i class="fab fa-paypal"></i> <?php echo __($obj->subscriptionButtonLabel); ?></button>
<script>
    $(document).ready(function () {
        $('#YPTWalletPayPalRecurrentButton<?php echo $uniqid; ?>').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();

            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletPayPal/requestSubscriptionV2.json.php',
                data: {
                    "hash": "<?php echo encryptString(json_encode($params)); ?>"
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