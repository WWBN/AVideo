<?php
$btcUid = uniqid();
$btcPlansId = intval($_GET['plans_id'] ?? 0);
// The server prices PPV and PPV Live purchases from the catalog; the page only names the selection (as PayPal does).
$btcIsPpvCheckout = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/PayPerView/page/buy.php') !== false;
$btcIsLiveCheckout = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/PayPerViewLive/View/buy.php') !== false;
$btcCheckoutPlugin = $btcIsLiveCheckout ? 'PayPerViewLive' : ($btcIsPpvCheckout ? 'PayPerView' : '');
?>
<button type="button" class="btn btn-primary" id="YPTWalletBTCButton<?php echo $btcUid; ?>"><i class="fab fa-bitcoin" aria-hidden="true"></i> <?php echo __('Pay With Bitcoin'); ?></button>
<div class="alert alert-danger hidden" id="YPTWalletBTCError<?php echo $btcUid; ?>" role="alert"></div>
<script>
    $(document).ready(function () {
        var button = $('#YPTWalletBTCButton<?php echo $btcUid; ?>');
        var errorBox = $('#YPTWalletBTCError<?php echo $btcUid; ?>');
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
            var amountInput = $('#value<?php echo $btcPlansId ?: ''; ?>');
            if (!amountInput.length) {
                // Subscription plan cards name their amount field #value{plan} with class "value".
                amountInput = button.parents().has('input.value').first().find('input.value').first();
            }
            $.ajax({
                url: webSiteRootURL + 'plugin/YPTWallet/plugins/YPTWalletBTC/requestPayment.json.php',
                data: {
                    "plugin": <?php echo json_encode($btcCheckoutPlugin); ?>,
                    "value": amountInput.val(),
                    "plans_id": <?php echo $btcPlansId; ?>,
                    "videos_id": $('#videos_id_<?php echo $btcPlansId; ?>').val() || 0,
                    "ppvlive_schedule_id": <?php echo $btcIsLiveCheckout ? intval($_GET['ppvlive_schedule_id'] ?? 0) : 0; ?>,
                    "globalToken": <?php echo json_encode(getToken()); ?>
                },
                type: 'post',
                dataType: 'json',
                timeout: 60000,
                success: function (response) {
                    if (!response.error && response.statusURL) {
                        // Keep the button disabled: the browser is leaving for the Bitcoin payment page.
                        window.top.location.href = response.statusURL;
                        return;
                    }
                    modal.hidePleaseWait();
                    pending = false;
                    button.prop('disabled', false);
                    errorBox.removeClass('hidden').text(response.msg || <?php echo json_encode(__('Bitcoin payments are not available right now. You were not charged. Please try again.')); ?>);
                },
                error: function () {
                    modal.hidePleaseWait();
                    pending = false;
                    button.prop('disabled', false);
                    errorBox.removeClass('hidden').text(<?php echo json_encode(__('Could not start the Bitcoin payment. You were not charged. Please try again.')); ?>);
                }
            });
            return false;
        });
    });
</script>
