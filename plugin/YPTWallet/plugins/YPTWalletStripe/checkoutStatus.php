<?php
// Shared by the single-payment and subscription forms; $uid scopes each checkout.
if (empty($global['stripeCheckoutStylesLoaded'])) {
    $global['stripeCheckoutStylesLoaded'] = true;
    ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(getURL('plugin/YPTWallet/plugins/YPTWalletStripe/checkout.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php
}
?>
<ol class="stripeCheckout-steps" aria-label="<?php echo __('Checkout progress'); ?>">
    <li data-checkout-step="1"><span>1</span> <?php echo __('Payment'); ?></li>
    <li data-checkout-step="2"><span>2</span> <?php echo $stripeCheckoutActivationLabel ?? __('Activate access'); ?></li>
    <li data-checkout-step="3"><span>3</span> <?php echo __('Ready'); ?></li>
</ol>
<?php if (strpos($obj->Publishablekey, 'pk_test_') === 0) { ?>
    <p class="alert alert-warning stripeCheckout-test"><strong><?php echo __('Test mode'); ?></strong> &mdash; <?php echo __('Use a Stripe test card. No real payment will be made.'); ?></p>
<?php } ?>
<div class="alert alert-info stripeCheckout-status hidden" id="checkout-status<?php echo $uid; ?>" role="status" aria-live="polite" tabindex="-1">
    <strong id="checkout-title<?php echo $uid; ?>"></strong>
    <p id="payment-status<?php echo $uid; ?>"></p>
    <a class="btn btn-success btn-block hidden" id="checkout-continue<?php echo $uid; ?>" target="_top"></a>
</div>
