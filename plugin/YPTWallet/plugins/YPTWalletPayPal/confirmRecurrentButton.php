<?php
$uniqid = uniqid();
$obj = AVideoPlugin::getObjectData("PayPalYPT");
$paypalRecurrentPlansId = intval($plans_id ?? ($_GET['plans_id'] ?? 0));
?>
<button type="button" class="btn btn-primary" id="YPTWalletPayPalRecurrentButton<?php echo $uniqid; ?>"><i class="fab fa-paypal"></i> <?php echo __($obj->subscriptionButtonLabel); ?></button>
<div class="alert alert-danger hidden" id="YPTWalletPayPalRecurrentError<?php echo $uniqid; ?>" role="alert"></div>
<script>
    $(document).ready(function () {
        var button = $('#YPTWalletPayPalRecurrentButton<?php echo $uniqid; ?>');
        var errorBox = $('#YPTWalletPayPalRecurrentError<?php echo $uniqid; ?>');
        var pending = false;
        button.click(function (evt) {
            evt.preventDefault();
            if (pending) {
                return false;
            }
            pending = true;
            button.prop('disabled', true);
            errorBox.addClass('hidden').text('');
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/YPTWallet/plugins/YPTWalletPayPal/requestSubscription.json.php',
                data: {
                    "plans_id": <?php echo $paypalRecurrentPlansId; ?>,
                    "globalToken": <?php echo json_encode(getToken()); ?>
                },
                type: 'post',
                dataType: 'json',
                timeout: 60000,
                success: function (response) {
                    if (!response.error && (response.approvalLink || response.statusURL)) {
                        window.top.location.href = response.approvalLink || response.statusURL;
                        return;
                    }
                    modal.hidePleaseWait();
                    pending = false;
                    button.prop('disabled', false);
                    errorBox.removeClass('hidden').text(response.msg || <?php echo json_encode(__('PayPal is not available right now. You were not charged. Please try again.')); ?>);
                },
                error: function () {
                    modal.hidePleaseWait();
                    pending = false;
                    button.prop('disabled', false);
                    errorBox.removeClass('hidden').text(<?php echo json_encode(__('Could not start the PayPal subscription. You were not charged. Please try again.')); ?>);
                }
            });
            return false;
        });
    });
</script>
