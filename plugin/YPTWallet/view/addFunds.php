<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
if (!User::isLogged()) {
    forbiddenPage('Login required');
}

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
if (empty($plugin)) {
    forbiddenPage(__('Wallet plugin is disabled'));
}
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
$obj = $plugin->getDataObject();
if (!empty($paypal)) {
    $paypalObj = $paypal->getDataObject();
}
$options = _json_decode($obj->addFundsOptions);
// A direct wallet top-up must not inherit an earlier movie/event checkout redirect.
YPTWallet::setAddFundsSuccessRedirectURL($global['webSiteRootURL'] . 'plugin/YPTWallet/view/addFunds.php');
$_page = new Page(array('Add Funds'));
$_page->setExtraStyles(['plugin/YPTWallet/plugins/YPTWalletStripe/checkout.css']);
?>
<div class="container walletCheckout">
    <div class="text-center walletCheckout-heading">
        <h1><?php echo __('Add funds to your wallet'); ?></h1>
        <p><?php echo __('Choose an amount and pay securely. Your balance is available after payment confirmation.'); ?></p>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-wallet" aria-hidden="true"></i> <?php echo __('Your wallet'); ?></div>
            <div class="panel-body">
                <div class="col-sm-6">
                    <p class="text-muted"><?php echo __('Current balance'); ?></p>
                    <p class="walletCheckout-balance"><?php echo YPTWallet::formatCurrency($plugin->getBalance(User::getId())); ?></p>
                    <p><?php echo __('This is a one-time wallet top-up. It does not start a subscription.'); ?></p>
                    <?php echo $obj->add_funds_text ?>
                </div>
                <div class="col-sm-6">
                    <?php
                    if (!empty($_GET['status'])) {
                        $text = "unknow";
                        $class = "danger";
                        switch ($_GET['status']) {
                            case "fail":
                                $text = $obj->add_funds_success_fail;
                                break;
                            case "success":
                                $text = $obj->add_funds_success_success;
                                $class = "success";
                                break;
                            case "cancel":
                                $text = $obj->add_funds_success_cancel;
                                $class = "warning";
                                break;
                        }
                    ?>
                        <div class="alert alert-<?php echo $class; ?>">
                            <?php echo $text; ?>
                        </div>
                    <?php
                    }
                    ?>
                    <div class="form-group">
                        <label for="value"><?php echo __("Add Funds"); ?> <?php echo $obj->currency_symbol; ?> <?php echo $obj->currency; ?></label>
                        <select class="form-control" id="value">
                            <?php
                            foreach ($options as $value) {
                            ?>
                                <option value="<?php echo $value; ?>"><?php echo $obj->currency_symbol; ?> <?php echo $value; ?> <?php echo $obj->currency; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                    $plugin->getAvailablePayments();
                    ?>
                </div>
            </div>
        </div>


    </div>
</div>
<script>
    $(document).ready(function() {

    });
</script>
<?php
$_page->print();
?>
