<?php
$obj = AVideoPlugin::getObjectData('StripeYPT');
$uid = uniqid();
?>
<form method="post" action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletRazorPay/requestSubscription.json.php" style="display:none;" id="RazorPayForm<?php echo $uid; ?>">
    <input type="text" name="value" value="" id="valueRazorPay<?php echo $uid; ?>" autocomplete="off"/>
    <input type="text" name="plans_id" value="<?php echo @$_GET['plans_id']; ?>" autocomplete="off"/>
</form>
<button class="btn btn-primary" id="YPTWalletRazorPayButton<?php echo $uid; ?>"><i class="far fa-credit-card"></i> RazorPay</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletRazorPayButton<?php echo $uid; ?>').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $('#valueRazorPay<?php echo $uid; ?>').val($('#value<?php echo @$_GET['plans_id']; ?>').val());
            $('#RazorPayForm<?php echo $uid; ?>').submit();
        });
    });
</script>
