<?php
$obj = AVideoPlugin::getObjectData('BTCPayments');
$uid = uniqid();
$redirectUrl = urlencode($_SERVER['REQUEST_URI']);
?>

<button type="submit" class="btn btn-primary" id="YPTWalletBTCButton">
    <i class="fab fa-bitcoin"></i> Bitcoin
</button>
<script>
    $(document).ready(function () {
        $('#YPTWalletBTCButton').click(function (evt) {
            evt.preventDefault();
            let invoiceUrl = webSiteRootURL + 'plugin/BTCPayments/View/payments.php?value=' +
                $('#value<?php echo @$_GET['plans_id']; ?>').val() +
                '&description=Wallet+add+funds&redirectUrl=<?php echo $redirectUrl; ?>';

            avideoModalIframe(invoiceUrl);
        });
    });
</script>
