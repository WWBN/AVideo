<form method="post" action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletRazorPay/requestPayment.json.php" style="display:none;" id="RazorPayForm">
    <input type="text" name="value" value="" id="valueRazorPay" autocomplete="off"/>
</form>
<button class="btn btn-primary" id="YPTWalletRazorPayButton"><i class="far fa-credit-card"></i> RazorPay</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletRazorPayButton').click(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $('#valueRazorPay').val($('#value<?php echo @$_GET['plans_id']; ?>').val());
            $('#RazorPayForm').submit();
        });
    });

</script>
