<?php
$obj = AVideoPlugin::getObjectData("PayPalYPT");
$paypalUid = uniqid();
$paypalPlansId = intval($_GET['plans_id'] ?? 0);
// The server prices PPV and PPV Live purchases from the catalog; the page only names the selection.
$paypalIsPpvCheckout = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/PayPerView/page/buy.php') !== false;
$paypalIsLiveCheckout = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/PayPerViewLive/View/buy.php') !== false;
$paypalCheckoutPlugin = $paypalIsLiveCheckout ? 'PayPerViewLive' : ($paypalIsPpvCheckout ? 'PayPerView' : '');
?>
<button type="button" class="btn btn-primary" id="YPTWalletPayPalButton<?php echo $paypalUid; ?>"><i class="fab fa-paypal"></i> <?php echo __($obj->paymentButtonLabel); ?></button>
<div class="alert alert-danger hidden" id="YPTWalletPayPalError<?php echo $paypalUid; ?>" role="alert"></div>
<script>
    $(document).ready(function () {
        var button = $('#YPTWalletPayPalButton<?php echo $paypalUid; ?>');
        var errorBox = $('#YPTWalletPayPalError<?php echo $paypalUid; ?>');
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
            var amountInput = $('#value<?php echo $paypalPlansId ?: ''; ?>');
            if (!amountInput.length) {
                // Subscription plan cards name their amount field #value{plan} with class "value".
                amountInput = button.parents().has('input.value').first().find('input.value').first();
            }
            $.ajax({
                url: webSiteRootURL + 'plugin/YPTWallet/plugins/YPTWalletPayPal/requestPayment.json.php',
                data: {
                    "plugin": <?php echo json_encode($paypalCheckoutPlugin); ?>,
                    "value": amountInput.val(),
                    "plans_id": <?php echo $paypalPlansId; ?>,
                    "videos_id": $('#videos_id_<?php echo $paypalPlansId; ?>').val() || 0,
                    "ppvlive_schedule_id": <?php echo $paypalIsLiveCheckout ? intval($_GET['ppvlive_schedule_id'] ?? 0) : 0; ?>,
                    "globalToken": <?php echo json_encode(getToken()); ?>
                },
                type: 'post',
                dataType: 'json',
                timeout: 60000,
                success: function (response) {
                    if (!response.error && response.approvalLink) {
                        // Keep the button disabled: the browser is leaving for PayPal.
                        window.top.location.href = response.approvalLink;
                        return;
                    }
                    if (!response.error && response.statusURL) {
                        // An earlier PayPal payment for this purchase is still being confirmed.
                        window.top.location.href = response.statusURL;
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
                    errorBox.removeClass('hidden').text(<?php echo json_encode(__('Could not start the PayPal payment. You were not charged. Please try again.')); ?>);
                }
            });
            return false;
        });
    });
</script>
